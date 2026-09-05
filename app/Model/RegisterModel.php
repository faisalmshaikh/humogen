<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\PasswordPolicy;
use PDO;

class RegisterModel extends BaseModel
{
    /**
     * Write registration-mail diagnostics without logging message contents or
     * credentials. These entries are intended for the PHP/server error log.
     */
    private function registrationMailDebug(string $message, array $context = []): void
    {
        $safeContext = [];
        foreach ($context as $key => $value) {
            if (is_bool($value)) {
                $safeContext[$key] = $value ? 'true' : 'false';
            } elseif (is_scalar($value)) {
                $safeContext[$key] = (string) $value;
            }
        }

        error_log('[HuMo registration mail] ' . $message . ($safeContext ? ' ' . json_encode($safeContext) : ''));
    }

    private function maskedEmail(string $email): string
    {
        $atPosition = strpos($email, '@');
        if ($atPosition === false) {
            return $email === '' ? '(empty)' : '(invalid)';
        }

        return substr($email, 0, min(2, $atPosition)) . '***' . substr($email, $atPosition);
    }

    public function getFormdata(): array
    {
        $register["name"] = '';
        if (isset($_POST['register_name'])) {
            $register["name"] = $_POST['register_name'];
        }

        $register["mail"] = '';
        if (isset($_POST['register_mail'])) {
            $register["mail"] = $_POST['register_mail'];
        }

        $register["text"] = '';
        if (isset($_POST['register_text'])) {
            $register["text"] = $_POST['register_text'];
        }

        return $register;
    }

    public function register_allowed()
    {
        // *** Check block_spam_answer ***
        $register["register_allowed"] = false;
        if (isset($_POST['send_mail']) && (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($this->humo_option["block_spam_answer"]))) {
            $register["register_allowed"] = true;
        }
        if ($this->humo_option["registration_use_spam_question"] != 'y') {
            $register["register_allowed"] = true;
        }
        return $register["register_allowed"];
    }

    public function register_user($register): array
    {
        $register["show_form"] = true;
        $register["error"] = '';
        if (isset($_POST['send_mail']) && $register["register_allowed"] != true) {
            $this->registrationMailDebug('Registration submission was rejected by the registration gate.', [
                'spam_protection_enabled' => ($this->humo_option['registration_use_spam_question'] ?? 'n') === 'y',
            ]);
        }
        if (isset($_POST['send_mail']) && $register["register_allowed"] == true) {
            $this->registrationMailDebug('Registration submission accepted for processing.');
            $usersql = 'SELECT * FROM humo_users WHERE user_name = :user_name';
            $stmt = $this->dbh->prepare($usersql);
            $stmt->execute([':user_name' => $_POST["register_name"]]);
            $userDb = $stmt->fetch(PDO::FETCH_OBJ);

            if (isset($userDb->user_id) || strtolower($_POST["register_name"]) === "admin") {
                $register["error"] = __('ERROR: username already exists');
            }

            $password = (string)($_POST["register_password"] ?? '');
            $repeat_password = (string)($_POST["register_repeat_password"] ?? '');

            if ($password != $repeat_password) {
                $register["error"] = __('ERROR: No identical passwords');
            }

            if (!$register["error"]) {
                $password_error = PasswordPolicy::validate($password);
                if ($password_error !== '') {
                    $register["error"] = __($password_error);
                }
            }

            if ($register["error"]) {
                $this->registrationMailDebug('Registration validation failed; no notification was attempted.', [
                    'validation_error' => $register["error"],
                ]);
            }

            if (!$register["error"]) {
                $register["show_form"] = false;
                $user_register_date = date("Y-m-d H:i");
                $hashToStoreInDb = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO humo_users 
                    (user_name, user_remark, user_register_date, user_mail, user_password_salted, user_group_id)
                    VALUES (:user_name, :user_remark, :user_register_date, :user_mail, :user_password_salted, :user_group_id)";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':user_name' => $_POST["register_name"],
                    ':user_remark' => $_POST["register_text"],
                    ':user_register_date' => $user_register_date,
                    ':user_mail' => $_POST["register_mail"],
                    ':user_password_salted' => $hashToStoreInDb,
                    ':user_group_id' => $this->humo_option["visitor_registration_group"]
                ]);

                // *** Mail new registered user to the administrator ***
                $register_address = '';
                if (isset($this->selectedFamilyTree->tree_email)) {
                    // Used in older HuMo-genealogy versions. Backwards compatible...
                    $register_address = $this->selectedFamilyTree->tree_email;
                }
                if ($this->humo_option["general_email"]) {
                    $register_address = $this->humo_option["general_email"];
                }

                $this->registrationMailDebug('Registration record created; preparing notification.', [
                    'recipient_configured' => trim((string) $register_address) !== '',
                    'recipient' => $this->maskedEmail(trim((string) $register_address)),
                    'mail_mode' => $this->humo_option['mail_auto'] ?? '(unset)',
                ]);

                if (trim((string) $register_address) === '') {
                    $this->registrationMailDebug('Notification skipped because no recipient address was resolved.');
                } elseif (!filter_var($register_address, FILTER_VALIDATE_EMAIL)) {
                    $this->registrationMailDebug('Resolved recipient address is invalid; continuing so PHPMailer diagnostics are captured.', [
                        'recipient' => $this->maskedEmail(trim((string) $register_address)),
                    ]);
                }

                $register_subject = "HuMo-genealogy. " . __('New registered user') . ": " . $_POST['register_name'] . "\n";

                // *** It's better to use plain text in the subject ***
                $register_subject = strip_tags($register_subject, ENT_QUOTES);

                $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
                $register_message .= "<br><br>\n";
                $register_message .= __('New registered user') . "<br>\n";
                $register_message .= __('Name') . ':' . $_POST['register_name'] . "<br>\n";
                $register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";
                $register_message .= $_POST['register_text'] . "<br>\n";

                $humo_option = $this->humo_option; // Used in mail.php
                include_once(__DIR__ . '/../../include/mail.php');
                $this->registrationMailDebug('PHPMailer initialized.', [
                    'mailer' => $mail->Mailer,
                    'smtp_host_configured' => $mail->Host !== '',
                    'smtp_port' => $mail->Port,
                    'smtp_auth' => $mail->SMTPAuth,
                    'smtp_encryption' => $mail->SMTPSecure,
                ]);

                if ((int) ($this->humo_option['smtp_debug'] ?? 0) > 0) {
                    $mail->Debugoutput = function ($message, $level): void {
                        error_log('[HuMo registration mail][PHPMailer ' . $level . '] ' . trim(strip_tags((string) $message)));
                    };
                }

                // *** Set who the message is to be sent from ***
                //$mail->setFrom($_POST['register_mail'], $_POST['register_name']);
                // *** Changed july 2024: Set who the message is to be sent from ***
                if ($this->humo_option["email_sender"] && filter_var($this->humo_option["email_sender"], FILTER_VALIDATE_EMAIL)) {
                    // *** Some providers don't accept other e-mail addresses because of safety reasons! ***
                    $mail->setFrom($this->humo_option["email_sender"], $this->humo_option["email_sender"]);
                } else {
                    $mail->setFrom($_POST['register_mail'], $_POST['register_name']);
                }

                // *** Added july 2024 ***
                $mail->AddReplyTo($_POST['register_mail'], $_POST['register_name']);

                // *** Set who the message is to be sent to ***
                try {
                    $recipientAdded = $mail->addAddress($register_address, $register_address);
                    if (!$recipientAdded) {
                        $this->registrationMailDebug('PHPMailer did not accept the recipient address.', [
                            'recipient' => $this->maskedEmail(trim((string) $register_address)),
                            'error' => $mail->ErrorInfo,
                        ]);
                    }
                } catch (Throwable $exception) {
                    $this->registrationMailDebug('PHPMailer rejected the recipient address.', [
                        'recipient' => $this->maskedEmail(trim((string) $register_address)),
                        'exception' => get_class($exception),
                        'message' => $exception->getMessage(),
                    ]);
                    throw $exception;
                }

                // *** Set the subject line ***
                $mail->Subject = $register_subject;
                $mail->msgHTML($register_message);

                // *** Replace the plain text body with one created manually ***
                //$mail->AltBody = 'This is a plain-text message body';
                try {
                    if (!$mail->send()) {
                        $this->registrationMailDebug('PHPMailer send failed.', [
                            'recipient' => $this->maskedEmail(trim((string) $register_address)),
                            'error' => $mail->ErrorInfo,
                        ]);
                    } else {
                        $this->registrationMailDebug('PHPMailer send completed successfully.', [
                            'recipient' => $this->maskedEmail(trim((string) $register_address)),
                        ]);
                    }
                } catch (Throwable $exception) {
                    $this->registrationMailDebug('PHPMailer send threw an exception.', [
                        'recipient' => $this->maskedEmail(trim((string) $register_address)),
                        'exception' => get_class($exception),
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }
        return $register;
    }
}
