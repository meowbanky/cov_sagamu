<?php
// utils/MemberLookup.php
//
// Resolves a member id to their email address SERVER-SIDE.
//
// The password-reset flow used to work by handing the client a member's real
// email address (from the unauthenticated search endpoint) and then passing it
// back on every subsequent call. That made every member's address harvestable
// by anyone who could guess a name. The client now only ever handles a member
// id, and the address is resolved here.

class MemberLookup
{
    /**
     * Email address for an active member, or null if the member does not exist,
     * has been closed, or has no address on file.
     */
    public static function emailForMember(PDO $db, $memberId)
    {
        $stmt = $db->prepare(
            'SELECT EmailAddress
               FROM tbl_personalinfo
              WHERE memberid = :member_id
                AND deleted_at IS NULL'
        );
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['EmailAddress'])) {
            return null;
        }

        return $row['EmailAddress'];
    }

    /**
     * Masks an address for display: enough for the owner to recognise it,
     * useless to anyone else.
     *
     *   adeola.bankole@gmail.com  ->  ad••••••••••e@gm•••.com
     */
    public static function maskEmail($email)
    {
        if (empty($email) || strpos($email, '@') === false) {
            return '';
        }

        list($local, $domain) = explode('@', $email, 2);

        $maskedLocal = self::maskPart($local, 2, 1);

        $dot = strrpos($domain, '.');
        if ($dot === false) {
            return $maskedLocal . '@' . self::maskPart($domain, 2, 0);
        }

        $domainName = substr($domain, 0, $dot);
        $tld = substr($domain, $dot); // includes the dot

        return $maskedLocal . '@' . self::maskPart($domainName, 2, 0) . $tld;
    }

    private static function maskPart($value, $keepStart, $keepEnd)
    {
        $length = strlen($value);
        if ($length <= $keepStart + $keepEnd) {
            return str_repeat('•', max($length, 1));
        }

        $hidden = $length - $keepStart - $keepEnd;

        return substr($value, 0, $keepStart)
            . str_repeat('•', $hidden)
            . ($keepEnd > 0 ? substr($value, -$keepEnd) : '');
    }
}
