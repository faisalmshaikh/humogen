<?php

namespace Genealogy\App\Model;

final class CloseRelativesGraphBuilder
{
    public function build(array $people, array $families, int $mainPersonId): array
    {
        $nodes = [];
        $edges = [];

        $addNode = function (int $personId) use (&$nodes, $people): void {
            if (isset($people[$personId])) {
                $nodes[$personId] = $people[$personId];
            }
        };
        $addEdge = function (int $from, int $to, string $label = '', string $style = 'solid') use (&$edges, $people): void {
            if (!isset($people[$from], $people[$to]) || $from === $to) {
                return;
            }
            $key = $from . ':' . $to . ':' . $label;
            $edges[$key] = ['from' => $from, 'to' => $to, 'label' => $label, 'style' => $style];
        };

        $partners = function (int $personId) use ($families): array {
            $result = [];
            foreach ($families as $family) {
                if (in_array($personId, $family['partners'], true)) {
                    foreach ($family['partners'] as $partnerId) {
                        if ($partnerId !== $personId) {
                            $result[] = $partnerId;
                        }
                    }
                }
            }
            return array_values(array_unique($result));
        };
        $parents = function (int $personId) use ($families, $people): array {
            foreach ($people[$personId]['parent_families'] ?? [] as $familyId) {
                if (!isset($families[$familyId])) {
                    continue;
                }
                return array_values(array_filter($families[$familyId]['partners'], static fn (int $id): bool => $id !== $personId));
            }
            return [];
        };
        $children = function (int $personId) use ($families): array {
            $result = [];
            foreach ($families as $family) {
                if (in_array($personId, $family['partners'], true)) {
                    $result = array_merge($result, $family['children']);
                }
            }
            return array_values(array_unique($result));
        };
        $siblings = function (int $personId) use ($families, $people): array {
            foreach ($people[$personId]['parent_families'] ?? [] as $familyId) {
                if (isset($families[$familyId])) {
                    return array_values(array_filter($families[$familyId]['children'], static fn (int $id): bool => $id !== $personId));
                }
            }
            return [];
        };
        $mother = function (int $personId) use ($parents, $people): ?int {
            foreach ($parents($personId) as $parentId) {
                if (($people[$parentId]['sex'] ?? '') === 'F') {
                    return $parentId;
                }
            }
            return null;
        };

        $addNode($mainPersonId);
        $spouseIds = $partners($mainPersonId);
        if ($spouseIds) {
            $addNode($spouseIds[0]);
            $addEdge($mainPersonId, $spouseIds[0], 'Spouse', 'dotted');
        }

        $parentTargets = array_values(array_unique(array_merge([$mainPersonId], $spouseIds ? [$spouseIds[0]] : [])));
        foreach ($parentTargets as $targetId) {
            foreach ($parents($targetId) as $parentId) {
                $addNode($parentId);
                $addEdge($parentId, $targetId);
            }
        }

        foreach ($parents($mainPersonId) as $parentId) {
            foreach ($parents($parentId) as $grandparentId) {
                $addNode($grandparentId);
                $addEdge($grandparentId, $parentId);
            }
        }

        $addSiblingBranch = function (int $personId) use ($siblings, $children, $mother, $addNode, $addEdge): void {
            foreach ($siblings($personId) as $siblingId) {
                $addNode($siblingId);
                $motherId = $mother($siblingId);
                if ($motherId !== null) {
                    $addNode($motherId);
                    $addEdge($motherId, $siblingId);
                }
                foreach ($children($siblingId) as $childId) {
                    $addNode($childId);
                    $addEdge($siblingId, $childId);
                }
            }
        };

        $addSiblingBranch($mainPersonId);
        foreach ($parents($mainPersonId) as $parentId) {
            $addSiblingBranch($parentId);
        }

        return ['nodes' => array_values($nodes), 'edges' => array_values($edges)];
    }
}
