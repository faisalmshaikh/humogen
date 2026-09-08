<?php

namespace Genealogy\Admin\Migrations;

class Migration23
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Existing accounts predate the explicit active/inactive workflow.
        $this->dbh->exec("UPDATE humo_users SET user_status = 'A' WHERE user_status IS NULL OR user_status = ''");
    }

    public function down(): void
    {
        // Migrations are forward-only in the current update mechanism.
    }
}
