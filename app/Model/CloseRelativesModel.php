<?php

namespace Genealogy\App\Model;

use Genealogy\Include\PersonPrivacy;

class CloseRelativesModel extends BaseModel
{
    public function getGraph(string $gedcomNumber): array
    {
        $allPeople = $this->db_functions->get_persons($this->tree_id);
        $peopleByGedcom = [];
        $people = [];

        $privacyChecker = new PersonPrivacy();
        foreach ($allPeople as $person) {
            $peopleByGedcom[$person->pers_gedcomnumber] = (int) $person->pers_id;
            $privacy = $privacyChecker->get_privacy($person);
            $name = trim($person->pers_firstname . ' ' . $person->pers_prefix . ' ' . $person->pers_lastname);
            $people[(int) $person->pers_id] = [
                'id' => (int) $person->pers_id,
                'gedcom' => $person->pers_gedcomnumber,
                'name' => $privacy ? __('Name filtered') : $name,
                'sex' => $person->pers_sexe,
                'parent_families' => $person->parent_relation_id ? [(int) $person->parent_relation_id] : [],
            ];
        }

        $mainPerson = $this->db_functions->get_person($gedcomNumber);
        if (!$mainPerson || !isset($people[(int) $mainPerson->pers_id])) {
            return ['main_person' => null, 'nodes' => [], 'edges' => []];
        }

        $families = [];
        $relationSql = "SELECT relation_id, person_id, person_gedcomnumber, relation_type
            FROM humo_relations_persons
            WHERE tree_id = :tree_id";
        $relationStmt = $this->dbh->prepare($relationSql);
        $relationStmt->execute([':tree_id' => $this->tree_id]);

        while ($relation = $relationStmt->fetch(\PDO::FETCH_OBJ)) {
            $familyId = (int) $relation->relation_id;
            if (!isset($families[$familyId])) {
                $families[$familyId] = ['partners' => [], 'children' => []];
            }

            if ($relation->relation_type === 'child') {
                $childId = (int) $relation->person_id;
                if (isset($people[$childId])) {
                    $families[$familyId]['children'][] = $childId;
                    $people[$childId]['parent_families'][] = $familyId;
                }
                continue;
            }

            $partnerId = (int) $relation->person_id;
            if (isset($people[$partnerId])) {
                $families[$familyId]['partners'][] = $partnerId;
            }
            if (isset($peopleByGedcom[$relation->person_gedcomnumber])) {
                $families[$familyId]['partners'][] = $peopleByGedcom[$relation->person_gedcomnumber];
            }
        }

        foreach ($families as &$family) {
            $family['partners'] = array_values(array_unique($family['partners']));
            $family['children'] = array_values(array_unique($family['children']));
        }
        unset($family);

        $graph = (new CloseRelativesGraphBuilder())->build($people, $families, (int) $mainPerson->pers_id);
        $graph['main_person'] = $mainPerson->pers_gedcomnumber;
        return $graph;
    }
}
