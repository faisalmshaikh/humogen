<?php

require_once __DIR__ . '/../app/Model/BaseModel.php';
require_once __DIR__ . '/../app/Model/AddressBookModel.php';

use Genealogy\App\Model\AddressBookModel;

function expectContains(string $needle, string $haystack, string $message): void
{
    if (strpos($haystack, $needle) === false) {
        throw new RuntimeException($message . "\nMissing: " . $needle);
    }
}

$contact = [
    'firstname' => 'Ana; Maria', 'lastname' => "O'Neil, Jr", 'gedcom' => 'I3695',
    'phone' => '+44 123,456', 'address' => "1 Main\nStreet", 'place' => 'London',
    'zip' => 'SW1A 1AA', 'birth_year' => '1980', 'birth_month' => '2', 'birth_day' => '3',
    'indexnr' => 'F1163', 'tree_id' => 1,
];

$vcard = AddressBookModel::formatVcard($contact);
expectContains("BEGIN:VCARD\r\nVERSION:3.0\r\n", $vcard, 'vCard header should be present.');
expectContains("N:O'Neil\\, Jr;Ana\\; Maria;;GEDCOM I3695;\r\n", $vcard, 'Name and GEDCOM prefix should be included.');
expectContains("TEL;TYPE=voice:+44 123\\,456\r\n", $vcard, 'Telephone should be included.');
expectContains("ADR;TYPE=home:;;1 Main\\nStreet;;;;\r\n", $vcard, 'Address should be included.');
expectContains("BDAY:19800203\r\n", $vcard, 'Birth date should be formatted.');
expectContains("URL:https://khandesh.co.in/familytree/index.php?page=family&tree_id=1&id=F1163&main_person=I3695\r\n", $vcard, 'Family URL should be included.');

echo "Address book vCard tests passed.\n";
