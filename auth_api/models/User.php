<?php
class User {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        try {
            error_log("Starting login process for username: $username");

            $query = "SELECT 
            e.Lname, 
            e.Fname, 
            e.memberid, 
            u.UPassword, 
            e.MobilePhone,
            e.EmailAddress,
            e.Address,
            e.City,
            e.State,
            n.nokfirstname AS nok_first_name,
            n.nokmiddlename AS nok_middle_name,
            n.noklastname AS nok_last_name,
            n.NOKPhone AS nok_tel
        FROM tbl_personalinfo e
        INNER JOIN tblusers u ON e.memberid = u.UserID
        LEFT JOIN tbl_nok n ON e.memberid = n.memberid
        WHERE e.memberid = :username OR e.MobilePhone = :mobile_number";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':mobile_number', $username);
            $stmt->execute();

            error_log("Query executed, rows found: " . $stmt->rowCount());

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("User found, checking password");
                error_log("Stored password hash: " . $row['UPassword']);
                error_log("Password being tested: " . $password);

                if (password_verify($password, $row['UPassword'])) {
                    error_log("Password verification successful");
                    return [
                        'success' => true,
                        'user' => [
                            'CoopID' => $row['memberid'],
                            'FirstName' => $row['Fname'],
                            'LastName' => $row['Lname'],
                            'EmailAddress' => $row['EmailAddress'],
                            'MobileNumber' => $row['MobilePhone'],
                            'StreetAddress' => $row['Address'],
                            'Town' => $row['City'],
                            'State' => $row['State'],
                            'nok_first_name' => $row['nok_first_name'],
                            'nok_middle_name' => $row['nok_middle_name'],
                            'nok_last_name' => $row['nok_last_name'],
                            'nok_tel' => $row['nok_tel'],
                        ]
                    ];
                } else {
                    error_log("Password verification failed");
                }
            } else {
                error_log("No user found with username: $username");
            }

            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        } catch (PDOException $e) {
            error_log("Database error in login: " . $e->getMessage());
            throw new Exception('Database error occurred');
        }
    }
}