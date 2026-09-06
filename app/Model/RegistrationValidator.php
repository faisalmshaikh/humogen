<?php

namespace Genealogy\App\Model;

final class RegistrationValidator
{
    private const USERNAME_ERROR = 'ERROR: Username may contain only letters, numbers, and - _ . + characters.';

    private function __construct()
    {
    }

    public static function usernameError(string $username): string
    {
        if ($username === '' || mb_strlen($username) > 25 || preg_match('/^[A-Za-z0-9._+\-]+$/', $username) !== 1) {
            return self::USERNAME_ERROR;
        }

        return '';
    }

    public static function birthDateError(string $birthDate): string
    {
        if ($birthDate === '') {
            return '';
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return 'ERROR: Date of birth must be a valid date.';
        }

        return '';
    }

    public static function maritalStatusError(string $maritalStatus): string
    {
        if ($maritalStatus !== '' && !in_array($maritalStatus, ['Single', 'Married', 'Divorced', 'Widowed', 'Separated'], true)) {
            return 'ERROR: Please select a valid marital status.';
        }

        return '';
    }
}
