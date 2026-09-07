<?php
/**
 * Columns of `employee` a staff member may request changes to.
 *
 * Both ends of the flow check against this list. submit_changes.php interpolates
 * the field name into a SELECT and handle_approval.php interpolates it into an
 * UPDATE, so an unchecked name is a SQL injection that can read any table and
 * write any column, including payroll and grade fields.
 *
 * Deliberately excluded: EMPDATE, DOPA, DOC, LEVE_APT, DEPTCD, PPNO. Those drive
 * salary, grade and retirement dates. They are HR-controlled, not self-service.
 */

final class ProfileFields
{
    private const EDITABLE = [
        'NAME',
        'EMAIL',
        'MOBILE_NO',
        'GENDER',
        'DOB',
        'LG_ORIGIN',
        'S_ORIGIN',
    ];

    public static function isEditable($field): bool
    {
        return is_string($field) && in_array($field, self::EDITABLE, true);
    }

    public static function assertEditable($field): void
    {
        if (!self::isEditable($field)) {
            throw new Exception('This field cannot be changed through self-service', 400);
        }
    }

    public static function all(): array
    {
        return self::EDITABLE;
    }
}
