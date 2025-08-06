<?php
session_start();
require_once('Connections/coop.php');
require_once('config/EnvConfig.php');

// Check if user is logged in
if (!isset($_SESSION['SESS_FIRST_NAME'])) {
    header("Location: login.php");
    exit();
}

// Check if OpenAI key is configured
$openai_configured = EnvConfig::hasOpenAIKey();

// Get payroll periods for dropdown
$periods_query = "SELECT id, PayrollPeriod, PhysicalYear, PhysicalMonth FROM tbpayrollperiods ORDER BY id DESC";
$periods_result = mysqli_query($coop, $periods_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Statement Upload & Analysis</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="datatable/datatables.min.css" rel="stylesheet">
    <style>
    .upload-area {
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 40px;
        text-align: center;
        background: #f9f9f9;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #007bff;
        background: #f0f8ff;
    }

    .upload-area.dragover {
        border-color: #28a745;
        background: #f0fff0;
    }

    .file-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin: 5px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .file-item .file-info {
        flex: 1;
    }

    .file-item .file-actions {
        display: flex;
        gap: 10px;
    }

    .progress {
        height: 20px;
        margin-top: 10px;
    }

    .analysis-result {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin: 10px 0;
    }

    .match-item {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin: 5px 0;
    }

    .match-item.matched {
        border-left: 4px solid #28a745;
    }

    .match-item.unmatched {
        border-left: 4px solid #dc3545;
    }

    .manual-match {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 5px;
        padding: 10px;
        margin: 5px 0;
    }

    /* PDF Page specific styles */
    .file-item.pdf-page {
        border-left: 4px solid #007bff;
        background: #f8f9fa;
    }

    .file-item.pdf-page .file-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pdf-page-results {
        margin-top: 10px;
        padding: 8px;
        background: #e9ecef;
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .pdf-bulk-actions {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        margin: 15px 0;
    }

    .pdf-bulk-actions h6 {
        margin-bottom: 10px;
        color: #495057;
    }

    .btn-group .btn {
        margin-right: 5px;
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fa fa-upload"></i> Bank Statement Upload & Analysis</h2>
                    <div>
                        <a href="test_bank_statement_system.php" class="btn btn-warning me-2" target="_blank">
                            <i class="fa fa-cog"></i> System Test
                        </a>
                        <a href="bank_statement_history.php" class="btn btn-info">
                            <i class="fa fa-history"></i> View History
                        </a>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fa fa-file-upload"></i> Upload Bank Statements</h5>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <?php if (!$openai_configured): ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>OpenAI API Key Not Configured</strong><br>
                                Please add your OpenAI API key to the <code>config.env</code> file to use this feature.
                                <br><br>
                                <a href="config_manager.php" class="btn btn-sm btn-outline-warning">
                                    <i class="fa fa-cog"></i> Configure API Key
                                </a>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="period">Select Period:</label>
                                        <select class="form-control" id="period" name="period" required
                                            <?php echo !$openai_configured ? 'disabled' : ''; ?>>
                                            <option value="">Select a period...</option>
                                            <?php while ($period = mysqli_fetch_assoc($periods_result)) { ?>
                                            <option value="<?php echo $period['id']; ?>">
                                                <?php echo $period['PayrollPeriod'] . ' (' . $period['PhysicalMonth'] . ' ' . $period['PhysicalYear'] . ')'; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>OpenAI API Status:</label>
                                        <div class="form-control-plaintext">
                                            <?php if ($openai_configured): ?>
                                            <span class="text-success">
                                                <i class="fa fa-check-circle"></i> API Key Configured
                                            </span>
                                            <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fa fa-times-circle"></i> API Key Not Configured
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="upload-area" id="uploadArea">
                                <i class="fa fa-cloud-upload fa-3x text-muted mb-3"></i>
                                <h5>Drag & Drop files here or click to browse</h5>
                                <p class="text-muted">Supported formats: PDF, Excel (.xlsx, .xls), Images (.jpg, .jpeg,
                                    .png)</p>
                                <input type="file" id="fileInput" name="files[]" multiple
                                    accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" style="display: none;">
                                <button type="button" class="btn btn-primary"
                                    onclick="document.getElementById('fileInput').click()">
                                    <i class="fa fa-folder-open"></i> Browse Files
                                </button>
                            </div>

                            <div id="fileList" class="mt-3"></div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success" id="uploadBtn"
                                    <?php echo !$openai_configured ? 'disabled' : ''; ?>>
                                    <i class="fa fa-upload"></i> Upload & Analyze
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearFiles()">
                                    <i class="fa fa-trash"></i> Clear Files
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Analysis Results -->
                <div class="card mt-4" id="analysisCard" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fa fa-chart-bar"></i> Analysis Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="analysisResults"></div>
                    </div>
                </div>

                <!-- Manual Matching Section -->
                <div class="card mt-4" id="manualMatchCard" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fa fa-user-edit"></i> Manual Name Matching</h5>
                    </div>
                    <div class="card-body">
                        <div id="manualMatchResults"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Match Modal -->
    <div class="modal fade" id="manualMatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manual Name Matching</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="manualMatchContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveManualMatch">Save Match</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PDF.js for client-side PDF handling -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="datatable/datatables.min.js"></script>
    <script>
    // Initialize PDF.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let uploadedFiles = [];
    let analysisData = [];
    let currentManualMatch = null;
    let pdfPages = []; // Store split PDF pages
    let allTransactions = []; // Store all transactions from all pages

    // Drag and drop functionality
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('fileInput');

    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    async function handleFiles(files) {
        for (let file of Array.from(files)) {
            if (isValidFile(file)) {
                if (file.type === 'application/pdf') {
                    // Handle PDF files - check if multi-page and split
                    await handlePDFFile(file);
                } else {
                    // Handle other file types normally
                    uploadedFiles.push(file);
                    displayFile(file);
                }
            } else {
                alert(`Invalid file type: ${file.name}. Please upload PDF, Excel, or image files only.`);
            }
        }
    }

    function isValidFile(file) {
        const validTypes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];
        return validTypes.includes(file.type);
    }

    function displayFile(file) {
        const fileList = document.getElementById('fileList');
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        fileItem.innerHTML = `
                <div class="file-info">
                    <strong>${file.name}</strong> (${formatFileSize(file.size)})
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeFile('${file.name}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            `;
        fileList.appendChild(fileItem);
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function removeFile(fileName) {
        uploadedFiles = uploadedFiles.filter(file => file.name !== fileName);
        updateFileList();
    }

    function updateFileList() {
        const fileList = document.getElementById('fileList');
        fileList.innerHTML = '';

        // Display regular files
        uploadedFiles.forEach(file => displayFile(file));

        // Display PDF pages
        pdfPages.forEach(pageData => displayPDFPage(pageData));

        // Show bulk actions if there are PDF pages
        if (pdfPages.length > 0) {
            showPDFBulkActions();
        }
    }

    async function handlePDFFile(file) {
        try {
            console.log('Processing PDF file:', file.name);

            // Load PDF document
            const arrayBuffer = await file.arrayBuffer();
            const pdfDocument = await pdfjsLib.getDocument({
                data: arrayBuffer
            }).promise;

            console.log('PDF loaded, pages:', pdfDocument.numPages);

            if (pdfDocument.numPages === 1) {
                // Single page PDF - handle normally
                console.log('Single page PDF, processing normally');
                uploadedFiles.push(file);
                displayFile(file);
            } else {
                // Multi-page PDF - split into pages
                console.log('Multi-page PDF, splitting into pages');
                await splitPDFIntoPages(file, pdfDocument, arrayBuffer);
            }
        } catch (error) {
            console.error('Error processing PDF:', error);
            alert('Error processing PDF: ' + error.message);
        }
    }

    async function splitPDFIntoPages(file, pdfDocument, arrayBuffer) {
        console.log('Splitting PDF into pages...');
        const fileList = document.getElementById('fileList');

        // Clear existing PDF pages
        pdfPages = [];

        for (let pageNum = 1; pageNum <= pdfDocument.numPages; pageNum++) {
            try {
                console.log(`Creating page ${pageNum}`);

                // Create page object
                const pageData = {
                    originalFile: file,
                    pageNum: pageNum,
                    arrayBuffer: arrayBuffer,
                    pdfDocument: pdfDocument,
                    processed: false,
                    transactions: []
                };

                pdfPages.push(pageData);

                // Display page row
                displayPDFPage(pageData);

            } catch (error) {
                console.error(`Error processing page ${pageNum}:`, error);
            }
        }

        console.log(`Created ${pdfPages.length} page objects`);

        // Show bulk actions for PDF pages
        showPDFBulkActions();
    }

    function displayPDFPage(pageData) {
        console.log('Displaying PDF page:', pageData.pageNum);

        const fileList = document.getElementById('fileList');
        const pageItem = document.createElement('div');
        pageItem.className = 'file-item pdf-page';
        pageItem.id = `page-${pageData.pageNum}`;

        pageItem.innerHTML = `
            <div class="file-info">
                <strong>${pageData.originalFile.name} - Page ${pageData.pageNum}</strong>
                <span class="badge badge-info ml-2">PDF Page</span>
                <span class="badge badge-secondary ml-2" id="status-${pageData.pageNum}">Pending</span>
            </div>
            <div class="file-actions">
                <button type="button" class="btn btn-sm btn-primary" onclick="processPDFPage(${pageData.pageNum})" id="btn-process-${pageData.pageNum}">
                    <i class="fa fa-cog"></i> Process Page
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="removePDFPage(${pageData.pageNum})">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        `;

        fileList.appendChild(pageItem);
        console.log('PDF page element added to DOM');
    }

    function showPDFBulkActions() {
        // Remove existing bulk actions if any
        const existingBulkActions = document.getElementById('pdfBulkActions');
        if (existingBulkActions) {
            existingBulkActions.remove();
        }

        const fileList = document.getElementById('fileList');
        const bulkActionsDiv = document.createElement('div');
        bulkActionsDiv.className = 'pdf-bulk-actions';
        bulkActionsDiv.id = 'pdfBulkActions';

        bulkActionsDiv.innerHTML = `
            <h6><i class="fa fa-tasks"></i> PDF Pages Actions</h6>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success btn-sm" onclick="processAllPDFPages()">
                    <i class="fa fa-play"></i> Process All Pages
                </button>
                <button type="button" class="btn btn-warning btn-sm" onclick="processSelectedPDFPages()">
                    <i class="fa fa-check-square"></i> Process Selected
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearPDFPages()">
                    <i class="fa fa-trash"></i> Clear All Pages
                </button>
            </div>
        `;

        fileList.appendChild(bulkActionsDiv);
    }

    async function processPDFPage(pageNum) {
        const pageData = pdfPages.find(p => p.pageNum === pageNum);
        if (!pageData || pageData.processed) return;

        // Update status
        updatePageStatus(pageNum, 'processing');

        try {
            // Extract text from the specific page
            const page = await pageData.pdfDocument.getPage(pageNum);
            const textContent = await page.getTextContent();
            const pageText = textContent.items.map(item => item.str).join(' ');

            // Send to backend for processing
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('file', pageData.originalFile);
            formData.append('pdf_texts', JSON.stringify([pageText]));
            formData.append('pdf_names', JSON.stringify([`page_${pageNum}_${pageData.originalFile.name}`]));
            formData.append('period', document.getElementById('period').value);
            formData.append('page_number', pageNum);

            const response = await fetch('bank_statement_processor.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Store the results
                pageData.processed = true;
                pageData.transactions = result.data || [];

                // Add to all transactions
                allTransactions = allTransactions.concat(pageData.transactions);

                updatePageStatus(pageNum, 'completed');
                displayPageResults(pageNum, result);

                // Update overall results display
                updateOverallResults();

            } else {
                updatePageStatus(pageNum, 'error');
                alert(`Error processing page ${pageNum}: ${result.message}`);
            }

        } catch (error) {
            console.error(`Error processing page ${pageNum}:`, error);
            updatePageStatus(pageNum, 'error');
            alert(`Error processing page ${pageNum}: ${error.message}`);
        }
    }

    function updatePageStatus(pageNum, status) {
        const statusElement = document.getElementById(`status-${pageNum}`);
        const buttonElement = document.getElementById(`btn-process-${pageNum}`);

        if (statusElement) {
            const statusClasses = {
                'pending': 'badge-secondary',
                'processing': 'badge-warning',
                'completed': 'badge-success',
                'error': 'badge-danger'
            };

            statusElement.className = `badge ${statusClasses[status]} ml-2`;
            statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        }

        if (buttonElement) {
            buttonElement.disabled = status === 'processing';
            if (status === 'completed') {
                buttonElement.innerHTML = '<i class="fa fa-check"></i> Completed';
                buttonElement.className = 'btn btn-sm btn-success';
            } else if (status === 'error') {
                buttonElement.innerHTML = '<i class="fa fa-exclamation-triangle"></i> Error';
                buttonElement.className = 'btn btn-sm btn-danger';
            }
        }
    }

    function displayPageResults(pageNum, result) {
        const pageItem = document.getElementById(`page-${pageNum}`);
        if (pageItem) {
            const resultsDiv = document.createElement('div');
            resultsDiv.className = 'pdf-page-results';
            resultsDiv.innerHTML = `
                <strong>Results:</strong> ${result.data?.length || 0} transactions found
                (${result.data?.filter(t => t.matched).length || 0} matched, 
                ${result.data?.filter(t => !t.matched).length || 0} unmatched)
            `;
            pageItem.appendChild(resultsDiv);
        }
    }

    function updateOverallResults() {
        if (allTransactions.length > 0) {
            const totalTransactions = allTransactions.length;
            const matchedCount = allTransactions.filter(t => t.matched).length;
            const unmatchedCount = allTransactions.filter(t => !t.matched).length;

            // Display overall results
            displayAnalysisResults({
                total_transactions: totalTransactions,
                matched_count: matchedCount,
                unmatched_count: unmatchedCount,
                matched_transactions: allTransactions.filter(t => t.matched),
                unmatched_transactions: allTransactions.filter(t => !t.matched)
            });
        }
    }

    async function processAllPDFPages() {
        const pendingPages = pdfPages.filter(p => !p.processed);
        if (pendingPages.length === 0) {
            alert('No pending pages to process.');
            return;
        }

        for (let pageData of pendingPages) {
            await processPDFPage(pageData.pageNum);
        }
    }

    function processSelectedPDFPages() {
        // Implementation for processing selected pages
        alert('Process selected pages functionality would be implemented here.');
    }

    function removePDFPage(pageNum) {
        pdfPages = pdfPages.filter(p => p.pageNum !== pageNum);
        const pageElement = document.getElementById(`page-${pageNum}`);
        if (pageElement) {
            pageElement.remove();
        }

        // Remove from allTransactions
        allTransactions = allTransactions.filter(t => !t.pageNum || t.pageNum !== pageNum);

        // Update overall results
        updateOverallResults();
    }

    function clearPDFPages() {
        if (confirm('Are you sure you want to clear all PDF pages?')) {
            pdfPages = [];
            allTransactions = [];
            const fileList = document.getElementById('fileList');

            // Remove all PDF page elements
            const pdfElements = fileList.querySelectorAll('[id^="page-"]');
            pdfElements.forEach(el => el.remove());

            // Remove bulk actions
            const bulkActions = document.getElementById('pdfBulkActions');
            if (bulkActions) {
                bulkActions.remove();
            }

            // Clear analysis results
            document.getElementById('analysisCard').style.display = 'none';
            document.getElementById('manualMatchCard').style.display = 'none';
        }
    }

    function clearFiles() {
        uploadedFiles = [];
        pdfPages = [];
        allTransactions = [];
        updateFileList();
        document.getElementById('fileInput').value = '';

        // Clear PDF bulk actions
        const bulkActions = document.getElementById('pdfBulkActions');
        if (bulkActions) {
            bulkActions.remove();
        }

        // Clear analysis results
        document.getElementById('analysisCard').style.display = 'none';
        document.getElementById('manualMatchCard').style.display = 'none';
    }

    // Form submission
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (uploadedFiles.length === 0 && pdfPages.length === 0) {
            alert('Please select at least one file to upload.');
            return;
        }

        // If we have PDF pages, they should be processed individually
        if (pdfPages.length > 0) {
            alert(
                'Please process PDF pages individually using the "Process Page" buttons or "Process All Pages".'
            );
            return;
        }

        // Handle regular files
        const formData = new FormData();
        formData.append('period', document.getElementById('period').value);

        uploadedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        uploadAndAnalyze(formData);
    });

    function uploadAndAnalyze(formData) {
        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';

        fetch('bank_statement_processor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fa fa-upload"></i> Upload & Analyze';

                if (data.success) {
                    analysisData = data.data;
                    displayAnalysisResults(data.data);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fa fa-upload"></i> Upload & Analyze';
                console.error('Error:', error);
                alert('An error occurred during processing.');
            });
    }

    function displayAnalysisResults(data) {
        const resultsDiv = document.getElementById('analysisResults');
        const analysisCard = document.getElementById('analysisCard');
        const manualMatchCard = document.getElementById('manualMatchCard');

        let html = '<div class="row">';

        // Summary
        html += '<div class="col-12 mb-3">';
        html += '<div class="alert alert-info">';
        html +=
            `<strong>Analysis Summary:</strong> ${data.total_transactions} transactions found, ${data.matched_count} matched, ${data.unmatched_count} unmatched`;
        html += '</div>';
        html += '</div>';

        // Matched transactions
        if (data.matched_transactions.length > 0) {
            html += '<div class="col-12 mb-3">';
            html += '<h6><i class="fa fa-check-circle text-success"></i> Matched Transactions</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped">';
            html +=
                '<thead><tr><th>Name</th><th>Coop ID</th><th>Amount</th><th>Type</th><th>Action</th></tr></thead><tbody>';

            data.matched_transactions.forEach(transaction => {
                html += `<tr>
                        <td>${transaction.name}</td>
                        <td>${transaction.coop_id}</td>
                        <td class="${transaction.type === 'credit' ? 'text-success' : 'text-danger'}">
                            ${transaction.type === 'credit' ? '+' : '-'}₦${transaction.amount.toLocaleString()}
                        </td>
                        <td><span class="badge badge-${transaction.type === 'credit' ? 'success' : 'danger'}">${transaction.type}</span></td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="insertTransaction('${transaction.coop_id}', ${transaction.amount}, '${transaction.type}', ${data.period})">
                                <i class="fa fa-save"></i> Insert
                            </button>
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div></div>';
        }

        // Unmatched transactions
        if (data.unmatched_transactions.length > 0) {
            html += '<div class="col-12 mb-3">';
            html += '<h6><i class="fa fa-exclamation-triangle text-warning"></i> Unmatched Transactions</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped">';
            html += '<thead><tr><th>Name</th><th>Amount</th><th>Type</th><th>Action</th></tr></thead><tbody>';

            data.unmatched_transactions.forEach(transaction => {
                html += `<tr>
                        <td>${transaction.name}</td>
                        <td class="${transaction.type === 'credit' ? 'text-success' : 'text-danger'}">
                            ${transaction.type === 'credit' ? '+' : '-'}₦${transaction.amount.toLocaleString()}
                        </td>
                        <td><span class="badge badge-${transaction.type === 'credit' ? 'success' : 'danger'}">${transaction.type}</span></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="showManualMatch('${transaction.name}', ${transaction.amount}, '${transaction.type}')">
                                <i class="fa fa-user-edit"></i> Manual Match
                            </button>
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div></div>';
        }

        html += '</div>';

        resultsDiv.innerHTML = html;
        analysisCard.style.display = 'block';

        if (data.unmatched_transactions.length > 0) {
            manualMatchCard.style.display = 'block';
        }
    }

    function insertTransaction(coopId, amount, type, period) {
        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'insert_transaction',
                    coop_id: coopId,
                    amount: amount,
                    type: type,
                    period: period
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaction inserted successfully!');
                    // Refresh the analysis results
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while inserting the transaction.');
            });
    }

    function showManualMatch(name, amount, type) {
        currentManualMatch = {
            name,
            amount,
            type
        };

        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'search_employees',
                    search_term: name
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayManualMatchModal(name, amount, type, data.employees);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while searching for employees.');
            });
    }

    function displayManualMatchModal(name, amount, type, employees) {
        const modalContent = document.getElementById('manualMatchContent');
        let html = `
                <div class="mb-3">
                    <strong>Transaction:</strong> ${name} - ${type === 'credit' ? '+' : '-'}₦${amount.toLocaleString()}
                </div>
                <div class="mb-3">
                    <label>Select Employee:</label>
                    <select class="form-control" id="manualCoopId">
                        <option value="">Select an employee...</option>
            `;

        employees.forEach(employee => {
            const fullName = `${employee.FirstName} ${employee.MiddleName} ${employee.LastName}`.trim();
            html += `<option value="${employee.CoopID}">${fullName} (${employee.CoopID})</option>`;
        });

        html += `
                    </select>
                </div>
            `;

        modalContent.innerHTML = html;
        $('#manualMatchModal').modal('show');
    }

    document.getElementById('saveManualMatch').addEventListener('click', function() {
        const coopId = document.getElementById('manualCoopId').value;
        if (!coopId) {
            alert('Please select an employee.');
            return;
        }

        insertTransaction(coopId, currentManualMatch.amount, currentManualMatch.type, document.getElementById(
            'period').value);
        $('#manualMatchModal').modal('hide');
    });
    </script>
</body>

</html>