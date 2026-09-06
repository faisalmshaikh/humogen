<?php

require_once __DIR__ . '/../app/Model/RegistrationValidator.php';

use Genealogy\App\Model\RegistrationValidator;

function expectSame($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true));
    }
}

expectSame('', RegistrationValidator::usernameError('user-_.+123'), 'Allowed username characters should pass.');
expectSame('ERROR: Username may contain only letters, numbers, and - _ . + characters.', RegistrationValidator::usernameError('user name'), 'Spaces should be rejected.');
expectSame('ERROR: Username may contain only letters, numbers, and - _ . + characters.', RegistrationValidator::usernameError('user@example'), 'Unlisted special characters should be rejected.');
expectSame('ERROR: Username may contain only letters, numbers, and - _ . + characters.', RegistrationValidator::usernameError(''), 'An empty username should be rejected.');
expectSame('ERROR: Username may contain only letters, numbers, and - _ . + characters.', RegistrationValidator::usernameError('अमित-123'), 'Characters outside the allowed username alphabet should be rejected.');
expectSame('', RegistrationValidator::birthDateError('1980-02-29'), 'A valid date should pass.');
expectSame('ERROR: Date of birth must be a valid date.', RegistrationValidator::birthDateError('1980-02-30'), 'An invalid date should be rejected.');
expectSame('', RegistrationValidator::maritalStatusError('Married'), 'A valid marital status should pass.');
expectSame('ERROR: Please select a valid marital status.', RegistrationValidator::maritalStatusError('Unknown'), 'An invalid marital status should be rejected.');

echo "Registration validator tests passed.\n";
