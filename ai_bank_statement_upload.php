<?php
session_start();
require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

// Check if user is logged in
if (!isset($_SESSION['FirstName'])) {
    header("Location: login.php");
    exit();
}

// Check if OpenAI key is configured
$openai_configured = EnvConfig::hasOpenAIKey();

// Get payroll periods for dropdown
$periods_query = "SELECT periodid as id, PayrollPeriod, PhysicalYear, PhysicalMonth FROM tbpayrollperiods ORDER BY periodid DESC";
$periods_result = mysqli_query($cov, $periods_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Statement Upload & Analysis</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
    /* Enhanced Modern Styling for AI Bank Statement Upload */

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container-fluid {
        padding: 2rem 0;
    }

    /* Card enhancements */
    .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px 16px 0 0 !important;
        border: none;
        padding: 1.5rem;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
    }

    .card-body {
        padding: 2rem;
    }

    /* Enhanced upload area */
    .upload-area {
        border: 3px dashed #667eea;
        border-radius: 20px;
        padding: 3rem 2rem;
        text-align: center;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        transition: all 0.4s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .upload-area::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s;
    }

    .upload-area:hover::before {
        left: 100%;
    }

    .upload-area:hover {
        border-color: #764ba2;
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
    }

    .upload-area.dragover {
        border-color: #10b981;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        transform: scale(1.02);
    }

    .upload-area i {
        color: #667eea;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .upload-area:hover i {
        color: #764ba2;
        transform: scale(1.1);
    }

    .upload-area h5 {
        color: #374151;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .upload-area p {
        color: #6b7280;
        font-size: 0.95rem;
    }

    /* Enhanced file items */
    .file-item {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        margin: 0.5rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .file-item:hover {
        border-color: #667eea;
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }

    .file-item .file-info {
        flex: 1;
        font-weight: 500;
    }

    .file-item .file-actions {
        display: flex;
        gap: 0.5rem;
    }

    /* Enhanced buttons */
    .btn {
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        transform: translateY(-2px);
    }

    .btn-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        transform: translateY(-2px);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
    }

    .btn-secondary:hover {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        transform: translateY(-2px);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
    }

    /* Enhanced form controls */
    .form-control,
    .form-select {
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    /* Enhanced progress bar */
    .progress {
        height: 12px;
        border-radius: 20px;
        background: #e5e7eb;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        transition: width 0.3s ease;
    }

    /* Enhanced table styling */
    .table {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .table th {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        color: #374151;
        font-weight: 600;
        padding: 1rem;
        border-bottom: 2px solid #e5e7eb;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        transform: scale(1.01);
    }

    /* Enhanced badges */
    .badge {
        border-radius: 20px;
        padding: 0.5rem 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .badge-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .badge-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    /* Enhanced alerts */
    .alert {
        border-radius: 12px;
        border: none;
        padding: 1rem 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        font-weight: 500;
    }

    .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border-left: 4px solid #3b82f6;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left: 4px solid #10b981;
    }

    .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
        border-left: 4px solid #ef4444;
    }

    /* Enhanced modals */
    .modal-content {
        border: none;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1.5rem;
    }

    .modal-header .btn-close {
        filter: invert(1);
    }

    .modal-body {
        padding: 2rem;
    }

    .modal-footer {
        border: none;
        padding: 1.5rem;
        background: #f8fafc;
    }

    /* Enhanced analysis results */
    .analysis-result {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1rem 0;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .analysis-result:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
    }

    /* Enhanced match items */
    .match-item {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem;
        margin: 0.5rem 0;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .match-item.matched {
        border-left: 4px solid #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    }

    .match-item.unmatched {
        border-left: 4px solid #ef4444;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    }

    .manual-match {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid #f59e0b;
        border-radius: 12px;
        padding: 1rem;
        margin: 0.5rem 0;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
    }

    /* Enhanced page header */
    .page-header {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .page-header h2 {
        color: #374151;
        font-weight: 700;
        margin: 0;
    }

    .page-header .btn {
        margin-left: 0.5rem;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .upload-area {
            padding: 2rem 1rem;
        }

        .btn {
            margin-bottom: 0.5rem;
            font-size: 0.8rem;
            padding: 0.6rem 1rem;
        }

        .table th,
        .table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8rem;
        }
    }

    /* Loading animation */
    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .loading {
        animation: pulse 1.5s infinite;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2><i class="fas fa-upload me-2"></i> Bank Statement Upload & Analysis</h2>
                        <div>
                            <a href="test_bank_statement_system.php" class="btn btn-warning me-2" target="_blank">
                                <i class="fas fa-cog me-1"></i> System Test
                            </a>
                            <a href="bank_statement_history.php" class="btn btn-info">
                                <i class="fas fa-history me-1"></i> View History
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-file-upload me-2"></i> Upload Bank Statements</h5>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" enctype="multipart/form-data">
                            <?php if (!$openai_configured): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>OpenAI API Key Not Configured</strong><br>
                                Please add your OpenAI API key to the <code>config.env</code> file to use this feature.
                                <br><br>
                                <a href="config_manager.php" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-cog me-1"></i> Configure API Key
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
                                                <i class="fas fa-check-circle me-1"></i> API Key Configured
                                            </span>
                                            <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fas fa-times-circle me-1"></i> API Key Not Configured
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="upload-area" id="uploadArea">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5>Drag & Drop files here or click to browse</h5>
                                <p class="text-muted">Supported formats: PDF, Excel (.xlsx, .xls), Images (.jpg, .jpeg,
                                    .png)</p>
                                <input type="file" id="fileInput" name="files[]" multiple
                                    accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" style="display: none;">
                                <button type="button" class="btn btn-primary"
                                    onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-folder-open me-1"></i> Browse Files
                                </button>
                            </div>

                            <div id="fileList" class="mt-3"></div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-success" id="uploadBtn"
                                    <?php echo !$openai_configured ? 'disabled' : ''; ?>>
                                    <i class="fas fa-upload me-1"></i> Upload & Analyze
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearFiles()">
                                    <i class="fas fa-trash me-1"></i> Clear Files
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Analysis Results -->
                <div class="card mt-4" id="analysisCard" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar me-2"></i> Analysis Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="analysisResults"></div>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="manualMatchContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveManualMatch">Save Match</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- PDF.js for client-side text extraction -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="js/pdf-text-extractor.js"></script>
    <script>
    let uploadedFiles = [];
    let analysisData = [];
    let currentManualMatch = null;

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

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (isValidFile(file)) {
                uploadedFiles.push(file);
                displayFile(file);
            } else {
                alert(`Invalid file type: ${file.name}. Please upload PDF, Excel, or image files only.`);
            }
        });
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
        uploadedFiles.forEach(file => displayFile(file));
    }

    function clearFiles() {
        uploadedFiles = [];
        updateFileList();
        document.getElementById('fileInput').value = '';
    }

    // Form submission
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (uploadedFiles.length === 0) {
            alert('Please select at least one file to upload.');
            return;
        }

        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Extracting PDF Text...';

        try {
            const formData = new FormData();
            formData.append('period', document.getElementById('period').value);

            // Extract PDF text for each file
            for (let i = 0; i < uploadedFiles.length; i++) {
                const file = uploadedFiles[i];
                formData.append('files[]', file);
                
                // Extract text from PDF files
                if (file.type === 'application/pdf') {
                    try {
                        console.log('Extracting text from PDF:', file.name);
                        const extractedText = await window.pdfTextExtractor.extractTextFromFile(file);
                        console.log('Extracted text length:', extractedText.length);
                        
                        if (extractedText && extractedText.trim().length > 0) {
                            formData.append('extracted_texts[]', extractedText);
                            console.log('Successfully extracted text from:', file.name);
                        } else {
                            console.warn('No text extracted from:', file.name);
                            formData.append('extracted_texts[]', '');
                        }
                    } catch (error) {
                        console.error('Error extracting text from PDF:', file.name, error);
                        formData.append('extracted_texts[]', '');
                    }
                } else {
                    formData.append('extracted_texts[]', '');
                }
            }

            uploadAndAnalyze(formData);
        } catch (error) {
            console.error('Error during PDF text extraction:', error);
            alert('Error extracting PDF text: ' + error.message);
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Analyze';
        }
    });

    function uploadAndAnalyze(formData) {
        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

        fetch('bank_statement_processor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Analyze';

                if (data.success) {
                    analysisData = data.data;
                    displayAnalysisResults(data.data);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Analyze';
                console.error('Error:', error);
                alert('An error occurred during processing.');
            });
    }

    function displayAnalysisResults(data) {
        const resultsDiv = document.getElementById('analysisResults');
        const analysisCard = document.getElementById('analysisCard');
        const manualMatchCard = document.getElementById('manualMatchCard');

        if (!Array.isArray(data) || data.length === 0) {
            resultsDiv.innerHTML = '<div class="alert alert-warning">No transactions found in the uploaded file.</div>';
            analysisCard.style.display = 'block';
            return;
        }

        // Count matched and unmatched transactions
        const matchedTransactions = data.filter(t => t.matched);
        const unmatchedTransactions = data.filter(t => !t.matched);

        let html = '<div class="row">';

        // Summary
        html += '<div class="col-12 mb-3">';
        html += '<div class="alert alert-info">';
        html += `<strong>Analysis Summary:</strong> ${data.length} transactions found, ${matchedTransactions.length} matched, ${unmatchedTransactions.length} unmatched`;
        html += '</div>';
        html += '</div>';

        // Matched transactions
        if (matchedTransactions.length > 0) {
            html += '<div class="col-12 mb-3">';
            html += '<h6><i class="fas fa-check-circle text-success me-2"></i> Matched Transactions</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped">';
            html += '<thead><tr><th><input type="checkbox" id="checkAllMatched" onchange="toggleAllMatched(this)"></th><th>Date</th><th>Name</th><th>Member ID</th><th>Matched Name</th><th>Amount</th><th>Type</th><th>Description</th><th>Actions</th></tr></thead><tbody>';

            matchedTransactions.forEach((transaction, index) => {
                html += `<tr>
                        <td><input type="checkbox" class="matched-checkbox" value="${index}" checked></td>
                        <td>${transaction.date || 'N/A'}</td>
                        <td>${transaction.name}</td>
                        <td>${transaction.member_id || 'N/A'}</td>
                        <td>${transaction.member_name || 'N/A'}</td>
                        <td class="${transaction.type === 'credit' ? 'text-success' : 'text-danger'}">
                            ${transaction.type === 'credit' ? '+' : '-'}₦${parseFloat(transaction.amount).toLocaleString()}
                        </td>
                        <td><span class="badge bg-${transaction.type === 'credit' ? 'success' : 'danger'}">${transaction.type}</span></td>
                        <td>${transaction.description || 'N/A'}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="reclassifyTransaction(${index}, 'matched')" title="Reclassify transaction type">
                                    <i class="fas fa-exchange-alt me-1"></i> Reclassify
                                </button>
                                <button class="btn btn-sm btn-success" onclick="insertSingleTransaction('${transaction.member_id}', ${transaction.amount}, '${transaction.type}')" title="Insert this transaction">
                                    <i class="fas fa-save me-1"></i> Insert
                                </button>
                            </div>
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div></div>';
        }

        // Unmatched transactions
        if (unmatchedTransactions.length > 0) {
            html += '<div class="col-12 mb-3">';
            html += '<h6><i class="fas fa-exclamation-triangle text-warning me-2"></i> Unmatched Transactions</h6>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-striped">';
            html += '<thead><tr><th><input type="checkbox" id="checkAllUnmatched" onchange="toggleAllUnmatched(this)"></th><th>Date</th><th>Name</th><th>Amount</th><th>Type</th><th>Description</th><th>Actions</th></tr></thead><tbody>';

            unmatchedTransactions.forEach((transaction, index) => {
                html += `<tr>
                        <td><input type="checkbox" class="unmatched-checkbox" value="${index}"></td>
                        <td>${transaction.date || 'N/A'}</td>
                        <td>${transaction.name}</td>
                        <td class="${transaction.type === 'credit' ? 'text-success' : 'text-danger'}">
                            ${transaction.type === 'credit' ? '+' : '-'}₦${parseFloat(transaction.amount).toLocaleString()}
                        </td>
                        <td><span class="badge bg-${transaction.type === 'credit' ? 'success' : 'danger'}">${transaction.type}</span></td>
                        <td>${transaction.description || 'N/A'}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info" onclick="reclassifyTransaction(${index}, 'unmatched')" title="Reclassify transaction type">
                                    <i class="fas fa-exchange-alt me-1"></i> Reclassify
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="openManualMatchModal('${transaction.name}', ${transaction.amount}, '${transaction.type}')">
                                    <i class="fas fa-user-edit me-1"></i> Manual Match
                                </button>
                            </div>
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div></div>';
        }

        html += '</div>';

        // Add bulk reclassification controls
        if (data.length > 0) {
            html += '<div class="col-12 mt-3">';
            html += '<div class="card">';
            html += '<div class="card-header">';
            html += '<h6><i class="fas fa-cogs me-2"></i> Bulk Actions</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="row">';
            html += '<div class="col-md-6">';
            html += '<button class="btn btn-outline-info" onclick="bulkReclassifyTransactions(\'credit\')">';
            html += '<i class="fas fa-exchange-alt me-1"></i> Reclassify All as Credit';
            html += '</button>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<button class="btn btn-outline-info" onclick="bulkReclassifyTransactions(\'debit\')">';
            html += '<i class="fas fa-exchange-alt me-1"></i> Reclassify All as Debit';
            html += '</button>';
            html += '</div>';
            html += '</div>';
            html += '<div class="row mt-3">';
            html += '<div class="col-md-6">';
            html += '<button class="btn btn-success btn-lg" onclick="processAllTransactions()">';
            html += '<i class="fas fa-save me-1"></i> Process All Transactions';
            html += '</button>';
            html += '<small class="text-muted ms-2">This will save all transactions to the database</small>';
            html += '</div>';
            html += '<div class="col-md-6">';
            html += '<button class="btn btn-primary btn-lg" onclick="processSelectedTransactions()">';
            html += '<i class="fas fa-check-square me-1"></i> Process Selected Only';
            html += '</button>';
            html += '<small class="text-muted ms-2">This will save only checked transactions to the database</small>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        }

        resultsDiv.innerHTML = html;
        analysisCard.style.display = 'block';
    }

    function reclassifyTransaction(index, section) {
        const newType = section === 'matched' ? 
            (analysisData.filter(t => t.matched)[index].type === 'credit' ? 'debit' : 'credit') :
            (analysisData.filter(t => !t.matched)[index].type === 'credit' ? 'debit' : 'credit');
        
        if (confirm(`Are you sure you want to reclassify this transaction from ${section === 'matched' ? analysisData.filter(t => t.matched)[index].type : analysisData.filter(t => !t.matched)[index].type} to ${newType}?`)) {
            if (section === 'matched') {
                analysisData.filter(t => t.matched)[index].type = newType;
            } else {
                analysisData.filter(t => !t.matched)[index].type = newType;
            }
            
            // Refresh the display
            displayAnalysisResults(analysisData);
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                Transaction reclassified successfully from ${section === 'matched' ? analysisData.filter(t => t.matched)[index].type : analysisData.filter(t => !t.matched)[index].type} to ${newType}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }
    }

    function bulkReclassifyTransactions(newType) {
        const count = analysisData.length;
        if (confirm(`Are you sure you want to reclassify all ${count} transactions as ${newType}?`)) {
            analysisData.forEach(transaction => {
                transaction.type = newType;
            });
            
            // Refresh the display
            displayAnalysisResults(analysisData);
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                All ${count} transactions have been reclassified as ${newType}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);
            
            // Auto-dismiss after 3 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 3000);
        }
    }

    function insertSingleTransaction(memberId, amount, type) {
        const period = document.getElementById('period').value;
        if (!period) {
            alert('Please select a period first.');
            return;
        }

        if (confirm(`Are you sure you want to insert this transaction?\nMember ID: ${memberId}\nAmount: ₦${amount.toLocaleString()}\nType: ${type}`)) {
            insertTransaction(memberId, amount, type, period, false); // Don't reload page
        }
    }

    function toggleAllMatched(checkbox) {
        const matchedCheckboxes = document.querySelectorAll('.matched-checkbox');
        matchedCheckboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    function toggleAllUnmatched(checkbox) {
        const unmatchedCheckboxes = document.querySelectorAll('.unmatched-checkbox');
        unmatchedCheckboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    function processSelectedTransactions() {
        if (!analysisData || analysisData.length === 0) {
            alert('No transactions to process.');
            return;
        }

        const period = document.getElementById('period').value;
        if (!period) {
            alert('Please select a period first.');
            return;
        }

        // Get selected transactions
        const selectedMatched = Array.from(document.querySelectorAll('.matched-checkbox:checked')).map(cb => parseInt(cb.value));
        const selectedUnmatched = Array.from(document.querySelectorAll('.unmatched-checkbox:checked')).map(cb => parseInt(cb.value));
        
        const matchedTransactions = analysisData.filter(t => t.matched);
        const unmatchedTransactions = analysisData.filter(t => !t.matched);
        
        const selectedTransactions = [
            ...selectedMatched.map(index => matchedTransactions[index]),
            ...selectedUnmatched.map(index => unmatchedTransactions[index])
        ].filter(t => t); // Remove any undefined entries

        if (selectedTransactions.length === 0) {
            alert('Please select at least one transaction to process.');
            return;
        }

        if (confirm(`Are you sure you want to process ${selectedTransactions.length} selected transactions? This action cannot be undone.`)) {
            // Show loading state
            const processBtn = document.querySelector('button[onclick="processSelectedTransactions()"]');
            const originalText = processBtn.innerHTML;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            processBtn.disabled = true;

            fetch('bank_statement_processor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'process_transactions',
                        transactions: selectedTransactions,
                        period: period
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;

                    if (data.success) {
                        alert(`Success! ${data.processed_count} transactions processed, ${data.skipped_count} skipped, ${data.unmatched_count} unmatched.`);
                        // Refresh the page to show updated results
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    // Reset button
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;
                    console.error('Error:', error);
                    alert('An error occurred while processing transactions.');
                });
        }
    }

    function processAllTransactions() {
        if (!analysisData || analysisData.length === 0) {
            alert('No transactions to process.');
            return;
        }

        const period = document.getElementById('period').value;
        if (!period) {
            alert('Please select a period first.');
            return;
        }

        if (confirm(`Are you sure you want to process all ${analysisData.length} transactions? This action cannot be undone.`)) {
            // Show loading state
            const processBtn = document.querySelector('button[onclick="processAllTransactions()"]');
            const originalText = processBtn.innerHTML;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            processBtn.disabled = true;

            fetch('bank_statement_processor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'process_transactions',
                        transactions: analysisData,
                        period: period
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;

                    if (data.success) {
                        alert(`Success! ${data.processed_count} transactions processed, ${data.skipped_count} skipped, ${data.unmatched_count} unmatched.`);
                        // Refresh the page to show updated results
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    // Reset button
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;
                    console.error('Error:', error);
                    alert('An error occurred while processing transactions.');
                });
        }
    }

    function insertTransaction(coopId, amount, type, period, reloadPage = true) {
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
                    // Refresh the analysis results only if requested
                    if (reloadPage) {
                        location.reload();
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while inserting the transaction.');
            });
    }

    function openManualMatchModal(name, amount, type) {
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
                    // Store original employees for clear search functionality
                    currentManualMatch.originalEmployees = data.employees;
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
                    <label>Search for Employee:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="employeeSearch" placeholder="Type to search for employees...">
                        <button class="btn btn-outline-secondary" type="button" onclick="searchEmployees()">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Select Employee:</label>
                    <select class="form-control" id="manualCoopId" size="8">
                        <option value="">Select an employee...</option>
            `;

        employees.forEach(employee => {
            html += `<option value="${employee.member_id}">${employee.name} (${employee.member_id})</option>`;
        });

        html += `
                    </select>
                    <small class="text-muted">Showing ${employees.length} results. Use search above to find more employees.</small>
                </div>
            `;

        modalContent.innerHTML = html;
        
        // Add event listeners for search input
        const searchInput = document.getElementById('employeeSearch');
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchEmployees();
            }
        });
        
        // Add debounced search on input change
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            if (searchTerm.length >= 2) {
                searchTimeout = setTimeout(() => {
                    searchEmployees();
                }, 500); // 500ms delay
            }
        });
        
        const modal = new bootstrap.Modal(document.getElementById('manualMatchModal'));
        modal.show();
    }

    function searchEmployees() {
        const searchTerm = document.getElementById('employeeSearch').value.trim();
        if (!searchTerm) {
            alert('Please enter a search term.');
            return;
        }

        // Show loading state
        const searchBtn = document.querySelector('#employeeSearch').nextElementSibling;
        const originalText = searchBtn.innerHTML;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        searchBtn.disabled = true;

        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'search_employees',
                    search_term: searchTerm
                })
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                searchBtn.innerHTML = originalText;
                searchBtn.disabled = false;

                if (data.success) {
                    updateEmployeeList(data.employees);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                // Reset button
                searchBtn.innerHTML = originalText;
                searchBtn.disabled = false;
                console.error('Error:', error);
                alert('An error occurred while searching for employees.');
            });
    }

    function updateEmployeeList(employees) {
        const selectElement = document.getElementById('manualCoopId');
        const searchTerm = document.getElementById('employeeSearch').value.trim();
        
        // Clear existing options except the first one
        selectElement.innerHTML = '<option value="">Select an employee...</option>';
        
        if (employees.length === 0) {
            selectElement.innerHTML += '<option value="" disabled>No employees found</option>';
        } else {
            employees.forEach(employee => {
                selectElement.innerHTML += `<option value="${employee.member_id}">${employee.name} (${employee.member_id})</option>`;
            });
        }
        
        // Update the help text
        const helpText = selectElement.nextElementSibling;
        if (helpText && helpText.classList.contains('text-muted')) {
            helpText.textContent = `Showing ${employees.length} results for "${searchTerm}".`;
        }
    }

    function clearSearch() {
        document.getElementById('employeeSearch').value = '';
        // Reset to original search results (the ones that were shown when modal opened)
        if (currentManualMatch && currentManualMatch.originalEmployees) {
            updateEmployeeList(currentManualMatch.originalEmployees);
        }
    }

    document.getElementById('saveManualMatch').addEventListener('click', function() {
        const coopId = document.getElementById('manualCoopId').value;
        if (!coopId) {
            alert('Please select an employee.');
            return;
        }

        // Find the selected employee name
        const selectElement = document.getElementById('manualCoopId');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const selectedEmployeeName = selectedOption.text.split(' (')[0]; // Get name without ID

        // Find the transaction in analysisData and update it
        const transactionName = currentManualMatch.name;
        const transactionIndex = analysisData.findIndex(t => t.name === transactionName);
        
        if (transactionIndex !== -1) {
            // Update the transaction with match information
            analysisData[transactionIndex].matched = true;
            analysisData[transactionIndex].member_id = coopId;
            analysisData[transactionIndex].member_name = selectedEmployeeName;
            
            // Refresh the display to show the updated transaction
            displayAnalysisResults(analysisData);
            
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                Transaction "${transactionName}" successfully matched to "${selectedEmployeeName}" (ID: ${coopId})
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('manualMatchModal'));
        modal.hide();
    });
    </script>
</body>

</html>