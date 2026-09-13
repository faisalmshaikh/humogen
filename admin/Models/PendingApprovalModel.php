<?php

namespace Genealogy\Admin\Models;

use PDO;

class PendingApprovalModel extends AdminBaseModel
{
    public function getPendingUsers(): array
    {
        $sql = "SELECT * FROM humo_users
            WHERE user_status = 'I' OR user_group_id = 2
            ORDER BY user_register_date ASC, user_id ASC";

        return $this->dbh->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function getUserGroups(): array
    {
        return $this->dbh->query('SELECT group_id, group_name FROM humo_groups ORDER BY group_id')
            ->fetchAll(PDO::FETCH_OBJ);
    }

    public function approveUser(): string
    {
        if (!isset($_POST['approve_user'])) {
            return '';
        }

        $userId = filter_var($_POST['approved_user_id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($userId === false) {
            return __('Please select a user.');
        }

        $groupIds = is_array($_POST['approved_group_id'] ?? null) ? $_POST['approved_group_id'] : [];
        $groupId = filter_var($groupIds[$userId] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($groupId === false) {
            return __('Please select a valid user group.');
        }

        $groupStmt = $this->dbh->prepare('SELECT group_id FROM humo_groups WHERE group_id = :group_id');
        $groupStmt->execute([':group_id' => $groupId]);
        if (!$groupStmt->fetch(PDO::FETCH_OBJ)) {
            return __('Please select a valid user group.');
        }

        $userStmt = $this->dbh->prepare("SELECT * FROM humo_users
            WHERE user_id = :user_id AND (user_status = 'I' OR user_group_id = 2)");
        $userStmt->execute([':user_id' => $userId]);
        $user = $userStmt->fetch(PDO::FETCH_OBJ);
        if (!$user) {
            return __('The selected user is no longer awaiting approval.');
        }

        $gedcomNumbers = is_array($_POST['approved_gedcom_nbr'] ?? null) ? $_POST['approved_gedcom_nbr'] : [];
        $gedcomNumber = substr(trim((string) ($gedcomNumbers[$userId] ?? '')), 0, 7);
        $updateStmt = $this->dbh->prepare("UPDATE humo_users SET
            user_gedcomnbr = :user_gedcomnbr,
            user_group_id = :user_group_id,
            user_status = 'A'
            WHERE user_id = :user_id");
        $updateStmt->execute([
            ':user_gedcomnbr' => $gedcomNumber,
            ':user_group_id' => $groupId,
            ':user_id' => $userId,
        ]);

        return $this->sendApprovalEmail($user);
    }

    private function sendApprovalEmail(object $user): string
    {
        $email = trim((string) ($user->user_mail ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return __('User approved, but no valid email address was available for the confirmation email.');
        }

        $sender = trim((string) ($this->humo_option['email_sender'] ?? ''));
        if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            $sender = trim((string) ($this->humo_option['general_email'] ?? ''));
        }
        if (!filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            return __('User approved, but the confirmation email could not be sent because no sender address is configured.');
        }

        $humo_option = $this->humo_option;
        include_once __DIR__ . '/../../include/mail.php';

        $name = trim((string) ($user->user_name ?? ''));
        $subject = __('Your Shijrah registration has been approved');
        $message = __('Your registration for Shijrah (Family Tree) has been approved.') . "<br><br>\n";
        $message .= __('You can now access Shijrah using the following link:') . "<br>\n";
        $message .= '<a href="https://khandesh.co.in/familytree">https://khandesh.co.in/familytree</a><br><br>\n';
        $message .= __('Username') . ': ' . htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        try {
            $mail->setFrom($sender, $sender);
            $mail->addAddress($email, $name);
            $mail->Subject = $subject;
            $mail->msgHTML($message);
            if (!$mail->send()) {
                return __('User approved, but the confirmation email could not be sent.');
            }
        } catch (\Throwable $exception) {
            error_log('[HuMo pending approval] Confirmation email failed: ' . get_class($exception));
            return __('User approved, but the confirmation email could not be sent.');
        }

        return __('User approved and confirmation email sent.');
    }
}
