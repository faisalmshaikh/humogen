<?php

namespace Genealogy\App\Model;

use PDO;

class GrowConnectionsModel extends BaseModel
{
    public function getData(?string $sourceGedcom = null, string $sortOrder = 'asc'): array
    {
        $people = [];
        $peopleIdByGedcom = [];
        foreach ($this->db_functions->get_persons($this->tree_id) as $person) {
            $gedcom = (string) $person->pers_gedcomnumber;
            $people[(int) $person->pers_id] = [
                'id' => (int) $person->pers_id, 'gedcom' => $gedcom,
                'family_id' => trim((string) ($person->pers_indexnr ?? '')),
                'name' => trim(implode(' ', array_filter([$person->pers_firstname, str_replace('_', ' ', $person->pers_prefix), $person->pers_lastname]))),
                'sex' => $person->pers_sexe,
                'birth_date' => trim((string) ($person->pers_birth_date ?? '')),
                'death_date' => trim((string) ($person->pers_death_date ?? '')), 'phone' => '', 'address' => '',
                'parent_families' => [],
            ];
            $peopleIdByGedcom[$gedcom] = (int) $person->pers_id;
        }

        $contacts = $this->dbh->prepare("SELECT c.connect_connect_id AS gedcom, a.address_phone, a.address_address, a.address_zip, a.address_place
            FROM humo_connections c INNER JOIN humo_addresses a ON a.address_tree_id = c.connect_tree_id AND a.address_gedcomnr = c.connect_item_id
            WHERE c.connect_tree_id = :tree_id AND c.connect_kind = 'person' AND c.connect_sub_kind = 'person_address'
            ORDER BY c.connect_order");
        $contacts->execute([':tree_id' => $this->tree_id]);
        $peopleByGedcom = [];
        foreach ($people as $person) $peopleByGedcom[$person['gedcom']] = ['phones' => [], 'addresses' => []];
        foreach ($contacts->fetchAll(PDO::FETCH_ASSOC) as $contact) {
            $gedcom = (string) $contact['gedcom'];
            if (!array_key_exists($gedcom, $peopleByGedcom)) continue;
            $phone = trim((string) ($contact['address_phone'] ?? ''));
            $address = trim(implode(' ', array_filter([$contact['address_address'] ?? '', $contact['address_zip'] ?? '', $contact['address_place'] ?? ''])));
            if ($phone !== '' && !in_array($phone, $peopleByGedcom[$gedcom]['phones'] ?? [], true)) $peopleByGedcom[$gedcom]['phones'][] = $phone;
            if ($address !== '' && !in_array($address, $peopleByGedcom[$gedcom]['addresses'] ?? [], true)) $peopleByGedcom[$gedcom]['addresses'][] = $address;
        }
        foreach ($people as &$person) {
            $contact = $peopleByGedcom[$person['gedcom']];
            $person['phone'] = implode('; ', $contact['phones'] ?? []);
            $person['address'] = implode('; ', $contact['addresses'] ?? []);
        }
        unset($person);

        $families = [];
        $relations = $this->dbh->prepare("SELECT relation_id, person_id, person_gedcomnumber, relation_type FROM humo_relations_persons WHERE tree_id = :tree_id");
        $relations->execute([':tree_id' => $this->tree_id]);
        foreach ($relations->fetchAll(PDO::FETCH_ASSOC) as $relation) {
            $familyId = (int) $relation['relation_id'];
            $families[$familyId] ??= ['partners' => [], 'children' => []];
            $personId = (int) $relation['person_id'];
            if (!isset($people[$personId])) continue;
            if ($relation['relation_type'] === 'child') {
                $families[$familyId]['children'][] = $personId;
                $people[$personId]['parent_families'][] = $familyId;
            } else {
                $families[$familyId]['partners'][] = $personId;
                $partnerGedcom = (string) ($relation['person_gedcomnumber'] ?? '');
                if (isset($peopleIdByGedcom[$partnerGedcom])) $families[$familyId]['partners'][] = $peopleIdByGedcom[$partnerGedcom];
            }
        }
        foreach ($families as &$family) {
            $family['partners'] = array_values(array_unique($family['partners']));
            $family['children'] = array_values(array_unique($family['children']));
        }
        unset($family);

        $selector = new GrowConnectionsSelector();
        $sources = [];
        foreach ($people as $person) {
            if ($person['death_date'] !== '' || $person['phone'] === '') continue;
            $person['reach_count'] = count($selector->missingRelatives($people, $families, $person['id']));
            $sources[] = $person;
        }
        usort($sources, static function (array $a, array $b) use ($sortOrder): int {
            $comparison = $a['reach_count'] <=> $b['reach_count'];
            return $sortOrder === 'desc' ? -$comparison : ($comparison !== 0 ? $comparison : strcasecmp($a['name'], $b['name']));
        });

        $missing = [];
        $source = null;
        if ($sourceGedcom !== null) {
            foreach ($sources as $person) if ($person['gedcom'] === $sourceGedcom) $source = $person;
            if ($source) $missing = $selector->missingRelativesWithRelations($people, $families, $source['id']);
        }
        return ['sources' => $sources, 'source' => $source, 'missing' => $missing];
    }
}
