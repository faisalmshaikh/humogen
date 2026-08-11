<?php

namespace Genealogy\Include;

class PasswordPolicy
{
    /**
     * Return an error message when the password violates the policy.
     * An empty string means the password is valid.
     */
    public static function validate(string $password, ?string $currentPasswordHash = null, ?string $legacyPasswordHash = null): string
    {
        if (strlen($password) < 8) {
            return 'ERROR: Password must be at least 8 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return 'ERROR: Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            return 'ERROR: Password must contain at least one lowercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            return 'ERROR: Password must contain at least one digit';
        }
        if (!preg_match('/[^a-zA-Z0-9\s]/', $password)) {
            return 'ERROR: Password must contain at least one special character';
        }

        if ($currentPasswordHash !== null && $currentPasswordHash !== '' && password_verify($password, $currentPasswordHash)) {
            return 'ERROR: New password cannot be the same as the old password';
        }

        // Support installations that still have only the legacy MD5 value.
        if ($legacyPasswordHash !== null && $legacyPasswordHash !== '' && hash_equals(strtolower($legacyPasswordHash), md5($password))) {
            return 'ERROR: New password cannot be the same as the old password';
        }

        return '';
    }
}
