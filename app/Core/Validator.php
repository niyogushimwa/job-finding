<?php
declare(strict_types=1);

namespace App\Core;

/** Server-side validation with a small, explicit rule set. */
final class Validator
{
    public static function make(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            foreach (explode('|', $ruleString) as $rule) {
                [$name, $argument] = array_pad(explode(':', $rule, 2), 2, null);
                $label = ucwords(str_replace('_', ' ', $field));
                $message = match ($name) {
                    'required' => (trim((string) $value) === '' && !is_array($value)) ? "$label is required." : null,
                    'email' => ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? "Enter a valid email address." : null,
                    'min' => ($value !== null && mb_strlen((string) $value) < (int) $argument) ? "$label must be at least $argument characters." : null,
                    'max' => ($value !== null && mb_strlen((string) $value) > (int) $argument) ? "$label must not exceed $argument characters." : null,
                    'numeric' => ($value !== '' && !is_numeric($value)) ? "$label must be a number." : null,
                    'integer' => ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) ? "$label must be a whole number." : null,
                    'url' => ($value && !filter_var($value, FILTER_VALIDATE_URL)) ? "Enter a valid URL starting with http:// or https://." : null,
                    'date' => ($value && !strtotime((string) $value)) ? "Enter a valid date." : null,
                    'in' => ($value && !in_array($value, explode(',', (string) $argument), true)) ? "Choose a valid $label." : null,
                    'alpha_num' => ($value && !preg_match('/^[\pL\pN\s_.-]+$/u', (string) $value)) ? "$label contains invalid characters." : null,
                    'confirmed' => ($value !== ($data[$field . '_confirmation'] ?? null)) ? "$label confirmation does not match." : null,
                    'regex' => ($value && !preg_match((string) $argument, (string) $value)) ? "$label has an invalid format." : null,
                    'unique' => self::unique($value, (string) $argument, $data, $field),
                    default => null,
                };
                if ($message && !isset($errors[$field])) {
                    $errors[$field] = $message;
                }
            }
        }
        return $errors;
    }

    private static function unique(mixed $value, string $argument, array $data, string $field): ?string
    {
        [$table, $column] = array_pad(explode(',', $argument, 2), 2, $field);
        if (!$value) {
            return null;
        }
        $sql = "SELECT COUNT(*) FROM `$table` WHERE `$column` = :v";
        $params = ['v' => $value];
        if (isset($data['id'])) {
            $sql .= ' AND id <> :id';
            $params['id'] = $data['id'];
        }
        return Database::scalar($sql, $params) ? 'This value is already in use.' : null;
    }

    public static function valid(array $errors): bool { return $errors === []; }
}
