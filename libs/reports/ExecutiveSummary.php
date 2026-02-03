<?php
/**
 * ExecutiveSummary - Generate Comparative Executive Report
 * 
 * Tracks Key Performance Indicators:
 * - Membership Growth
 * - Shares & Savings Growth
 * - Loan Portfolio Analysis
 * 
 * @version 1.0
 * @author Cooperative Management System
 */

class ExecutiveSummary {
    private $db;
    private $database_name;
    
    public function __construct($database_connection, $database_name = null) {
        $this->db = $database_connection;
        $this->database_name = $database_name;
        
        if ($database_name) {
            mysqli_select_db($this->db, $database_name);
        }
    }
    
    /**
     * Generate Executive Summary
     * 
     * @param int $periodid Period ID
     * @param array $comparative_periods Optional array of period IDs for comparison
     * @param string $scope Scope of report ('period' or 'year'). Default 'period' (legacy).
     * @return array Statement data
     */
    public function generateStatement($periodid, $comparative_periods = [], $scope = 'period') {
        try {
            // Usually we show Current vs Previous. The user passes them in order.
            // Let's reset to passed order for display.
            $periods = array_merge([$periodid], $comparative_periods);
            
            $statement = [];
            
            foreach ($periods as $pid) {
                // Ensure scope is passed down
                $statement[$pid] = $this->generateForPeriod($pid, $scope);
            }
            
            return [
                'success' => true,
                'statement' => $statement,
                'periods' => $periods
            ];
            
        } catch (Exception $e) {
            error_log("ExecutiveSummary::generateStatement - Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate stats for a single period
     */
    private function generateForPeriod($periodid, $scope) {
        // 1. Get Period Details (Year, Month) to establish a "cutoff"
        $pQuery = "SELECT PhysicalYear, PhysicalMonth, DateInserted FROM tbpayrollperiods WHERE Periodid = ?";
        $stmt = mysqli_prepare($this->db, $pQuery);
        mysqli_stmt_bind_param($stmt, "i", $periodid);
        mysqli_stmt_execute($stmt);
        $pRes = mysqli_stmt_get_result($stmt);
        $pRow = mysqli_fetch_assoc($pRes);
        mysqli_stmt_close($stmt);
        
        if (!$pRow) return [];
        
        $year = intval($pRow['PhysicalYear']);
        $month = $pRow['PhysicalMonth']; // Could be string like 'January' or '01'
        
        // Memberships: Always cumulative (As At)
        $monthNum = date('m', strtotime("$month 1 2000")); // Parse month name
        $periodEndDate = date('Y-m-t', strtotime("$year-$monthNum-01"));
        
        $membership_count = $this->getMembershipCount($periodEndDate);
        $new_members = $this->getNewMembersCount($scope, $year, $monthNum);
        
        // FINANCIAL STATS (Cumulative to Period - Balance Sheet items)
        $financials = $this->getCumulativeFinancials($periodid);
        
        // PERIOD FLOWS (Activity during Period/Year)
        $flows = $this->getPeriodFinancials($periodid, $scope, $year);
        
        return [
            'period_details' => $pRow,
            'membership' => [
                'total_members' => $membership_count,
                'new_members' => $new_members
            ],
            'financials' => array_merge($financials, $flows)
        ];
    }
    
    private function getMembershipCount($date) {
        // Count members registered on or before this date
        $sql = "SELECT COUNT(*) as c FROM tbl_personalinfo WHERE DateOfReg <= ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "s", $date);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['c'];
    }

    private function getNewMembersCount($scope, $year, $monthNum) {
        $sql = "";
        $params = [];
        $types = "";

        if ($scope == 'year') {
            $sql = "SELECT COUNT(*) as c FROM tbl_personalinfo WHERE YEAR(DateOfReg) = ?";
            $params[] = $year;
            $types = "s";
        } else {
            $sql = "SELECT COUNT(*) as c FROM tbl_personalinfo WHERE YEAR(DateOfReg) = ? AND MONTH(DateOfReg) = ?";
            $params[] = $year;
            $params[] = $monthNum;
            $types = "ss";
        }

        $stmt = mysqli_prepare($this->db, $sql);
        if ($types) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row['c'];
    }
    
    private function getCumulativeFinancials($maxPeriodId) {
        $sql = "SELECT 
                    SUM(COALESCE(shares, 0)) as total_shares,
                    SUM(COALESCE(savings, 0)) as total_savings,
                    SUM(COALESCE(loanAmount, 0)) as total_loan_issued,
                    SUM(COALESCE(loanRepayment, 0)) as total_loan_repaid,
                    SUM(COALESCE(interestPaid, 0)) as total_interest_paid
                FROM tlb_mastertransaction 
                WHERE periodid <= ?";
                
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $maxPeriodId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        $loan_balance = $row['total_loan_issued'] - $row['total_loan_repaid'];
        
        return [
            'total_shares' => $row['total_shares'] ?? 0,
            'total_savings' => $row['total_savings'] ?? 0,
            'loan_portfolio' => $loan_balance,
            'cumulative_loan_issued' => $row['total_loan_issued'] ?? 0,
            'cumulative_loan_repaid' => $row['total_loan_repaid'] ?? 0
        ];
    }

    private function getPeriodFinancials($periodid, $scope, $year) {
        /*
          Calculate flows (Loans Issued, Repaid) specifically for the selected window.
          
          If Scope = 'period': WHERE periodid = ?
          If Scope = 'year': WHERE periodid IN (SELECT Periodid FROM tbpayrollperiods WHERE PhysicalYear = ?)
                             BUT since we don't want to use subquery if possible, we can just join.
          Actually, since `tlb_mastertransaction` has `periodid`, we can do a JOIN on `tbpayrollperiods` to check `PhysicalYear`.
        */

        $sql = "";
        $params = [];
        $types = "";

        if ($scope == 'year') {
            // Get flows for the entire Physical Year
            $sql = "SELECT 
                        SUM(COALESCE(t.shares, 0)) as period_shares,
                        SUM(COALESCE(t.savings, 0)) as period_savings,
                        SUM(COALESCE(t.loanAmount, 0)) as period_loan_issued,
                        SUM(COALESCE(t.loanRepayment, 0)) as period_loan_repaid
                    FROM tlb_mastertransaction t
                    INNER JOIN tbpayrollperiods p ON t.periodid = p.Periodid
                    WHERE p.PhysicalYear = ?";
            $params[] = $year;
            $types = "s"; // Year is often string or int in DB, let's treat as string to be safe based on previous code usage
        } else {
            // Get flows for just this period (Month)
            $sql = "SELECT 
                        SUM(COALESCE(shares, 0)) as period_shares,
                        SUM(COALESCE(savings, 0)) as period_savings,
                        SUM(COALESCE(loanAmount, 0)) as period_loan_issued,
                        SUM(COALESCE(loanRepayment, 0)) as period_loan_repaid
                    FROM tlb_mastertransaction 
                    WHERE periodid = ?";
            $params[] = $periodid;
            $types = "i";
        }

        $stmt = mysqli_prepare($this->db, $sql);
        if ($types && count($params) > 0) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return [
            'period_shares' => $row['period_shares'] ?? 0,
            'period_savings' => $row['period_savings'] ?? 0,
            'period_loan_issued' => $row['period_loan_issued'] ?? 0,
            'period_loan_repaid' => $row['period_loan_repaid'] ?? 0
        ];
    }
}
?>
