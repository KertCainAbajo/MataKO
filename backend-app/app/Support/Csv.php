<?php

namespace App\Support;

/**
 * Makes values safe to open in Excel or Google Sheets. Text that starts with =, +, -, @, a tab or a
 * carriage return would otherwise run as a formula (for example a user who names themselves
 * =HYPERLINK("http://…")), so it gets a leading apostrophe and is shown as plain text.
 */
class Csv
{
    /**
     * @param  array<int, mixed>  $row
     * @return array<int, mixed>
     */
    public static function row(array $row): array
    {
        return array_map(
            fn (mixed $value): mixed => is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value,
            $row,
        );
    }
}
