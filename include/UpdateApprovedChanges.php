<?php

/**
 * Apply approved changes from the Google Sheets "ApprovedChanges" worksheet.
 *
 * This file is intended to be run from cPanel's daily scheduler using the CLI
 * PHP binary. It deliberately updates only non-empty spreadsheet cells.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../../.env.php';

// db_login.php is the application's central database connection bootstrap.
// Setting ADMIN avoids its browser-only installation message when run by cron.
$ADMIN = true;
require_once __DIR__ . '/db_login.php';

const APPROVED_CHANGES_WORKSHEET = 'ApprovedChanges';
const APPROVED_CHANGES_RANGE = "'ApprovedChanges'!A:F";

/**
 * Normalize worksheet headings so minor case/whitespace differences do not
 * prevent the import from finding a required column.
 */
function normalizeApprovedChangesHeader(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    $value = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

    return strtolower($value);
}

/**
 * Return a scalar Google Sheets cell as text.
 */
function approvedChangesCell(array $row, int $index): string
{
    $value = $row[$index] ?? '';

    if (!is_scalar($value)) {
        throw new RuntimeException('A spreadsheet cell contains an invalid value.');
    }

    return trim((string) $value);
}

/**
 * Update one event kind for a person, inserting it if it does not exist.
 * Empty spreadsheet values are intentionally ignored by the caller.
 */
function updateApprovedChangesEvent(
    PDO $pdo,
    int $treeId,
    int $personId,
    string $gedcomNumber,
    string $eventKind,
    string $eventDate
): bool {
    $select = $pdo->prepare(
        "SELECT event_id, event_date
         FROM humo_events
         WHERE event_tree_id = :tree_id
           AND event_connect_kind = 'person'
           AND event_connect_id = :gedcom_number
           AND event_kind = :event_kind
         ORDER BY event_order, event_id"
    );
    $select->execute([
        ':tree_id' => $treeId,
        ':gedcom_number' => $gedcomNumber,
        ':event_kind' => $eventKind,
    ]);
    $events = $select->fetchAll(PDO::FETCH_ASSOC);

    $changed = false;
    if ($events) {
        $update = $pdo->prepare(
            "UPDATE humo_events
             SET event_date = :event_date
             WHERE event_id = :event_id
               AND (event_date IS NULL OR event_date <> :comparison_date)"
        );

        foreach ($events as $event) {
            $update->execute([
                ':event_date' => $eventDate,
                ':event_id' => $event['event_id'],
                ':comparison_date' => $eventDate,
            ]);
            $changed = $changed || $update->rowCount() > 0;
        }

        return $changed;
    }

    $insert = $pdo->prepare(
        "INSERT INTO humo_events (
            event_tree_id,
            event_connect_kind,
            event_connect_id,
            event_kind,
            event_date,
            person_id
         ) VALUES (
            :tree_id,
            'person',
            :gedcom_number,
            :event_kind,
            :event_date,
            :person_id
         )"
    );
    $insert->execute([
        ':tree_id' => $treeId,
        ':gedcom_number' => $gedcomNumber,
        ':event_kind' => $eventKind,
        ':event_date' => $eventDate,
        ':person_id' => $personId,
    ]);

    return true;
}

/**
 * Find the first address associated with a person. HuMo supports both direct
 * person addresses and residence records connected through humo_connections.
 */
function findApprovedChangesAddress(PDO $pdo, int $treeId, string $gedcomNumber): ?array
{
    $statement = $pdo->prepare(
        "SELECT a.address_id, a.address_gedcomnr, a.address_phone, a.address_address
         FROM humo_addresses a
         LEFT JOIN humo_connections c
           ON c.connect_tree_id = a.address_tree_id
          AND c.connect_sub_kind = 'person_address'
          AND c.connect_connect_id = :connection_person
          AND c.connect_item_id = a.address_gedcomnr
         WHERE a.address_tree_id = :tree_id
           AND (
                (a.address_connect_kind = 'person'
                 AND a.address_connect_sub_kind = 'person'
                 AND a.address_connect_id = :direct_person)
                OR c.connect_id IS NOT NULL
           )
         ORDER BY a.address_order IS NULL, a.address_order, a.address_id
         LIMIT 1"
    );
    $statement->execute([
        ':connection_person' => $gedcomNumber,
        ':tree_id' => $treeId,
        ':direct_person' => $gedcomNumber,
    ]);

    $address = $statement->fetch(PDO::FETCH_ASSOC);
    return $address ?: null;
}

/**
 * Update the first person address, or create and connect one when none exists.
 * Only values supplied by the worksheet are written.
 */
function upsertApprovedChangesAddress(
    PDO $pdo,
    int $treeId,
    string $gedcomNumber,
    ?string $addressText,
    ?string $phone
): bool {
    $address = findApprovedChangesAddress($pdo, $treeId, $gedcomNumber);

    if ($address) {
        $updates = [];
        $parameters = [':address_id' => $address['address_id']];
        if ($addressText !== null && $addressText !== $address['address_address']) {
            $updates[] = 'address_address = :address';
            $parameters[':address'] = $addressText;
        }
        if ($phone !== null && $phone !== $address['address_phone']) {
            $updates[] = 'address_phone = :phone';
            $parameters[':phone'] = $phone;
        }
        if (!$updates) {
            return false;
        }

        $statement = $pdo->prepare(
            'UPDATE humo_addresses SET ' . implode(', ', $updates) .
            ' WHERE address_id = :address_id'
        );
        $statement->execute($parameters);
        return $statement->rowCount() > 0;
    }

    $numberStatement = $pdo->prepare(
        "SELECT address_gedcomnr
         FROM humo_addresses
         WHERE address_tree_id = :tree_id"
    );
    $numberStatement->execute([':tree_id' => $treeId]);
    $nextNumber = 0;
    foreach ($numberStatement->fetchAll(PDO::FETCH_COLUMN) as $existingNumber) {
        $numericPart = (int) preg_replace('/\D/', '', (string) $existingNumber);
        $nextNumber = max($nextNumber, $numericPart);
    }
    $addressGedcomNumber = 'R' . ($nextNumber + 1);

    $insert = $pdo->prepare(
        "INSERT INTO humo_addresses (
            address_tree_id,
            address_gedcomnr,
            address_connect_kind,
            address_connect_sub_kind,
            address_connect_id,
            address_address,
            address_phone
         ) VALUES (
            :tree_id, :address_gedcomnr, 'person', 'person', :gedcom_number,
            :address, :phone
         )"
    );
    $insert->execute([
        ':tree_id' => $treeId,
        ':address_gedcomnr' => $addressGedcomNumber,
        ':gedcom_number' => $gedcomNumber,
        ':address' => $addressText,
        ':phone' => $phone,
    ]);

    $connection = $pdo->prepare(
        "INSERT INTO humo_connections (
            connect_tree_id,
            connect_kind,
            connect_sub_kind,
            connect_connect_id,
            connect_item_id
         ) VALUES (:tree_id, 'person', 'person_address', :gedcom_number, :address_gedcomnr)"
    );
    $connection->execute([
        ':tree_id' => $treeId,
        ':gedcom_number' => $gedcomNumber,
        ':address_gedcomnr' => $addressGedcomNumber,
    ]);

    return true;
}

/**
 * Delete processed worksheet rows. Rows are deleted from bottom to top so
 * earlier row numbers remain valid after each deletion.
 */
function deleteApprovedChangesRows(
    Google\Service\Sheets $service,
    string $spreadsheetId,
    int $sheetId,
    array $rowNumbers
): void {
    $requests = [];
    rsort($rowNumbers, SORT_NUMERIC);
    foreach ($rowNumbers as $rowNumber) {
        $dimension = new Google\Service\Sheets\DimensionRange();
        $dimension->setSheetId($sheetId);
        $dimension->setDimension('ROWS');
        $dimension->setStartIndex($rowNumber - 1);
        $dimension->setEndIndex($rowNumber);

        $delete = new Google\Service\Sheets\DeleteDimensionRequest();
        $delete->setRange($dimension);

        $request = new Google\Service\Sheets\Request();
        $request->setDeleteDimension($delete);
        $requests[] = $request;
    }

    if ($requests) {
        $batch = new Google\Service\Sheets\BatchUpdateSpreadsheetRequest();
        $batch->setRequests($requests);
        $service->spreadsheets->batchUpdate($spreadsheetId, $batch);
    }
}

try {
    if (!isset($dbh) || !$dbh instanceof PDO) {
        throw new RuntimeException('The HuMo database connection was not initialized.');
    }

    if (!isset($GOOGLE_SHEET_ID) || !is_string($GOOGLE_SHEET_ID) || trim($GOOGLE_SHEET_ID) === '') {
        throw new RuntimeException('GOOGLE_SHEET_ID is not configured in the environment file.');
    }

    $credentialsFile = getenv('HUMOGEN_GOOGLE_SERVICE_ACCOUNT')
        ?: __DIR__ . '/../../../service-account.json';
    if (!is_readable($credentialsFile)) {
        throw new RuntimeException('Google service-account credentials are unavailable.');
    }

    $client = new Google\Client();
    $client->setApplicationName('HuMo-genealogy Approved Changes');
    $client->setAuthConfig($credentialsFile);
    $client->setScopes([Google\Service\Sheets::SPREADSHEETS]);

    $service = new Google\Service\Sheets($client);
    $spreadsheet = $service->spreadsheets->get($GOOGLE_SHEET_ID, [
        'fields' => 'sheets(properties(sheetId,title))',
    ]);
    $sheetId = null;
    foreach ($spreadsheet->getSheets() as $sheet) {
        if ($sheet->getProperties()->getTitle() === APPROVED_CHANGES_WORKSHEET) {
            $sheetId = (int) $sheet->getProperties()->getSheetId();
            break;
        }
    }
    if ($sheetId === null) {
        throw new RuntimeException('ApprovedChanges worksheet was not found.');
    }
    $response = $service->spreadsheets_values->get($GOOGLE_SHEET_ID, APPROVED_CHANGES_RANGE);
    $sheetRows = $response->getValues() ?: [];

    if (count($sheetRows) < 2) {
        echo "No approved changes found.\n";
        exit(0);
    }

    $headers = array_map(
        static fn($header): string => normalizeApprovedChangesHeader((string) $header),
        $sheetRows[0]
    );
    $columnMap = array_flip($headers);
    $requiredHeaders = [
        'gedcom number' => 'GEDCOM number',
        'birth date' => 'Birth date',
        'death date' => 'Death date',
        'phone' => 'Phone',
        'address' => 'Address',
    ];

    foreach ($requiredHeaders as $normalizedHeader => $displayHeader) {
        if (!array_key_exists($normalizedHeader, $columnMap)) {
            throw new RuntimeException("Required worksheet column is missing: {$displayHeader}");
        }
    }

    $pdo = $dbh;
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->beginTransaction();

    $personStatement = $pdo->prepare(
        "SELECT pers_id, pers_tree_id
         FROM humo_persons
         WHERE pers_gedcomnumber = :gedcom_number
         LIMIT 1"
    );
    $seenGedcomNumbers = [];
    $processedSheetRows = [];
    $updated = 0;
    $unchanged = 0;
    $skipped = 0;

    foreach (array_slice($sheetRows, 1) as $rowNumber => $row) {
        $sheetRowNumber = $rowNumber + 2;
        $gedcomNumber = approvedChangesCell($row, $columnMap['gedcom number']);

        if ($gedcomNumber === '') {
            $skipped++;
            continue;
        }
        if (isset($seenGedcomNumbers[$gedcomNumber])) {
            throw new RuntimeException("Duplicate GEDCOM number '{$gedcomNumber}' on worksheet row {$sheetRowNumber}.");
        }
        $seenGedcomNumbers[$gedcomNumber] = true;

        $personStatement->execute([':gedcom_number' => $gedcomNumber]);
        $person = $personStatement->fetch();
        if (!$person) {
            throw new RuntimeException("GEDCOM number '{$gedcomNumber}' on worksheet row {$sheetRowNumber} was not found.");
        }

        $rowChanged = false;
        $birthDate = approvedChangesCell($row, $columnMap['birth date']);
        if ($birthDate !== '') {
            $rowChanged = updateApprovedChangesEvent(
                $pdo,
                (int) $person['pers_tree_id'],
                (int) $person['pers_id'],
                $gedcomNumber,
                'birth',
                $birthDate
            ) || $rowChanged;
        }

        $deathDate = approvedChangesCell($row, $columnMap['death date']);
        if ($deathDate !== '') {
            $rowChanged = updateApprovedChangesEvent(
                $pdo,
                (int) $person['pers_tree_id'],
                (int) $person['pers_id'],
                $gedcomNumber,
                'death',
                $deathDate
            ) || $rowChanged;
        }

        $phone = approvedChangesCell($row, $columnMap['phone']);
        $addressText = approvedChangesCell($row, $columnMap['address']);
        if ($phone !== '' || $addressText !== '') {
            $rowChanged = upsertApprovedChangesAddress(
                $pdo,
                (int) $person['pers_tree_id'],
                $gedcomNumber,
                $addressText !== '' ? $addressText : null,
                $phone !== '' ? $phone : null
            ) || $rowChanged;
        }

        $processedSheetRows[] = $sheetRowNumber;

        if ($rowChanged) {
            $updated++;
        } else {
            $unchanged++;
        }
    }

    $pdo->commit();
    deleteApprovedChangesRows($service, $GOOGLE_SHEET_ID, $sheetId, $processedSheetRows);
    echo sprintf(
        "Approved changes processed. Updated: %d, unchanged: %d, skipped: %d.\n",
        $updated,
        $unchanged,
        $skipped
    );
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Approved changes import failed');
    error_log('Exception: ' . get_class($exception));
    error_log('Message: ' . $exception->getMessage());
    error_log('File: ' . $exception->getFile());
    error_log('Line: ' . $exception->getLine());
    if ($exception instanceof Google\Service\Exception) {
        error_log('Google API errors: ' . json_encode($exception->getErrors()));
        error_log('HTTP code: ' . $exception->getCode());
    }

    echo 'Error: ' . $exception->getMessage() . "\n";
    exit(1);
}
