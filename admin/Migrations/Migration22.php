<?php

namespace Genealogy\Admin\Migrations;

class Migration22
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        $this->dbh->exec("ALTER TABLE humo_users
            ADD user_father_name VARCHAR(100) CHARACTER SET utf8 DEFAULT '' AFTER user_mail,
            ADD user_mother_name VARCHAR(100) CHARACTER SET utf8 DEFAULT '' AFTER user_father_name,
            ADD user_birth_date DATE DEFAULT NULL AFTER user_mother_name,
            ADD user_reference_name VARCHAR(100) CHARACTER SET utf8 DEFAULT '' AFTER user_birth_date,
            ADD user_address TEXT CHARACTER SET utf8 AFTER user_reference_name,
            ADD user_marital_status VARCHAR(20) CHARACTER SET utf8 DEFAULT '' AFTER user_address,
            ADD user_paternal_grandparent_names VARCHAR(200) CHARACTER SET utf8 DEFAULT '' AFTER user_marital_status,
            ADD user_maternal_grandparent_names VARCHAR(200) CHARACTER SET utf8 DEFAULT '' AFTER user_paternal_grandparent_names,
            ADD user_phone VARCHAR(50) CHARACTER SET utf8 DEFAULT '' AFTER user_maternal_grandparent_names");
    }

    public function down(): void
    {
        // Migrations are forward-only in the current update mechanism.
    }
}
