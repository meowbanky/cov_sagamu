<?php
// utils/MemberBalance.php
//
// Shared logic for "does this member owe the society money?" and for the
// anonymisation carried out when a deletion request is approved.
//
// Both the mobile request endpoint and the cov_admin approval page use this,
// so the figure the member sees can never disagree with the figure the officer
// sees, and the anonymisation can never drift between the two paths.

class MemberBalance
{
    /**
     * Outstanding loan for a member.
     *
     * Deliberately the SAME expression as api/auth/get_wallet_data.php:
     *   SUM(loanAmount) - SUM(loanRepayment)
     * If that ever changes, change it here too or the home screen and the
     * deletion check will contradict each other.
     */
    public static function outstandingLoan(PDO $db, $memberId)
    {
        $stmt = $db->prepare(
            'SELECT COALESCE(SUM(t.loanAmount), 0) - COALESCE(SUM(t.loanRepayment), 0)
                      AS unpaid_loan
               FROM tlb_mastertransaction t
              WHERE t.memberid = :member_id'
        );
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $outstanding = $row ? (float) $row['unpaid_loan'] : 0.0;

        // A credit balance is not a debt.
        return $outstanding > 0 ? round($outstanding, 2) : 0.0;
    }

    /**
     * Member-facing wording for a deletion request outcome.
     */
    public static function deletionMessage($status, $outstanding)
    {
        if ($status === 'pending_settlement') {
            return sprintf(
                'Your deletion request has been sent to the cooperative office. '
                . 'Our records show an outstanding balance of %s, which must be '
                . 'settled before your account can be closed. The office will '
                . 'contact you.',
                self::formatNaira($outstanding)
            );
        }

        if ($status === 'pending') {
            return 'Your deletion request has been sent to the cooperative office '
                . 'for approval. Your account will be closed once an officer has '
                . 'confirmed you have no outstanding obligations.';
        }

        if ($status === 'approved') {
            return 'Your account has been closed.';
        }

        return 'Your deletion request was not approved. Please contact the '
            . 'cooperative office.';
    }

    public static function formatNaira($amount)
    {
        return '₦' . number_format((float) $amount, 2);
    }

    /**
     * Irreversibly anonymises a member and revokes their access.
     *
     * Financial records in tlb_mastertransaction are intentionally untouched —
     * the society is required to retain them for audit, and they continue to
     * reference the member id.
     *
     * Caller is responsible for the surrounding transaction.
     */
    public static function anonymise(PDO $db, $memberId, $reviewedBy)
    {
        $stmt = $db->prepare(
            'UPDATE tbl_personalinfo
                SET Fname        = :redacted_first,
                    Lname        = :redacted_last,
                    EmailAddress = NULL,
                    MobilePhone  = NULL,
                    Address      = NULL,
                    City         = NULL,
                    State        = NULL,
                    Picture      = NULL,
                    fcm_token    = NULL,
                    deleted_at   = NOW(),
                    deletion_requested_by = :reviewed_by
              WHERE memberid = :member_id'
        );
        $redactedFirst = 'Deleted';
        $redactedLast = 'Member';
        $stmt->bindParam(':redacted_first', $redactedFirst);
        $stmt->bindParam(':redacted_last', $redactedLast);
        $stmt->bindParam(':reviewed_by', $reviewedBy);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();

        // Next-of-kin details are pure PII with no audit value.
        $stmt = $db->prepare('DELETE FROM tbl_nok WHERE memberid = :member_id');
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();

        // Scramble the credential so the login cannot be replayed.
        $stmt = $db->prepare('UPDATE tblusers SET UPassword = :dead WHERE UserID = :member_id');
        $dead = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $stmt->bindParam(':dead', $dead);
        $stmt->bindParam(':member_id', $memberId);
        $stmt->execute();
    }
}
