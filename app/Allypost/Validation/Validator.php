<?php

namespace Allypost\Validation;

use Violin\Violin;

class Validator extends Violin {

    public function __construct() {
        $this->addFieldMessages(
            [
                'email' => [
                    'uniqueEmail' => 'That email is already in use.',
                ],
                'username' => [
                    'uniqueUsername' => 'That username is already in use.',
                ],
            ]
        );

        $this->addRuleMessages(
            [
                'matchesCurrentPassword' => 'That does not match your current password.',
                'notmatches' => 'The {field} field must NOT match the {$0} field.',
                'uniqueField' => 'The {field} is already taken.',
                'uniqueUserEmail' => 'That email is already taken.',
                'filled' => 'The {field} must NOT be empty.',
                'isValidRole' => '{field} is not a valid role.',
                /**/
                'required' => 'The {field} field must be filled in.',
                'matches' => 'The {field} field must match the {$0} field.',
                'alnum' => 'The {field} field must be alphanumeric.',
                'alnumDash' => 'The {field} field must be alphanumeric with dashes and underscores permitted.',
                'alpha' => 'The {field} field must be alphabetic.',
                'alphaDash' => 'The {field} field must be alphabetic with dashes and underscores permitted.',
                'array' => 'The {field} field must be an array.',
                'between' => 'The {field} field must be in between {$0} and {$1}',
                'bool' => 'The {field} field must be a boolean (true or false).',
                'email' => 'The {field} field must be in a valid email format.',
                'int' => 'The {field} field must be a whole number',
                'number' => 'The {field} field must be in a valid number format (e.g. 0777, 0b10100111001, +0123.45e6, 1234, 0xf4c3b00c)',
                'ip' => 'The {field} field must be in a valid IP format.',
                'min' => 'The length of the {field} field must be greater than or equal to {$0}',
                'max' => 'The length of the {field} field must be less than or equal to {$0}',
                'url' => 'The {field} field must be in a valid URL format.',
                'date' => 'The {field} field must be in a valid date.',
                'checked' => 'The {field} field must be checked.',
                'regex' => 'The {field} field must match the following regular expression: {$0}',
            ]
        );
    }

    public function validate_name($value, $input, $args) {
        return (bool) true; #preg_match("/^[a-zA-Z'\-\ ]+$/", $value);
    }

    /**
     * Validate that the supplied data does not match another field
     */
    public function validate_notmatches($value, $input, $args) {
        foreach ($args as $arg) {
            if ($value === $input[ $arg ])
                return false;
        }

        return true;
    }

    /**
     * Alias for filled
     */
    public function validate_notempty($value, $input, $args) {
        return $this->validate_filled($value, $input, $args);
    }

    /**
     * Validate that the field is not empty
     */
    public function validate_filled($value, $input, $args) {
        return !empty($value);
    }

}
