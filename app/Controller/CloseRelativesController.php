<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\CloseRelativesModel;

class CloseRelativesController
{
    private const GOOGLE_SHEET_ID = '16uvHsVK1BjdaP2x8WE0xpuxxMGPyQci5WOmS9ls4zlc';
    private const GOOGLE_SERVICE_ACCOUNT_FILE = __DIR__ . '/../../../../service-account.json';

    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail(string $id): array
    {
        $graph = (new CloseRelativesModel($this->config))->getGraph($id);
        $graph['title'] = __('Close Relatives');
        return $graph;
    }

    public function submitToGoogleSheet(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $requestToken = $_SERVER['HTTP_X_CLOSE_RELATIVES_SHEET_TOKEN'] ?? '';
            $sessionToken = $_SESSION['close_relatives_sheet_token'] ?? '';
            if (!$requestToken || !$sessionToken || !hash_equals($sessionToken, $requestToken)) {
                throw new \RuntimeException('Invalid close relatives submission request.');
            }

            $payload = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
            $submittedRows = $payload['rows'] ?? null;
            $mainPerson = $payload['mainPerson'] ?? '';
            if (!is_array($submittedRows) || count($submittedRows) < 2 || count($submittedRows) > 5000) {
                throw new \RuntimeException('Invalid close relatives table data.');
            }
            if (!is_string($mainPerson) || !preg_match('/^[A-Za-z0-9_-]+$/', $mainPerson)) {
                throw new \RuntimeException('Invalid main person GEDCOM number.');
            }
            $worksheetTitle = $mainPerson;

            $rows = [];
            foreach (array_slice($submittedRows, 1) as $row) {
                if (!is_array($row) || count($row) !== 8) {
                    throw new \RuntimeException('Invalid close relatives table row.');
                }
                $values = array_map(static function ($value): string {
                    if (!is_scalar($value)) {
                        throw new \RuntimeException('Invalid close relatives table cell.');
                    }
                    $text = trim((string) $value);
                    return function_exists('mb_substr') ? mb_substr($text, 0, 2000) : substr($text, 0, 2000);
                }, array_values($row));
                if ($values[2] === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $values[2])) {
                    throw new \RuntimeException('Invalid GEDCOM number in close relatives table.');
                }
                $values[0] = '';
                $rows[] = $values;
            }

            $credentialsFile = getenv('HUMOGEN_GOOGLE_SERVICE_ACCOUNT') ?: self::GOOGLE_SERVICE_ACCOUNT_FILE;
            if (!is_readable($credentialsFile)) {
                throw new \RuntimeException('Google service-account credentials are unavailable.');
            }

            $client = new \Google\Client();
            $client->setApplicationName('HuMo-genealogy Close Relatives');
            $client->setAuthConfig($credentialsFile);
            $client->setScopes([\Google\Service\Sheets::SPREADSHEETS]);
            $sheets = new \Google\Service\Sheets($client);
            $spreadsheet = $sheets->spreadsheets->get(self::GOOGLE_SHEET_ID, [
                'fields' => 'sheets(properties(title))'
            ]);
            $worksheetExists = false;
            foreach ($spreadsheet->getSheets() as $sheet) {
                if ($sheet->getProperties()->getTitle() === $worksheetTitle) {
                    $worksheetExists = true;
                    break;
                }
            }
            if (!$worksheetExists) {
                $sheets->spreadsheets->batchUpdate(
                    self::GOOGLE_SHEET_ID,
                    new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                        'requests' => [new \Google\Service\Sheets\Request([
                            'addSheet' => new \Google\Service\Sheets\AddSheetRequest([
                                'properties' => new \Google\Service\Sheets\SheetProperties([
                                    'title' => $worksheetTitle
                                ])
                            ])
                        ])]
                    ])
                );
            }

            $range = "'" . str_replace("'", "''", $worksheetTitle) . "'!A:H";
            $header = ['', 'First Name', 'GEDCOM Number', 'Birth Date', 'Death Date', 'Phone Number', 'Address', 'Relation Name'];
            $existingHeader = $sheets->spreadsheets_values->get(
                self::GOOGLE_SHEET_ID,
                "'" . str_replace("'", "''", $worksheetTitle) . "'!A1:H1"
            )->getValues() ?: [];
            $values = empty($existingHeader) ? array_merge([$header], $rows) : $rows;
            $sheets->spreadsheets_values->append(
                self::GOOGLE_SHEET_ID,
                $range,
                new \Google\Service\Sheets\ValueRange(['values' => $values]),
                ['valueInputOption' => 'RAW', 'insertDataOption' => 'INSERT_ROWS']
            );

            echo json_encode(['success' => true, 'worksheet' => $worksheetTitle, 'rows' => count($rows)]);
        } catch (\Throwable $exception) {
            error_log('Close relatives Google Sheets submission failed: ' . $exception->getMessage());
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $exception instanceof \RuntimeException
                ? $exception->getMessage()
                : 'Google Sheets submission failed.']);
        }
    }
}
