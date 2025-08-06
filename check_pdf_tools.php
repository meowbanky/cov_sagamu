<?php
echo "<h1>PDF Tools Availability Check</h1>";

echo "<h2>Checking for PDF extraction tools:</h2>";

// Check for pdftotext
echo "<h3>1. pdftotext (poppler-utils)</h3>";
if (function_exists('shell_exec')) {
    $output = shell_exec("which pdftotext 2>&1");
    if (!empty($output) && strpos($output, 'pdftotext') !== false) {
        echo "<p style='color: green;'>✅ pdftotext is available at: " . trim($output) . "</p>";
        
        // Test version
        $version = shell_exec("pdftotext -v 2>&1");
        echo "<p>Version info: " . htmlspecialchars($version) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ pdftotext not found</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ shell_exec function is disabled</p>";
}

// Check for Python
echo "<h3>2. Python</h3>";
if (function_exists('shell_exec')) {
    $python_output = shell_exec("which python3 2>&1");
    if (!empty($python_output) && strpos($python_output, 'python3') !== false) {
        echo "<p style='color: green;'>✅ Python3 is available at: " . trim($python_output) . "</p>";
        
        // Check Python version
        $python_version = shell_exec("python3 --version 2>&1");
        echo "<p>Python version: " . htmlspecialchars($python_version) . "</p>";
        
        // Check for pdfplumber
        $pdfplumber_check = shell_exec("python3 -c 'import pdfplumber; print(\"pdfplumber available\")' 2>&1");
        if (strpos($pdfplumber_check, 'pdfplumber available') !== false) {
            echo "<p style='color: green;'>✅ pdfplumber is installed</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ pdfplumber not installed</p>";
        }
        
        // Check for PyPDF2
        $pypdf2_check = shell_exec("python3 -c 'import PyPDF2; print(\"PyPDF2 available\")' 2>&1");
        if (strpos($pypdf2_check, 'PyPDF2 available') !== false) {
            echo "<p style='color: green;'>✅ PyPDF2 is installed</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ PyPDF2 not installed</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Python3 not found</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ shell_exec function is disabled</p>";
}

// Check for other PDF tools
echo "<h3>3. Other PDF tools</h3>";
$tools = ['pdfinfo', 'pdftk', 'gs', 'ghostscript'];
foreach ($tools as $tool) {
    if (function_exists('shell_exec')) {
        $output = shell_exec("which $tool 2>&1");
        if (!empty($output) && strpos($output, $tool) !== false) {
            echo "<p style='color: green;'>✅ $tool is available at: " . trim($output) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ $tool not found</p>";
        }
    }
}

// Check PHP extensions
echo "<h3>4. PHP Extensions</h3>";
$extensions = ['imagick', 'gd', 'curl', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ $ext extension is loaded</p>";
    } else {
        echo "<p style='color: red;'>❌ $ext extension not loaded</p>";
    }
}

echo "<h2>Installation Options:</h2>";
echo "<h3>For Shared Hosting (No Root Access):</h3>";
echo "<ol>";
echo "<li><strong>Contact your hosting provider</strong> - Ask them to install pdftotext (poppler-utils)</li>";
echo "<li><strong>Use Python libraries</strong> - Install pdfplumber or PyPDF2 via pip</li>";
echo "<li><strong>Use online services</strong> - Convert PDFs to text via API</li>";
echo "<li><strong>Use JavaScript libraries</strong> - Client-side PDF processing</li>";
echo "</ol>";

echo "<h3>Python Installation (if available):</h3>";
echo "<pre>";
echo "# Install pdfplumber\n";
echo "pip3 install pdfplumber\n\n";
echo "# Or install PyPDF2\n";
echo "pip3 install PyPDF2\n";
echo "</pre>";

echo "<h3>Alternative Solutions:</h3>";
echo "<ul>";
echo "<li><strong>Online PDF to Text API</strong> - Use services like PDFTron, Adobe PDF Services</li>";
echo "<li><strong>Client-side processing</strong> - Use JavaScript libraries like pdf.js</li>";
echo "<li><strong>Manual conversion</strong> - Convert PDFs to text files before upload</li>";
echo "</ul>";

echo "<h2>Next Steps:</h2>";
echo "<p>Based on what's available, we can:</p>";
echo "<ul>";
echo "<li>Use existing tools if available</li>";
echo "<li>Install Python libraries if Python is available</li>";
echo "<li>Implement alternative solutions</li>";
echo "</ul>";
?>