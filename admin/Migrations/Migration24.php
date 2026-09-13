<?php

namespace Genealogy\Admin\Migrations;

class Migration24
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        $this->dbh->exec("ALTER TABLE humo_users
            ADD user_full_name VARCHAR(100) CHARACTER SET utf8 DEFAULT '' AFTER user_name");
    }

    public function down(): void
    {
        // Migrations are forward-only in the current update mechanism.
    }
}
