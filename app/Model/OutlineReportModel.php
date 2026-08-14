<?php

/**
 * OutlineReportModel.php
 * 
 * Jul. 2025 Huub: changed <div> into <ul> in function outline_report_html.
 */

namespace Genealogy\App\Model;

use Genealogy\App\Model\FamilyModel;

class OutlineReportModel extends FamilyModel
{
    //private $generation_number = 0;
    private $nr_generations;
    private $show_details;
    private $show_date;
    private $dates_behind_names;
    private string $html_output = '';

    public function getShowDetails(): bool
    {
        $show_details = false;
        if (isset($_GET["show_details"]) && is_numeric(($_GET["show_details"]))) {
            $show_details = $_GET["show_details"];
        }
        if (isset($_POST["show_details"]) && is_numeric($_POST["show_details"])) {
            $show_details = $_POST["show_details"];
        }
        $this->show_details = $show_details;
        return $show_details;
    }

    public function getShowDate(): bool
    {
        $show_date = true;
        if (isset($_GET["show_date"]) && is_numeric($_GET["show_date"])) {
            $show_date = $_GET["show_date"];
        }
        if (isset($_POST["show_date"]) && is_numeric($_POST["show_date"])) {
            $show_date = $_POST["show_date"];
        }
        $this->show_date = $show_date;
        return $show_date;
    }

    public function getDatesBehindNames(): bool
    {
        $dates_behind_names = true;
        if (isset($_GET["dates_behind_names"]) && is_numeric($_GET["dates_behind_names"])) {
            $dates_behind_names = $_GET["dates_behind_names"];
        }
        if (isset($_POST["dates_behind_names"]) && is_numeric($_POST["dates_behind_names"])) {
            $dates_behind_names = $_POST["dates_behind_names"];
        }
        $this->dates_behind_names = $dates_behind_names;
        return $dates_behind_names;
    }

    public function getNrGenerations(): int
    {
        $nr_generations = ($this->humo_option["descendant_generations"] - 1);
        if (isset($_GET["nr_generations"]) && is_numeric($_GET["nr_generations"])) {
            $nr_generations = $_GET["nr_generations"];
        }
        if (isset($_POST["nr_generations"]) && is_numeric($_POST["nr_generations"])) {
            $nr_generations = $_POST["nr_generations"];
        }
        $this->nr_generations = $nr_generations;
        return $nr_generations;
    }

    /**
     * Recursive function outline
     */
    public function outline_report_html($outline_family_id, $outline_main_person, $generation_number = 0)
    {
        $personPrivacy = new \Genealogy\Include\PersonPrivacy;
        $personName_extended = new \Genealogy\Include\PersonNameExtended('compact');
        $languageDate = new \Genealogy\Include\LanguageDate;
        $totallyFilterPerson = new \Genealogy\Include\TotallyFilterPerson;

        $family_nr = 1; //*** Process multiple families ***

        $show_privacy_text = false;

        if ($this->nr_generations < $generation_number) {
            return;
        }
        $generation_number++;

        // *** Count marriages of man ***
        // *** YB: if needed show woman as main_person ***
        $familyDb = $this->db_functions->get_family($outline_family_id, 'man-woman');
        $parent1 = '';
        $parent2 = '';
        $swap_parent1_parent2 = false;

        // *** Standard main_person is the father ***
        if ($familyDb->partner1_gedcomnumber) {
            $parent1 = $familyDb->partner1_gedcomnumber;
        }
        // *** If mother is selected, mother will be main_person ***
        if ($familyDb->partner2_gedcomnumber == $outline_main_person) {
            $parent1 = $familyDb->partner2_gedcomnumber;
            $swap_parent1_parent2 = true;
        }

        if ($parent1) {
            $personDb = $this->db_functions->get_person($parent1);
            $relations = $this->db_functions->get_relations($personDb->pers_id);
        }

        // *** Loop multiple marriages of main_person ***
        foreach ($relations as $relation) {
            $familyDb = $this->db_functions->get_family_with_id($relation->relation_id);

            // *** Privacy filter man and woman ***
            $person_manDb = $this->db_functions->get_person($familyDb->partner1_gedcomnumber);
            $privacy_man = $personPrivacy->get_privacy($person_manDb);

            $person_womanDb = $this->db_functions->get_person($familyDb->partner2_gedcomnumber);
            $privacy_woman = $personPrivacy->get_privacy($person_womanDb);

            if ($generation_number === 1 && $family_nr === 1) {
                $this->html_output .= '<table class="table table-sm outline-report-table"><thead><tr>'
                    . '<th>' . __('Generation / Name') . '</th>'
                    . '<th>' . __('GEDCOM number') . '</th>'
                    . '<th>' . __('Birth date') . '</th>'
                    . '<th>' . __('Death date') . '</th>'
                    . '<th>' . __('Phone') . '</th>'
                    . '<th>' . __('Address') . '</th>'
                    . '</tr></thead><tbody>';
            }

            // *** Show parent1 (normally the father) ***
            if ($familyDb->fam_kind != 'PRO-GEN' && $family_nr == 1) {
                $parent1Db = $swap_parent1_parent2 ? $person_womanDb : $person_manDb;
                $parent1Privacy = $swap_parent1_parent2 ? $privacy_woman : $privacy_man;
                $this->appendPersonRow($parent1Db, $parent1Privacy, $generation_number, $personName_extended, $languageDate);
            }
            $family_nr++;

            // *** Show parent2 (normally the mother) ***
            $parent2Db = $swap_parent1_parent2 ? $person_manDb : $person_womanDb;
            $parent2Privacy = $swap_parent1_parent2 ? $privacy_man : $privacy_woman;
            if (!$totallyFilterPerson->isTotallyFiltered($this->user, $parent2Db)) {
                $this->appendPersonRow($parent2Db, $parent2Privacy, $generation_number, $personName_extended, $languageDate, false);
            }

            // *** Show children ***
            $children = $this->db_functions->get_children($familyDb->fam_id);
            if ($children) {
                foreach ($children as $child) {
                    $childDb = $this->db_functions->get_person_with_id($child->person_id);
                    // *** Totally hide children if setting is active ***
                    if ($totallyFilterPerson->isTotallyFiltered($this->user, $childDb)) {
                        if (!$show_privacy_text) {
                            $this->html_output .= '<tr><td colspan="6">' . __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '</td></tr>';
                            $show_privacy_text = true;
                        }
                        continue;
                    }

                    $child_privacy = $personPrivacy->get_privacy($childDb);

                    // *** Build descendant_report ***
                    $first_relation = $this->db_functions->get_first_relation($childDb->pers_id);
                    if (isset($first_relation->relation_gedcomnumber)) {
                        $this->outline_report_html($first_relation->relation_gedcomnumber, $childDb->pers_gedcomnumber, $generation_number);  // recursive
                    } else {
                        // Child without own family
                        if ($this->nr_generations >= $generation_number) {
                            $childgn = $generation_number + 1;
                            $this->appendPersonRow($childDb, $child_privacy, $childgn, $personName_extended, $languageDate);
                        }
                    }
                    $this->html_output .= "\n";
                }
            }
        } // Show  multiple marriages

        if ($generation_number === 1) {
            $this->html_output .= '</tbody></table>';
        }
    }

    public function getHtmlOutput(): string
    {
        return $this->html_output;
    }

    private function appendPersonRow($personDb, $privacy, int $generation, $personName_extended, $languageDate, bool $showGeneration = true): void
    {
        if (!$personDb || $this->isPersonHidden($personDb)) {
            return;
        }

        $name = $personName_extended->name_extended($personDb, $privacy, 'outline');
        $birthDate = $privacy || $this->show_date != '1' ? '' : $languageDate->language_date($personDb->pers_birth_date);
        $deathDate = $privacy || $this->show_date != '1' ? '' : $languageDate->language_date($personDb->pers_death_date);
        $contact = $this->getContactFields($personDb, $privacy);
        $indent = max(0, ($generation - 1) * 20);
        $generationMarker = $showGeneration ? $generation : 'x';
        $birthDateStyle = $personDb->pers_alive == 'alive' && empty($personDb->pers_birth_date)
            ? ' style="background-color:#ffffa0;"'
            : '';
        $calculateDates = new \Genealogy\Include\CalculateDates;
        $birthYear = $calculateDates->search_year($personDb->pers_birth_date);
        $age = $birthYear ? ((int) date('Y') - (int) $birthYear) : null;
        $phoneStyle = $personDb->pers_alive == 'alive' && $age !== null && $age > 20 && empty($contact['phone'])
            ? ' style="background-color:#ffffa0;"'
            : '';

        $this->html_output .= '<tr>'
            . '<td style="padding-inline-start: ' . $indent . 'px;"><span class="generation-number">' . $generationMarker . '</span> ' . $name . '</td>'
            . '<td>' . htmlspecialchars($personDb->pers_gedcomnumber ?? '', ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td' . $birthDateStyle . '>' . htmlspecialchars($birthDate, ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td>' . htmlspecialchars($deathDate, ENT_QUOTES, 'UTF-8') . '</td>'
            . '<td' . $phoneStyle . '>' . $contact['phone'] . '</td>'
            . '<td>' . $contact['address'] . '</td>'
            . '</tr>';
    }

    private function getContactFields($personDb, $privacy): array
    {
        $contact = ['phone' => '', 'address' => ''];
        if ($privacy || $this->user['group_living_place'] != 'j') {
            return $contact;
        }

        $addresses = $this->db_functions->get_addresses('person', 'person_address', $personDb->pers_gedcomnumber);
        if (empty($addresses)) {
            return $contact;
        }

        $address = $addresses[0];
        if (!empty($address->address_phone)) {
            $contact['phone'] = htmlspecialchars($address->address_phone, ENT_QUOTES, 'UTF-8');
        }
        if (!empty($address->address_address)) {
            $contact['address'] = htmlspecialchars($address->address_address, ENT_QUOTES, 'UTF-8');
        }

        return $contact;
    }

    private function isPersonHidden($personDb): bool
    {
        $totallyFilterPerson = new \Genealogy\Include\TotallyFilterPerson;
        return $totallyFilterPerson->isTotallyFiltered($this->user, $personDb);
    }
}
