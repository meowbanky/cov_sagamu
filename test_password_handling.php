<?php
session_start();
require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

// Simulate a logged-in user
$_SESSION['UserID'] = 1;
$_SESSION['SESS_FIRST_NAME'] = 'Test User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password-Protected PDF Handling Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-lock me-2"></i>Password-Protected PDF Handling Test</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>How Password Protection Works</h5>
                    <p>This system can handle password-protected PDF bank statements. Here's how it works:</p>
                    <ul>
                        <li><strong>Automatic Detection:</strong> When you upload a PDF, the system automatically detects if it's password-protected</li>
                        <li><strong>Password Prompt:</strong> If a protected PDF is detected, a password input field appears</li>
                        <li><strong>Secure Processing:</strong> The password is used only for PDF text extraction and is not stored</li>
                        <li><strong>Multi-Page Support:</strong> Password-protected PDFs are processed page by page like regular PDFs</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <label for="pdfFile" class="form-label">Select PDF File (Protected or Unprotected):</label>
                    <input type="file" class="form-control" id="pdfFile" accept=".pdf">
                </div>
                
                <button class="btn btn-primary" onclick="testPasswordHandling()">
                    <i class="fas fa-play me-2"></i>Test Password Handling
                </button>
                
                <div id="results" class="mt-4"></div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4><i class="fas fa-cogs me-2"></i>Technical Details</h4>
            </div>
            <div class="card-body">
                <h6>Features Implemented:</h6>
                <ul>
                    <li><strong>Client-side Detection:</strong> Uses PDF.js to detect password protection before upload</li>
                    <li><strong>Password Input UI:</strong> Dynamic password field appears when protected PDFs are detected</li>
                    <li><strong>Validation:</strong> Ensures password is provided before processing</li>
                    <li><strong>Error Handling:</strong> Graceful handling of incorrect passwords</li>
                    <li><strong>Multi-page Support:</strong> Password-protected PDFs are processed page by page</li>
                    <li><strong>Server Integration:</strong> Password is passed to server for fallback processing</li>
                </ul>

                <h6>Security Features:</h6>
                <ul>
                    <li><strong>No Storage:</strong> Passwords are never stored in the database</li>
                    <li><strong>Temporary Use:</strong> Password is only used during PDF text extraction</li>
                    <li><strong>Secure Transmission:</strong> Password is sent securely via HTTPS</li>
                    <li><strong>Memory Cleanup:</strong> Password is cleared from memory after processing</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- PDF.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="js/pdf-text-extractor.js"></script>
    
    <script>
        async function testPasswordHandling() {
            const fileInput = document.getElementById('pdfFile');
            const resultsDiv = document.getElementById('results');
            
            if (!fileInput.files[0]) {
                alert('Please select a PDF file first.');
                return;
            }
            
            const file = fileInput.files[0];
            resultsDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Testing password handling...</div>';
            
            try {
                console.log('Testing password handling for:', file.name);
                
                // Test password detection
                const passwordCheck = await checkPDFPassword(file);
                console.log('Password check result:', passwordCheck);
                
                let resultsHTML = '<div class="alert alert-success"><h5>Password Handling Test Results</h5>';
                resultsHTML += `<p><strong>File:</strong> ${file.name}</p>`;
                resultsHTML += `<p><strong>File size:</strong> ${(file.size / 1024).toFixed(2)} KB</p>`;
                resultsHTML += `<p><strong>Password Protected:</strong> ${passwordCheck.isProtected ? 'Yes' : 'No'}</p>`;
                
                if (passwordCheck.isProtected) {
                    resultsHTML += `
                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-lock me-2"></i>Password Required</h6>
                            <p>This PDF is password-protected. You would need to provide the password to process it.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="testPassword" class="form-label">Test Password:</label>
                                    <input type="password" class="form-control" id="testPassword" placeholder="Enter password to test">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <button type="button" class="btn btn-primary" onclick="testWithPassword()">
                                            <i class="fas fa-key me-1"></i> Test with Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    resultsHTML += `
                        <div class="alert alert-success mt-3">
                            <h6><i class="fas fa-unlock me-2"></i>No Password Required</h6>
                            <p>This PDF is not password-protected and can be processed normally.</p>
                        </div>
                    `;
                }
                
                resultsHTML += '</div>';
                resultsDiv.innerHTML = resultsHTML;
                
            } catch (error) {
                console.error('Password handling test error:', error);
                resultsDiv.innerHTML = `<div class="alert alert-danger"><h5>Error</h5><p>${error.message}</p></div>`;
            }
        }

        async function testWithPassword() {
            const password = document.getElementById('testPassword').value;
            const fileInput = document.getElementById('pdfFile');
            const resultsDiv = document.getElementById('results');
            
            if (!password) {
                alert('Please enter a password to test.');
                return;
            }
            
            const file = fileInput.files[0];
            resultsDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Testing with password...</div>';
            
            try {
                console.log('Testing PDF extraction with password');
                
                // Test extraction with password
                const text = await window.pdfTextExtractor.extractTextFromFile(file, password);
                console.log('Extraction with password successful');
                
                resultsDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle me-2"></i>Password Test Successful!</h5>
                        <p><strong>File:</strong> ${file.name}</p>
                        <p><strong>Password:</strong> Correct</p>
                        <p><strong>Extracted Text Length:</strong> ${text.length} characters</p>
                        <h6>Sample Text (first 300 chars):</h6>
                        <pre class="bg-light p-3">${text.substring(0, 300)}...</pre>
                    </div>
                `;
                
            } catch (error) {
                console.error('Password test error:', error);
                resultsDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle me-2"></i>Password Test Failed</h5>
                        <p><strong>Error:</strong> ${error.message}</p>
                        <p>This could mean the password is incorrect or the PDF has other protection mechanisms.</p>
                    </div>
                `;
            }
        }

        async function checkPDFPassword(file) {
            try {
                const arrayBuffer = await readFileAsArrayBuffer(file);
                const pdf = await window['pdfjs-dist/build/pdf'].getDocument({ data: arrayBuffer }).promise;
                return { isProtected: false, error: null };
            } catch (error) {
                if (error.name === 'PasswordException' || error.message.includes('password')) {
                    return { isProtected: true, error: error };
                }
                return { isProtected: false, error: error };
            }
        }

        function readFileAsArrayBuffer(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(reader.error);
                reader.readAsArrayBuffer(file);
            });
        }
    </script>
</body>
</html> 