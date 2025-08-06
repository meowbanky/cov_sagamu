<?php
require_once 'config/EnvConfig.php';

// Initialize database connection only when needed
function getDatabaseConnection() {
    static $cov = null;
    if ($cov === null) {
        // Suppress errors and handle connection manually
        $hostname_cov = "localhost";
        $database_cov = "emmaggic_cofv";
        $username_cov = "emmaggic_root";
        $password_cov = "Oluwaseyi";
        
        $cov = @mysqli_connect($hostname_cov, $username_cov, $password_cov);
        if (!$cov) {
            throw new Exception('Database connection failed: ' . mysqli_connect_error());
        }
        
        if (!@mysqli_select_db($cov, $database_cov)) {
            throw new Exception('Database selection failed: ' . mysqli_error($cov));
        }
    }
    return $cov;
}

// Check if user is logged in
session_start();
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        break;
    }
}

if (!$is_logged_in) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in. Session expired or not authenticated.']);
    exit();
}

try {
    // Get the filename from POST data
    $filename = $_POST['filename'] ?? '';
    
    if (empty($filename)) {
        throw new Exception('Filename is required');
    }
    
    // Delete the file entry from the database by filename
    $cov = getDatabaseConnection();
    $delete_query = "DELETE FROM bank_statement_files WHERE filename = ?";
    $delete_stmt = mysqli_prepare($cov, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 's', $filename);
    mysqli_stmt_execute($delete_stmt);
    
    $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
    
    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'File entry cleared successfully. You can now upload the file again.',
            'deleted_count' => $affected_rows
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No file entry found with that filename'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Clear duplicate error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error clearing file: ' . $e->getMessage()
    ]);
}
?> 