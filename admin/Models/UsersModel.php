<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use Genealogy\Include\PasswordPolicy;
use PDO;
use PDOException;

class UsersModel extends AdminBaseModel
{
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
                        $password_alert = PasswordPolicy::validate(
                            $new_password,
                            $userDb->user_password_salted ?? null,
                            $userDb->user_password ?? null
                        );
                        if ($password_alert !== '') {
                            $alert = __($password_alert) . '<br>';
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
                $password_alert = PasswordPolicy::validate($add_password);
                if ($password_alert !== '') {
                    return __($password_alert) . '<br>';
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
