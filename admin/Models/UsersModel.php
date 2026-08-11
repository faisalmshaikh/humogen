<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;
use PDOException;

class UsersModel extends AdminBaseModel
{
    /**
     * Validate a new password and, when changing one, ensure it differs from
     * the existing password.
     */
    private function validate_password(string $password, ?string $current_password_hash = null, ?string $legacy_password_hash = null): string
    {
        if (strlen($password) < 8) {
            return __('Error: password must be at least 8 characters long.') . '<br>';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return __('Error: password must contain at least one uppercase letter.') . '<br>';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return __('Error: password must contain at least one lowercase letter.') . '<br>';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return __('Error: password must contain at least one digit.') . '<br>';
        }
        if (!preg_match('/[^a-zA-Z0-9\s]/', $password)) {
            return __('Error: password must contain at least one special character.') . '<br>';
        }

        if ($current_password_hash !== null && $current_password_hash !== '' && password_verify($password, $current_password_hash)) {
            return __('Error: new password cannot be the same as the old password.') . '<br>';
        }

        // Existing installations may still have only the legacy MD5 value.
        if ($legacy_password_hash !== null && $legacy_password_hash !== '' && hash_equals(strtolower($legacy_password_hash), md5($password))) {
            return __('Error: new password cannot be the same as the old password.') . '<br>';
        }

        return '';
    }

    function update_user(): string
    {
        $alert = '';
        if (isset($_POST['change_user'])) {
            $usersql = "SELECT * FROM humo_users ORDER BY user_name";
            $user = $this->dbh->query($usersql);
            while ($userDb = $user->fetch(PDO::FETCH_OBJ)) {
                if (is_numeric($_POST[$userDb->user_id . "group_id"]) && is_numeric($_POST[$userDb->user_id . "user_id"])) {
                    $username = $_POST[$userDb->user_id . "username"];
                    $usermail = $_POST[$userDb->user_id . "usermail"];
                    if ($_POST[$userDb->user_id . "username"] == "") {
                        $username = 'GEEN NAAM / NO NAME';
                    }

                    $update_fields = [
                        'user_name' => $username,
                        'user_mail' => $usermail,
                        'user_group_id' => $_POST[$userDb->user_id . "group_id"]
                    ];
                    $set_clause = "user_name = :user_name, user_mail = :user_mail, ";
                    $params = [
                        ':user_name' => $update_fields['user_name'],
                        ':user_mail' => $update_fields['user_mail'],
                        ':user_group_id' => $update_fields['user_group_id'],
                        ':user_id' => $_POST[$userDb->user_id . "user_id"]
                    ];
                    $new_password = (string)($_POST[$userDb->user_id . "password"] ?? '');
                    if ($new_password !== '') {
                        $password_alert = $this->validate_password(
                            $new_password,
                            $userDb->user_password_salted ?? null,
                            $userDb->user_password ?? null
                        );
                        if ($password_alert !== '') {
                            $alert = $password_alert;
                            continue;
                        }

                        $hashToStoreInDb = password_hash($new_password, PASSWORD_DEFAULT);
                        $set_clause .= "user_password_salted = :user_password_salted, user_password = '', ";
                        $params[':user_password_salted'] = $hashToStoreInDb;
                    }
                    $set_clause .= "user_group_id = :user_group_id";
                    $sql = "UPDATE humo_users SET $set_clause WHERE user_id = :user_id";
                    try {
                        $stmt = $this->dbh->prepare($sql);
                        $stmt->execute($params);
                    } catch (PDOException $e) {
                        $alert = __('Error: user name probably allready exist.') . '<br>';
                    }

                }
            }
        }

        if (isset($_POST['add_user']) && is_numeric($_POST["add_group_id"])) {
            // Validate username and password are not empty
            $add_username = trim($_POST["add_username"] ?? '');
            $add_password = (string)($_POST["add_password"] ?? '');
            
            if (empty($add_username)) {
                $alert = __('Error: username cannot be empty.') . '<br>';
            } elseif ($add_password === '') {
                $alert = __('Error: password cannot be empty.') . '<br>';
            } else {
                $password_alert = $this->validate_password($add_password);
                if ($password_alert !== '') {
                    return $password_alert;
                }

                $user_prep = $this->dbh->prepare("INSERT INTO humo_users SET
                    user_name=:add_username, user_mail=:add_usermail,
                    user_password_salted=:add_password_salted, user_group_id=:add_group_id");
                $user_prep->bindValue(':add_username', $add_username, PDO::PARAM_STR);
                $user_prep->bindValue(':add_usermail', $_POST["add_usermail"]);
                $hashToStoreInDb = password_hash($add_password, PASSWORD_DEFAULT);
                $user_prep->bindValue(':add_password_salted', $hashToStoreInDb);
                $user_prep->bindValue(':add_group_id', $_POST["add_group_id"], PDO::PARAM_INT);
                try {
                    $user_prep->execute();
                } catch (PDOException $e) {
                    $alert =  __('Error: user name probably allready exist.') . '<br>';
                }
            }
        }

        if (isset($_POST['remove_user2']) && is_numeric($_POST['remove_user'])) {
            // *** Delete source connection ***
            $sql = "DELETE FROM humo_users WHERE user_id = :user_id";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':user_id', $_POST['remove_user'], PDO::PARAM_INT);
            $stmt->execute();
        }

        if (isset($_GET['unblock_ip_address'])) {
            $sql = "DELETE FROM humo_user_log WHERE log_ip_address = :ip_address AND log_status = 'failed'";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':ip_address', $_GET['unblock_ip_address'], PDO::PARAM_STR);
            $stmt->execute();
        }

        return $alert;
    }

    public function check_username_password(): array
    {
        // *** Check for standard admin username and password ***
        $user['check_admin_user'] = false;
        $user['check_admin_pw'] = false;
        $sql = "SELECT * FROM humo_users WHERE user_group_id='1'";
        $check_login = $this->dbh->query($sql);
        while ($check_loginDb = $check_login->fetch(PDO::FETCH_OBJ)) {
            if ($check_loginDb->user_name == 'admin') {
                $user['check_admin_user'] = true;
            }
            // *** Check old password method ***
            if ($check_loginDb->user_password == MD5('humogen')) {
                $user['check_admin_pw'] = true;
            }
            $stored_password_hash = $check_loginDb->user_password_salted ?? null;
            if (is_string($stored_password_hash) && $stored_password_hash !== '') {
                $check_password = password_verify('humogen', $stored_password_hash);
            } else {
                $check_password = false;
            }
            if ($check_password) {
                $user['check_admin_pw'] = true;
            }
        }

        return $user;
    }
}
