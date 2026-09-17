<?php

namespace Genealogy\App\Model;

/** Selects the people reachable through the requested close-family branches. */
final class GrowConnectionsSelector
{
    public function missingRelatives(array $people, array $families, int $personId): array
    {
        $parents = $this->parents($people, $families, $personId);
        $parentSiblings = [];
        foreach ($parents as $parentId) {
            $parentSiblings = array_merge($parentSiblings, $this->siblings($people, $families, $parentId));
        }

        $candidateIds = array_merge(
            $parents,
            $this->partners($families, $personId),
            $this->children($families, $personId),
            $this->siblings($people, $families, $personId),
            $parentSiblings
        );
        foreach ($parentSiblings as $uncleOrAuntId) {
            $candidateIds = array_merge($candidateIds, $this->children($families, $uncleOrAuntId));
            $candidateIds = array_merge($candidateIds, $this->partners($families, $uncleOrAuntId));
        }

        $result = [];
        foreach (array_values(array_unique($candidateIds)) as $candidateId) {
            if ($candidateId === $personId || !isset($people[$candidateId]) || trim((string) ($people[$candidateId]['phone'] ?? '')) !== '') {
                continue;
            }
            $result[] = $people[$candidateId] + ['id' => $candidateId];
        }
        return $result;
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
