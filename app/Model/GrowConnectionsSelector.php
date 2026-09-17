<?php

namespace Genealogy\App\Model;

/** Selects the people reachable through the requested close-family branches. */
final class GrowConnectionsSelector
{
    public function missingRelatives(array $people, array $families, int $personId): array
    {
        return array_map(static function (array $relative): array {
            unset($relative['relation']);
            return $relative;
        }, $this->missingRelativesWithRelations($people, $families, $personId));
    }

    public function missingRelativesWithRelations(array $people, array $families, int $personId): array
    {
        $parents = $this->parents($people, $families, $personId);
        $parentSiblings = [];
        foreach ($parents as $parentId) {
            $parentSiblings = array_merge($parentSiblings, $this->siblings($people, $families, $parentId));
        }

        $relations = [];
        foreach ($parents as $parentId) $relations[$parentId] = $this->genderedLabel($people, $parentId, 'Father', 'Mother');
        foreach ($this->partners($families, $personId) as $relativeId) $relations[$relativeId] = 'Spouse';
        foreach ($this->children($families, $personId) as $relativeId) $relations[$relativeId] = 'Child';
        foreach ($this->siblings($people, $families, $personId) as $relativeId) $relations[$relativeId] = $this->genderedLabel($people, $relativeId, 'Brother', 'Sister');
        $candidateIds = array_merge($parents, array_keys($relations), $parentSiblings);
        foreach ($parentSiblings as $uncleOrAuntId) {
            $side = 'Paternal';
            foreach ($parents as $parentId) {
                if (in_array($uncleOrAuntId, $this->siblings($people, $families, $parentId), true)) {
                    $side = (($people[$parentId]['sex'] ?? '') === 'F') ? 'Maternal' : 'Paternal';
                    break;
                }
            }
            $relations[$uncleOrAuntId] = $side . ' ' . $this->genderedLabel($people, $uncleOrAuntId, 'Uncle', 'Aunt');
            foreach ($this->children($families, $uncleOrAuntId) as $relativeId) {
                $candidateIds[] = $relativeId;
                $relations[$relativeId] ??= $side . ' Cousin';
            }
            foreach ($this->partners($families, $uncleOrAuntId) as $relativeId) {
                $candidateIds[] = $relativeId;
                $relations[$relativeId] ??= $side . ' ' . $this->genderedLabel($people, $relativeId, 'Uncle', 'Aunt');
            }
        }

        $result = [];
        foreach (array_values(array_unique($candidateIds)) as $candidateId) {
            if ($candidateId === $personId || !isset($people[$candidateId]) || trim((string) ($people[$candidateId]['phone'] ?? '')) !== '') {
                continue;
            }
            $result[] = $people[$candidateId] + ['id' => $candidateId, 'relation' => $relations[$candidateId] ?? 'Close relative'];
        }
        return $result;
    }

    private function genderedLabel(array $people, int $personId, string $male, string $female): string
    {
        return (($people[$personId]['sex'] ?? '') === 'F') ? $female : $male;
    }

    private function parents(array $people, array $families, int $personId): array
    {
        $result = [];
        foreach ($people[$personId]['parent_families'] ?? [] as $familyId) {
            foreach ($families[$familyId]['partners'] ?? [] as $parentId) {
                if ($parentId !== $personId) $result[] = $parentId;
            }
        }
        return array_values(array_unique($result));
    }

    private function partners(array $families, int $personId): array
    {
        $result = [];
        foreach ($families as $family) {
            if (in_array($personId, $family['partners'] ?? [], true)) {
                foreach ($family['partners'] as $partnerId) if ($partnerId !== $personId) $result[] = $partnerId;
            }
        }
        return array_values(array_unique($result));
    }

    private function children(array $families, int $personId): array
    {
        $result = [];
        foreach ($families as $family) {
            if (in_array($personId, $family['partners'] ?? [], true)) $result = array_merge($result, $family['children'] ?? []);
        }
        return array_values(array_unique($result));
    }

    private function siblings(array $people, array $families, int $personId): array
    {
        $result = [];
        foreach ($people[$personId]['parent_families'] ?? [] as $familyId) {
            $result = array_merge($result, $families[$familyId]['children'] ?? []);
        }
        return array_values(array_filter(array_unique($result), static fn (int $id): bool => $id !== $personId));
    }
}
