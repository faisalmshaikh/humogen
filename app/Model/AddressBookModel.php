<?php

namespace Genealogy\App\Model;

use PDO;

class AddressBookModel extends BaseModel
{
    public function getVcardFile(): string
    {
        $sql = "SELECT p.pers_firstname, p.pers_prefix, p.pers_lastname,
                    p.pers_gedcomnumber, p.pers_indexnr, p.pers_tree_id,
                    a.address_phone, a.address_address, a.address_place, a.address_zip,
                    birth.date_year AS birth_year, birth.date_month AS birth_month,
                    birth.date_day AS birth_day
                FROM humo_persons p
                INNER JOIN humo_connections c ON c.connect_tree_id = p.pers_tree_id
                    AND c.connect_connect_id = p.pers_gedcomnumber
                    AND c.connect_kind = 'person' AND c.connect_sub_kind = 'person_address'
                INNER JOIN humo_addresses a ON a.address_tree_id = c.connect_tree_id
                    AND a.address_gedcomnr = c.connect_item_id
                LEFT JOIN humo_events birth ON birth.person_id = p.pers_id
                    AND birth.event_kind = 'birth'
                WHERE p.pers_tree_id = :tree_id
                    AND TRIM(COALESCE(a.address_phone, '')) <> ''
                ORDER BY p.pers_lastname, p.pers_firstname, p.pers_id, c.connect_order";
        $statement = $this->dbh->prepare($sql);
        $statement->execute([':tree_id' => $this->tree_id]);
        $contacts = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $gedcom = (string) ($row['pers_gedcomnumber'] ?? '');
            if ($gedcom === '') {
                continue;
            }
            if (!isset($contacts[$gedcom])) {
                $contacts[$gedcom] = [
                    'firstname' => (string) ($row['pers_firstname'] ?? ''),
                    'prefix' => (string) ($row['pers_prefix'] ?? ''),
                    'lastname' => (string) ($row['pers_lastname'] ?? ''),
                    'gedcom' => $gedcom,
                    'indexnr' => (string) ($row['pers_indexnr'] ?? ''),
                    'tree_id' => (int) $row['pers_tree_id'],
                    'phones' => [], 'addresses' => [],
                    'birth_year' => $row['birth_year'] ?? '',
                    'birth_month' => $row['birth_month'] ?? '',
                    'birth_day' => $row['birth_day'] ?? '',
                ];
            }
            $phone = trim((string) ($row['address_phone'] ?? ''));
            if ($phone !== '' && !in_array($phone, $contacts[$gedcom]['phones'], true)) {
                $contacts[$gedcom]['phones'][] = $phone;
            }
            $address = trim(implode(' ', array_filter([
                $row['address_address'] ?? '', $row['address_place'] ?? '', $row['address_zip'] ?? '',
            ], static fn ($value): bool => trim((string) $value) !== '')));
            if ($address !== '' && !in_array($address, $contacts[$gedcom]['addresses'], true)) {
                $contacts[$gedcom]['addresses'][] = $address;
            }
        }

        $output = '';
        foreach ($contacts as $contact) {
            $contact['phone'] = implode('; ', $contact['phones']);
            $contact['address'] = implode('; ', $contact['addresses']);
            $output .= self::formatVcard($contact);
        }
        return $output;
    }

    public function getPersonVcard(string $gedcomNumber): string
    {
        $sql = "SELECT p.pers_firstname, p.pers_prefix, p.pers_lastname,
                    p.pers_gedcomnumber, p.pers_indexnr, p.pers_tree_id,
                    a.address_phone, a.address_address, a.address_place, a.address_zip,
                    birth.date_year AS birth_year, birth.date_month AS birth_month,
                    birth.date_day AS birth_day
                FROM humo_persons p
                INNER JOIN humo_connections c ON c.connect_tree_id = p.pers_tree_id
                    AND c.connect_connect_id = p.pers_gedcomnumber
                    AND c.connect_kind = 'person' AND c.connect_sub_kind = 'person_address'
                INNER JOIN humo_addresses a ON a.address_tree_id = c.connect_tree_id
                    AND a.address_gedcomnr = c.connect_item_id
                LEFT JOIN humo_events birth ON birth.person_id = p.pers_id
                    AND birth.event_kind = 'birth'
                WHERE p.pers_tree_id = :tree_id
                    AND p.pers_gedcomnumber = :gedcom
                    AND TRIM(COALESCE(a.address_phone, '')) <> ''
                ORDER BY c.connect_order";
        $statement = $this->dbh->prepare($sql);
        $statement->execute([':tree_id' => $this->tree_id, ':gedcom' => $gedcomNumber]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return '';
        }

        $first = $rows[0];
        $phones = [];
        $addresses = [];
        foreach ($rows as $row) {
            $phone = trim((string) ($row['address_phone'] ?? ''));
            if ($phone !== '' && !in_array($phone, $phones, true)) {
                $phones[] = $phone;
            }
            $address = trim(implode(' ', array_filter([
                $row['address_address'] ?? '', $row['address_place'] ?? '', $row['address_zip'] ?? '',
            ], static fn ($value): bool => trim((string) $value) !== '')));
            if ($address !== '' && !in_array($address, $addresses, true)) {
                $addresses[] = $address;
            }
        }

        return self::formatVcard([
            'firstname' => $first['pers_firstname'] ?? '', 'prefix' => $first['pers_prefix'] ?? '',
            'lastname' => $first['pers_lastname'] ?? '', 'gedcom' => $first['pers_gedcomnumber'] ?? '',
            'indexnr' => $first['pers_indexnr'] ?? '', 'tree_id' => $first['pers_tree_id'] ?? $this->tree_id,
            'phone' => implode('; ', $phones), 'address' => implode('; ', $addresses),
            'birth_year' => $first['birth_year'] ?? '', 'birth_month' => $first['birth_month'] ?? '',
            'birth_day' => $first['birth_day'] ?? '',
        ]);
    }

    public static function formatVcard(array $contact): string
    {
        $firstname = (string) ($contact['firstname'] ?? '');
        $lastname = (string) ($contact['lastname'] ?? '');
        $gedcom = (string) ($contact['gedcom'] ?? '');
        $fullname = trim(implode(' ', array_filter([$firstname, $contact['prefix'] ?? '', $lastname])));
        $familyUrl = self::getFamilyUrl() . '&' . http_build_query([
            'tree_id' => (int) ($contact['tree_id'] ?? 0),
            'id' => (string) ($contact['indexnr'] ?? ''),
            'main_person' => $gedcom,
        ], '', '&', PHP_QUERY_RFC3986);
        $lines = [
            'BEGIN:VCARD', 'VERSION:3.0', 'FN:' . self::escape($fullname),
            'N:' . self::escape($lastname) . ';' . self::escape($firstname) . ';;' . self::escape($gedcom) . ';',
            'TEL;TYPE=voice:' . self::escape((string) ($contact['phone'] ?? '')),
            'ADR;TYPE=home:;;' . self::escape((string) ($contact['address'] ?? '')) . ';;;;',
            'URL:' . $familyUrl, 'X-GEDCOM-NUMBER:' . self::escape($gedcom),
        ];
        $birthDate = self::formatBirthDate($contact);
        if ($birthDate !== '') {
            $lines[] = 'BDAY:' . $birthDate;
        }
        $lines[] = 'END:VCARD';
        return implode("\r\n", $lines) . "\r\n";
    }

    private static function escape(string $value): string
    {
        return str_replace(["\\", ";", ",", "\r\n", "\r", "\n"], ["\\\\", "\\;", "\\,", "\\n", "\\n", "\\n"], $value);
    }

    private static function getFamilyUrl(): string
    {
        $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https')
            ? 'https'
            : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $applicationPath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        return $scheme . '://' . $host . $applicationPath . '/index.php?page=family';
    }

    private static function formatBirthDate(array $contact): string
    {
        $year = (int) ($contact['birth_year'] ?? 0);
        $month = (int) ($contact['birth_month'] ?? 0);
        $day = (int) ($contact['birth_day'] ?? 0);
        if ($year < 1) return '';
        if ($month < 1 || $month > 12) return sprintf('%04d', $year);
        if ($day < 1 || $day > 31) return sprintf('%04d%02d', $year, $month);
        return sprintf('%04d%02d%02d', $year, $month, $day);
    }
}
