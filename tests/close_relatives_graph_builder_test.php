<?php

require_once __DIR__ . '/../app/Model/CloseRelativesGraphBuilder.php';

use Genealogy\App\Model\CloseRelativesGraphBuilder;

$people = [];
foreach (range(1, 14) as $id) {
    $people[$id] = ['id' => $id, 'sex' => in_array($id, [2, 4, 6, 8, 10, 12, 14], true) ? 'F' : 'M', 'name' => 'Person ' . $id, 'parent_families' => []];
}
$families = [
    1 => ['partners' => [3, 4], 'children' => [1, 5],],
    2 => ['partners' => [7, 8], 'children' => [3, 9],],
    3 => ['partners' => [11, 12], 'children' => [7, 13],],
    4 => ['partners' => [1, 2], 'children' => [3, 5],],
    5 => ['partners' => [5, 6], 'children' => [14],],
];
$people[1]['parent_families'] = [1];
$people[2]['parent_families'] = [4];
$people[3]['parent_families'] = [2];
$people[5]['parent_families'] = [1];
$people[7]['parent_families'] = [3];
$people[9]['parent_families'] = [2];
$people[13]['parent_families'] = [3];
$people[14]['parent_families'] = [5];

$graph = (new CloseRelativesGraphBuilder())->build($people, $families, 1);
$edgeKeys = array_map(static fn (array $edge): string => $edge['from'] . ':' . $edge['to'] . ':' . $edge['label'], $graph['edges']);

foreach (['1:2:Spouse', '3:1:', '4:1:', '4:5:', '5:14:', '7:3:'] as $expected) {
    if (!in_array($expected, $edgeKeys, true)) {
        throw new RuntimeException('Missing expected edge: ' . $expected);
    }
}

echo "Close relatives graph tests passed.\n";
