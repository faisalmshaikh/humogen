<?php

namespace Genealogy\Admin\Migrations;

class Migration26
{
    public function __construct(private $dbh)
    {
    }

    public function up(): void
    {
        $this->dbh->exec("CREATE TABLE IF NOT EXISTS humo_user_social_logins (
            social_login_id bigint unsigned NOT NULL AUTO_INCREMENT,
            user_id smallint(5) unsigned NOT NULL,
            provider varchar(20) NOT NULL,
            provider_user_id varchar(255) NOT NULL,
            provider_email varchar(100) NOT NULL DEFAULT '',
            created_at datetime NOT NULL,
            PRIMARY KEY (social_login_id),
            UNIQUE KEY user_social_provider (user_id, provider),
            UNIQUE KEY provider_social_user (provider, provider_user_id),
            KEY social_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function down(): void
    {
        // Migrations are forward-only in the current update mechanism.
    }
}
