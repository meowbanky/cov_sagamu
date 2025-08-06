<?php
// Start session with proper configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

echo "<h1>Ghostscript PDF Text Extraction Test</h1>";

// Check authentication
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        break;
    }
}

if (!$is_logged_in) {
    echo "<p style='color: red;'>Please log in first!</p>";
    exit();
}

echo "<h2>Ghostscript Availability:</h2>";
if (function_exists('shell_exec')) {
    $gs_path = shell_exec("which gs 2>&1");
    if (!empty($gs_path) && strpos($gs_path, 'gs') !== false) {
        echo "<p style='color: green;'>✅ Ghostscript available at: " . trim($gs_path) . "</p>";
        
        // Test Ghostscript version
        $gs_version = shell_exec("gs --version 2>&1");
        echo "<p>Ghostscript version: " . htmlspecialchars($gs_version) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Ghostscript not found</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ shell_exec function is disabled</p>";
}

// Check if files were uploaded
echo "<h2>PDF Upload Test:</h2>";
if (isset($_FILES['pdf_file'])) {
    echo "<p>PDF uploaded: ✅ Yes</p>";
    echo "<p>File: " . $_FILES['pdf_file']['name'] . "</p>";
    echo "<p>Size: " . $_FILES['pdf_file']['size'] . " bytes</p>";
    
    $tmp_name = $_FILES['pdf_file']['tmp_name'];
    $file_name = $_FILES['pdf_file']['name'];
    
    // Save file temporarily
    $upload_dir = 'uploads/bank_statements/';
    $test_path = $upload_dir . 'test_gs_' . time() . '_' . $file_name;
    
    if (move_uploaded_file($tmp_name, $test_path)) {
        echo "<p>File saved: ✅ Yes</p>";
        echo "<p>Saved to: $test_path</p>";
        
        // Test Ghostscript text extraction
        echo "<h3>Ghostscript Text Extraction Test:</h3>";
        
        // Method 1: txtwrite device
        echo "<h4>Method 1: txtwrite device</h4>";
        $output1 = shell_exec("gs -sDEVICE=txtwrite -dNOPAUSE -dBATCH -dSAFER -sOutputFile=- \"$test_path\" 2>&1");
        if (!empty($output1)) {
            echo "<p style='color: green;'>✅ Text extracted using txtwrite</p>";
            echo "<p>Text length: " . strlen($output1) . " characters</p>";
            echo "<h5>Extracted Text (first 1000 characters):</h5>";
            echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;'>";
            echo "<pre>" . htmlspecialchars(substr($output1, 0, 1000)) . "</pre>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ No text extracted using txtwrite</p>";
        }
        
        // Method 2: ps2ascii device
        echo "<h4>Method 2: ps2ascii device</h4>";
        $output2 = shell_exec("gs -sDEVICE=ps2ascii -dNOPAUSE -dBATCH -dSAFER -sOutputFile=- \"$test_path\" 2>&1");
        if (!empty($output2)) {
            echo "<p style='color: green;'>✅ Text extracted using ps2ascii</p>";
            echo "<p>Text length: " . strlen($output2) . " characters</p>";
            echo "<h5>Extracted Text (first 1000 characters):</h5>";
            echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;'>";
            echo "<pre>" . htmlspecialchars(substr($output2, 0, 1000)) . "</pre>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ No text extracted using ps2ascii</p>";
        }
        
        // Method 3: ps2write to PostScript then extract
        echo "<h4>Method 3: ps2write to PostScript</h4>";
        $ps_file = $test_path . '.ps';
        $ps_output = shell_exec("gs -sDEVICE=ps2write -dNOPAUSE -dBATCH -dSAFER -sOutputFile=\"$ps_file\" \"$test_path\" 2>&1");
        
        if (file_exists($ps_file)) {
            echo "<p style='color: green;'>✅ PostScript file created</p>";
            $ps_content = file_get_contents($ps_file);
            echo "<p>PostScript file size: " . strlen($ps_content) . " bytes</p>";
            
            // Extract text from PostScript
            preg_match_all('/\(([^)]+)\)/', $ps_content, $matches);
            $extracted_text = '';
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    $extracted_text .= $match . ' ';
                }
            }
            
            if (!empty($extracted_text)) {
                echo "<p style='color: green;'>✅ Text extracted from PostScript</p>";
                echo "<p>Text length: " . strlen($extracted_text) . " characters</p>";
                echo "<h5>Extracted Text (first 1000 characters):</h5>";
                echo "<div style='background: #f5f5f5; padding: 15px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px;'>";
                echo "<pre>" . htmlspecialchars(substr($extracted_text, 0, 1000)) . "</pre>";
                echo "</div>";
            } else {
                echo "<p style='color: red;'>❌ No text extracted from PostScript</p>";
            }
            
            unlink($ps_file); // Clean up
        } else {
            echo "<p style='color: red;'>❌ Failed to create PostScript file</p>";
        }
        
        // Clean up
        unlink($test_path);
        echo "<p>Test file cleaned up</p>";
        
    } else {
        echo "<p style='color: red;'>Failed to save file</p>";
    }
} else {
    echo "<p>No PDF uploaded</p>";
    echo "<p>Please upload a PDF to test Ghostscript text extraction.</p>";
}

// Add upload form
echo "<h2>Upload Test Form</h2>";
echo "<form action='' method='post' enctype='multipart/form-data'>";
echo "<div style='margin-bottom: 15px;'>";
echo "<label for='pdf_file'><strong>Select PDF File:</strong></label><br>";
echo "<input type='file' name='pdf_file' id='pdf_file' accept='.pdf' required style='width: 300px;'>";
echo "</div>";
echo "<div>";
echo "<button type='submit' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test PDF Extraction</button>";
echo "</div>";
echo "</form>";

echo "<h2>Test Links:</h2>";
echo "<ul>";
echo "<li><a href='ai_bank_statement_upload.php'>Go to AI Upload</a></li>";
echo "<li><a href='debug_ai_analysis.php'>Debug AI Analysis</a></li>";
echo "<li><a href='dashboard.php'>Go to Dashboard</a></li>";
echo "</ul>";
?>