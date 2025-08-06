<?php
session_start();

// Check if user is logged in
$is_logged_in = false;
$session_vars = ['UserID', 'userid', 'SESS_FIRST_NAME', 'FirstName'];
$user_id = null;
$user_name = null;

foreach ($session_vars as $var) {
    if (isset($_SESSION[$var])) {
        $is_logged_in = true;
        if ($var === 'UserID' || $var === 'userid') {
            $user_id = $_SESSION[$var];
        } else {
            $user_name = $_SESSION[$var];
        }
        break;
    }
}

if (!$is_logged_in) {
    header('Location: login.php');
    exit();
}

include 'header.php';
?>

<style>
/* PDF Splitter specific styles */
.pdf-splitter-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.upload-section {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    margin-bottom: 30px;
    transition: all 0.3s ease;
}

.upload-section:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.upload-section.dragover {
    border-color: #28a745;
    background: #d4edda;
}

.pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 30px;
}

.page-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.page-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.page-preview {
    width: 100%;
    height: 200px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    overflow: hidden;
}

.page-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.page-info {
    margin-bottom: 15px;
}

.page-info h5 {
    color: #495057;
    margin-bottom: 5px;
}

.page-info p {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

.page-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-process-page {
    flex: 1;
    min-width: 120px;
}

.progress-container {
    margin-top: 20px;
    display: none;
}

.progress {
    height: 25px;
    border-radius: 15px;
}

.progress-bar {
    border-radius: 15px;
    font-size: 12px;
    line-height: 25px;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-error {
    background: #f8d7da;
    color: #721c24;
}

.file-info {
    background: #e9ecef;
    border-radius: 5px;
    padding: 15px;
    margin: 20px 0;
}

.file-info h6 {
    color: #495057;
    margin-bottom: 10px;
}

.file-info p {
    margin: 5px 0;
    color: #6c757d;
}

.bulk-actions {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.bulk-actions h5 {
    color: #495057;
    margin-bottom: 15px;
}

.bulk-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .pages-grid {
        grid-template-columns: 1fr;
    }
    
    .page-actions {
        flex-direction: column;
    }
    
    .btn-process-page {
        min-width: auto;
    }
}
</style>

<div class="pdf-splitter-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-pdf"></i> PDF Splitter & Processor</h2>
        <a href="ai_bank_statement_upload.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Single Upload
        </a>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>How it works:</strong> Upload a multi-page PDF bank statement, split it into individual pages, then process each page separately to avoid memory/timeout issues.
    </div>

    <!-- Period Selection -->
    <div class="mb-4">
        <label for="periodSelect" class="form-label fw-bold">
            <i class="fas fa-calendar"></i> Period:
        </label>
        <select id="periodSelect" name="periodSelect" class="form-select">
            <option value="">Select Period</option>
            <option value="2025-01">January 2025</option>
            <option value="2025-02">February 2025</option>
            <option value="2025-03">March 2025</option>
            <option value="2025-04">April 2025</option>
            <option value="2025-05">May 2025</option>
            <option value="2025-06" selected>June 2025</option>
            <option value="2025-07">July 2025</option>
            <option value="2025-08">August 2025</option>
            <option value="2025-09">September 2025</option>
            <option value="2025-10">October 2025</option>
            <option value="2025-11">November 2025</option>
            <option value="2025-12">December 2025</option>
        </select>
    </div>

    <!-- Upload Section -->
    <div class="upload-section" id="uploadSection">
        <div id="uploadContent">
            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
            <h4>Upload Multi-Page PDF</h4>
            <p class="text-muted">Drag and drop your PDF file here or click to browse</p>
            <input type="file" id="pdfFile" accept=".pdf" style="display: none;">
            <button class="btn btn-primary btn-lg" onclick="document.getElementById('pdfFile').click()">
                <i class="fas fa-upload"></i> Choose PDF File
            </button>
            <p class="mt-2 text-muted"><small>Maximum file size: 50MB</small></p>
        </div>
        
        <div id="uploadProgress" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
            <p class="mt-2">Uploading PDF...</p>
        </div>
    </div>

    <!-- File Info Section -->
    <div id="fileInfo" class="file-info" style="display: none;">
        <h6><i class="fas fa-file-pdf"></i> File Information</h6>
        <div id="fileDetails"></div>
    </div>

    <!-- Bulk Actions -->
    <div id="bulkActions" class="bulk-actions" style="display: none;">
        <h5><i class="fas fa-tasks"></i> Bulk Actions</h5>
        <div class="bulk-buttons">
            <button class="btn btn-success" onclick="processAllPages()">
                <i class="fas fa-play"></i> Process All Pages
            </button>
            <button class="btn btn-warning" onclick="processSelectedPages()">
                <i class="fas fa-check-square"></i> Process Selected
            </button>
            <button class="btn btn-info" onclick="downloadAllPages()">
                <i class="fas fa-download"></i> Download All Pages
            </button>
            <button class="btn btn-secondary" onclick="resetSplitter()">
                <i class="fas fa-redo"></i> Reset
            </button>
        </div>
    </div>

    <!-- Progress Bar -->
    <div id="progressContainer" class="progress-container">
        <div class="progress">
            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: 0%">
                0%
            </div>
        </div>
        <p class="text-center mt-2">
            <span id="progressText">Processing pages...</span>
        </p>
    </div>

    <!-- Pages Grid -->
    <div id="pagesGrid" class="pages-grid"></div>
</div>

<!-- PDF.js for client-side PDF handling -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Global variables
let pdfDocument = null;
let uploadedFile = null;
let pageFiles = [];
let processingPages = new Set();

// Initialize PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

// File upload handling
document.getElementById('pdfFile').addEventListener('change', handleFileSelect);

// Drag and drop handling
const uploadSection = document.getElementById('uploadSection');
uploadSection.addEventListener('dragover', handleDragOver);
uploadSection.addEventListener('drop', handleDrop);
uploadSection.addEventListener('dragenter', handleDragEnter);
uploadSection.addEventListener('dragleave', handleDragLeave);

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
}

function handleDragEnter(e) {
    e.preventDefault();
    e.stopPropagation();
    uploadSection.classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    uploadSection.classList.remove('dragover');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    uploadSection.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0 && files[0].type === 'application/pdf') {
        handleFile(files[0]);
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        handleFile(file);
    }
}

async function handleFile(file) {
    if (file.type !== 'application/pdf') {
        alert('Please select a PDF file.');
        return;
    }

    if (file.size > 50 * 1024 * 1024) { // 50MB limit
        alert('File size must be less than 50MB.');
        return;
    }

    uploadedFile = file;
    showUploadProgress();
    
    try {
        // Load PDF document
        const arrayBuffer = await file.arrayBuffer();
        pdfDocument = await pdfjsLib.getDocument({data: arrayBuffer}).promise;
        
        // Show file info
        showFileInfo(file, pdfDocument.numPages);
        
        // Split PDF into pages
        await splitPDFIntoPages(arrayBuffer);
        
        // Show bulk actions
        document.getElementById('bulkActions').style.display = 'block';
        
    } catch (error) {
        console.error('Error processing PDF:', error);
        alert('Error processing PDF: ' + error.message);
        hideUploadProgress();
    }
}

function showUploadProgress() {
    document.getElementById('uploadContent').style.display = 'none';
    document.getElementById('uploadProgress').style.display = 'block';
}

function hideUploadProgress() {
    document.getElementById('uploadContent').style.display = 'block';
    document.getElementById('uploadProgress').style.display = 'none';
}

function showFileInfo(file, numPages) {
    const fileInfo = document.getElementById('fileInfo');
    const fileDetails = document.getElementById('fileDetails');
    
    fileDetails.innerHTML = `
        <p><strong>Filename:</strong> ${file.name}</p>
        <p><strong>Size:</strong> ${formatFileSize(file.size)}</p>
        <p><strong>Pages:</strong> ${numPages}</p>
        <p><strong>Upload Date:</strong> ${new Date().toLocaleString()}</p>
    `;
    
    fileInfo.style.display = 'block';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

async function splitPDFIntoPages(arrayBuffer) {
    const pagesGrid = document.getElementById('pagesGrid');
    pagesGrid.innerHTML = '';
    pageFiles = [];

    for (let pageNum = 1; pageNum <= pdfDocument.numPages; pageNum++) {
        try {
            // Create page card
            const pageCard = createPageCard(pageNum);
            pagesGrid.appendChild(pageCard);

            // Generate page preview
            await generatePagePreview(pageNum, pageCard);

            // Create page file
            const pageFile = await createPageFile(arrayBuffer, pageNum);
            pageFiles.push({
                pageNum: pageNum,
                file: pageFile,
                card: pageCard,
                status: 'pending'
            });

        } catch (error) {
            console.error(`Error processing page ${pageNum}:`, error);
        }
    }
}

function createPageCard(pageNum) {
    const card = document.createElement('div');
    card.className = 'page-card';
    card.innerHTML = `
        <div class="page-preview" id="preview-${pageNum}">
            <i class="fas fa-file-pdf fa-2x text-muted"></i>
        </div>
        <div class="page-info">
            <h5>Page ${pageNum}</h5>
            <p>Status: <span class="status-badge status-pending" id="status-${pageNum}">Pending</span></p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary btn-process-page" onclick="processPage(${pageNum})" id="btn-process-${pageNum}">
                <i class="fas fa-cog"></i> Process Page
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="downloadPage(${pageNum})">
                <i class="fas fa-download"></i>
            </button>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="select-${pageNum}" checked>
                <label class="form-check-label" for="select-${pageNum}">
                    Select
                </label>
            </div>
        </div>
    `;
    return card;
}

async function generatePagePreview(pageNum, pageCard) {
    try {
        const page = await pdfDocument.getPage(pageNum);
        const viewport = page.getViewport({scale: 0.5});
        
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        
        await page.render({
            canvasContext: context,
            viewport: viewport
        }).promise;
        
        const previewDiv = pageCard.querySelector('.page-preview');
        previewDiv.innerHTML = '';
        previewDiv.appendChild(canvas);
        
    } catch (error) {
        console.error(`Error generating preview for page ${pageNum}:`, error);
    }
}

async function createPageFile(arrayBuffer, pageNum) {
    // For now, we'll create a reference to the original file with page info
    // In a real implementation, you might want to use a PDF library to extract individual pages
    return {
        name: `page_${pageNum}_${uploadedFile.name}`,
        pageNum: pageNum,
        originalFile: uploadedFile,
        arrayBuffer: arrayBuffer
    };
}

async function processPage(pageNum) {
    const pageData = pageFiles.find(p => p.pageNum === pageNum);
    if (!pageData) return;

    if (processingPages.has(pageNum)) {
        alert('Page is already being processed.');
        return;
    }

    processingPages.add(pageNum);
    updatePageStatus(pageNum, 'processing');

    try {
        // Extract text from the specific page
        const page = await pdfDocument.getPage(pageNum);
        const textContent = await page.getTextContent();
        const pageText = textContent.items.map(item => item.str).join(' ');

        // Send to backend for processing
        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('file', uploadedFile);
        formData.append('pdf_texts', JSON.stringify([pageText]));
        formData.append('pdf_names', JSON.stringify([`page_${pageNum}_${uploadedFile.name}`]));
        formData.append('period', document.getElementById('periodSelect')?.value || '2025-06');
        formData.append('page_number', pageNum);

        const response = await fetch('bank_statement_processor.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            updatePageStatus(pageNum, 'completed');
            showPageResults(pageNum, result);
        } else {
            updatePageStatus(pageNum, 'error');
            alert(`Error processing page ${pageNum}: ${result.message}`);
        }

    } catch (error) {
        console.error(`Error processing page ${pageNum}:`, error);
        updatePageStatus(pageNum, 'error');
        alert(`Error processing page ${pageNum}: ${error.message}`);
    } finally {
        processingPages.delete(pageNum);
    }
}

function updatePageStatus(pageNum, status) {
    const statusElement = document.getElementById(`status-${pageNum}`);
    const buttonElement = document.getElementById(`btn-process-${pageNum}`);
    
    if (statusElement) {
        statusElement.className = `status-badge status-${status}`;
        statusElement.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    }
    
    if (buttonElement) {
        buttonElement.disabled = status === 'processing';
        if (status === 'completed') {
            buttonElement.innerHTML = '<i class="fas fa-check"></i> Completed';
            buttonElement.className = 'btn btn-success btn-process-page';
        } else if (status === 'error') {
            buttonElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
            buttonElement.className = 'btn btn-danger btn-process-page';
        }
    }
}

function showPageResults(pageNum, result) {
    // Create a modal or expand the card to show results
    const pageData = pageFiles.find(p => p.pageNum === pageNum);
    if (pageData && pageData.card) {
        const resultsDiv = document.createElement('div');
        resultsDiv.className = 'mt-3 p-3 bg-light rounded';
        resultsDiv.innerHTML = `
            <h6><i class="fas fa-chart-bar"></i> Processing Results</h6>
            <p><strong>Transactions found:</strong> ${result.data?.length || 0}</p>
            <p><strong>Matched:</strong> ${result.data?.filter(t => t.matched).length || 0}</p>
            <p><strong>Unmatched:</strong> ${result.data?.filter(t => !t.matched).length || 0}</p>
            <button class="btn btn-sm btn-outline-primary" onclick="viewDetailedResults(${pageNum})">
                View Details
            </button>
        `;
        
        pageData.card.appendChild(resultsDiv);
    }
}

function viewDetailedResults(pageNum) {
    // Open detailed results in a new window or modal
    window.open(`ai_bank_statement_upload.php?page=${pageNum}`, '_blank');
}

async function processAllPages() {
    const pendingPages = pageFiles.filter(p => p.status === 'pending');
    if (pendingPages.length === 0) {
        alert('No pending pages to process.');
        return;
    }

    showProgress();
    
    for (let i = 0; i < pendingPages.length; i++) {
        const pageData = pendingPages[i];
        updateProgress((i / pendingPages.length) * 100, `Processing page ${pageData.pageNum}...`);
        await processPage(pageData.pageNum);
    }
    
    updateProgress(100, 'All pages processed!');
    hideProgress();
}

async function processSelectedPages() {
    const selectedPages = pageFiles.filter(p => 
        document.getElementById(`select-${p.pageNum}`)?.checked && p.status === 'pending'
    );
    
    if (selectedPages.length === 0) {
        alert('No selected pages to process.');
        return;
    }

    showProgress();
    
    for (let i = 0; i < selectedPages.length; i++) {
        const pageData = selectedPages[i];
        updateProgress((i / selectedPages.length) * 100, `Processing page ${pageData.pageNum}...`);
        await processPage(pageData.pageNum);
    }
    
    updateProgress(100, 'Selected pages processed!');
    hideProgress();
}

function downloadPage(pageNum) {
    // Implementation for downloading individual page
    alert(`Download functionality for page ${pageNum} would be implemented here.`);
}

function downloadAllPages() {
    // Implementation for downloading all pages
    alert('Download all pages functionality would be implemented here.');
}

function resetSplitter() {
    if (confirm('Are you sure you want to reset? This will clear all uploaded data.')) {
        uploadedFile = null;
        pdfDocument = null;
        pageFiles = [];
        processingPages.clear();
        
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('bulkActions').style.display = 'none';
        document.getElementById('pagesGrid').innerHTML = '';
        hideUploadProgress();
        hideProgress();
    }
}

function showProgress() {
    document.getElementById('progressContainer').style.display = 'block';
}

function hideProgress() {
    setTimeout(() => {
        document.getElementById('progressContainer').style.display = 'none';
    }, 2000);
}

function updateProgress(percentage, text) {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    progressBar.style.width = percentage + '%';
    progressBar.textContent = Math.round(percentage) + '%';
    progressText.textContent = text;
}
</script>

<?php include 'footer.php'; ?> 