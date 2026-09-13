<?php

namespace Genealogy\Admin\Migrations;

class Migration25
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        $this->dbh->exec("INSERT INTO humo_location (location_location)
            SELECT DISTINCT a.address_place
            FROM humo_addresses a
            WHERE a.address_place IS NOT NULL
                AND a.address_place != ''
                AND a.address_place NOT IN (SELECT location_location FROM humo_location)");
    }

    public function down(): void
    {
        // Migrations are forward-only in the current update mechanism.
    }
}
