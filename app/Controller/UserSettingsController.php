<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\UserSettingsModel;
use Genealogy\App\Service\SocialAuthService;

class UserSettingsController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function user_settings(): array
    {
        $user_settingsModel = new UserSettingsModel($this->config);

        $result_message = $user_settingsModel->updateSettings();

        // Reload user settings (needed for 2FA).
        $user_settingsModel->getUser();
        $twofa = $user_settingsModel->showQRcode();

        $get_userDb = $user_settingsModel->getUserDb();
        $socialAuth = new SocialAuthService($this->config['dbh']);

        $data = array(
            "user" => $get_userDb,
            "result_message" => $result_message,
            "social_error" => $socialAuth->consumeError(),
            "social_csrf" => $socialAuth->csrfToken(),
            "linked_providers" => $get_userDb ? $socialAuth->linkedProviders((int) $get_userDb->user_id) : [],
        );

        if (isset($twofa)) {
            $data = array_merge($data, $twofa);
        }

        return $data;
    }
}
