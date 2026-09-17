<?php

require_once __DIR__ . '/../app/Controller/GrowConnectionsController.php';

use Genealogy\App\Controller\GrowConnectionsController;

$_SESSION = ['grow_connections_html_token' => 'test-token'];
$controller = new GrowConnectionsController(['uri_path' => '/humogen/index.php']);
$reflection = new ReflectionClass($controller);
$buildDocument = $reflection->getMethod('buildExportDocument');
$buildDocument->setAccessible(true);
$sanitizeTable = $reflection->getMethod('sanitizeTable');
$sanitizeTable->setAccessible(true);

$table = '<table id="grow-connections-table" class="table"><thead><tr>'
    . '<th>Gedcom Number</th><th>Name</th><th>Birth Date</th><th>Death Date</th>'
    . '<th>Phone</th><th>Address</th><th>Relation</th></tr></thead><tbody>'
    . '<tr><td>1</td><td>Person</td><td>1900</td><td>1980</td><td>123</td><td>Street</td><td>Sibling</td></tr>'
    . '</tbody></table>';
$sanitized = $sanitizeTable->invoke($controller, $table);
$document = $buildDocument->invoke($controller, $sanitized, 'grow_connections_' . str_repeat('a', 32) . '.html');

foreach ([
    'id="grow-connections-export-edit"',
    '>✎</button>',
    'id="grow-connections-export-save">Save</button>',
    'const growConnectionsEditableColumns=[2,3,4,5]',
    'filename:growConnectionsExportFilename',
    'X-Grow-Connections-HTML-Token',
] as $expected) {
    if (strpos($document, $expected) === false) {
        throw new RuntimeException('Export document is missing: ' . $expected);
    }
}

if (strpos($sanitized, 'id="grow-connections-table"') === false) {
    throw new RuntimeException('Export table id must be retained for editing.');
}
if (strpos($sanitized, '<script') !== false || strpos($sanitized, 'onclick') !== false) {
    throw new RuntimeException('Unsafe markup must be removed from the exported table.');
}

echo "Grow connections export tests passed.\n";
