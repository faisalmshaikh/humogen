<?php

namespace Genealogy\App\Model;

final class RegistrationFields
{
    private const FIELDS = [
        'register_father_name' => 'user_father_name',
        'register_mother_name' => 'user_mother_name',
        'register_birth_date' => 'user_birth_date',
        'register_reference_name' => 'user_reference_name',
        'register_address' => 'user_address',
        'register_marital_status' => 'user_marital_status',
        'register_paternal_grandparent_names' => 'user_paternal_grandparent_names',
        'register_maternal_grandparent_names' => 'user_maternal_grandparent_names',
        'register_phone' => 'user_phone',
    ];

    private const LABELS = [
        'register_father_name' => "Father's name",
        'register_mother_name' => "Mother's name",
        'register_birth_date' => 'Date of birth',
        'register_reference_name' => 'Relative name for reference',
        'register_address' => 'Address',
        'register_marital_status' => 'Marital status',
        'register_paternal_grandparent_names' => "Paternal grandparent's names",
        'register_maternal_grandparent_names' => "Maternal grandparent's names",
        'register_phone' => 'Phone number',
    ];

    public static function names(): array
    {
        return array_keys(self::FIELDS);
    }

    public static function column(string $field): string
    {
        return self::FIELDS[$field];
    }

    public static function labels(): array
    {
        return self::LABELS;
    }
}
