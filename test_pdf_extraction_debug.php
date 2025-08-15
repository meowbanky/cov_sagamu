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
    <title>PDF Extraction Debug Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bug me-2"></i>PDF Extraction Debug Test</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="pdfFile" class="form-label">Select PDF File:</label>
                    <input type="file" class="form-control" id="pdfFile" accept=".pdf">
                </div>
                
                <button class="btn btn-primary" onclick="testPDFExtraction()">
                    <i class="fas fa-play me-2"></i>Test PDF Extraction
                </button>
                
                <div id="results" class="mt-4"></div>
            </div>
        </div>
    </div>

    <!-- PDF.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="js/pdf-text-extractor.js"></script>
    
    <script>
        async function testPDFExtraction() {
            const fileInput = document.getElementById('pdfFile');
            const resultsDiv = document.getElementById('results');
            
            if (!fileInput.files[0]) {
                alert('Please select a PDF file first.');
                return;
            }
            
            const file = fileInput.files[0];
            resultsDiv.innerHTML = '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Testing PDF extraction...</div>';
            
            try {
                console.log('Testing PDF extraction for:', file.name);
                console.log('PDF text extractor available:', typeof window.pdfTextExtractor);
                
                if (!window.pdfTextExtractor) {
                    throw new Error('PDF text extractor not loaded');
                }
                
                // Test single page extraction first
                console.log('Testing single page extraction...');
                const singleText = await window.pdfTextExtractor.extractText(file);
                console.log('Single page extraction result:', singleText);
                
                // Test multi-page extraction
                console.log('Testing multi-page extraction...');
                const pages = await window.pdfTextExtractor.extractTextByPages(file);
                console.log('Multi-page extraction result:', pages);
                
                let resultsHTML = '<div class="alert alert-success"><h5>PDF Extraction Test Results</h5>';
                resultsHTML += `<p><strong>File:</strong> ${file.name}</p>`;
                resultsHTML += `<p><strong>File size:</strong> ${(file.size / 1024).toFixed(2)} KB</p>`;
                resultsHTML += `<p><strong>Single page text length:</strong> ${singleText.length} characters</p>`;
                resultsHTML += `<p><strong>Number of pages:</strong> ${pages.length}</p>`;
                
                resultsHTML += '<h6>Single Page Text (first 500 chars):</h6>';
                resultsHTML += `<pre class="bg-light p-3">${singleText.substring(0, 500)}...</pre>`;
                
                resultsHTML += '<h6>Multi-Page Results:</h6>';
                pages.forEach((page, index) => {
                    resultsHTML += `<p><strong>Page ${page.pageNumber}:</strong> ${page.text.length} characters</p>`;
                    resultsHTML += `<pre class="bg-light p-2 small">${page.text.substring(0, 200)}...</pre>`;
                });
                
                resultsHTML += '</div>';
                resultsDiv.innerHTML = resultsHTML;
                
            } catch (error) {
                console.error('PDF extraction error:', error);
                resultsDiv.innerHTML = `<div class="alert alert-danger"><h5>Error</h5><p>${error.message}</p></div>`;
            }
        }
    </script>
</body>
</html> 