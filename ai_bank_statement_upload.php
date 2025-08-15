<?php
session_start();
require_once('Connections/cov.php');
require_once('config/EnvConfig.php');

// Check if user is logged in
if (!isset($_SESSION['FirstName'])) {
    header("Location: index.php");
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

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    // Debug script loading and load jQuery UI dynamically
    window.addEventListener('load', function() {
        console.log('=== SCRIPT LOADING DEBUG ===');
        console.log('jQuery loaded:', typeof $ !== 'undefined');
        if (typeof $ !== 'undefined') {
            console.log('jQuery version:', $.fn.jquery);

            // Load jQuery UI CSS and JS dynamically
            if (typeof $.fn.autocomplete === 'undefined') {
                console.log('jQuery UI not loaded, loading dynamically...');

                // Load CSS
                const cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.min.css';
                document.head.appendChild(cssLink);

                // Load JS
                const script = document.createElement('script');
                script.src = 'https://code.jquery.com/ui/1.13.2/jquery-ui.min.js';
                script.onload = function() {
                    console.log('jQuery UI loaded dynamically');
                    console.log('jQuery UI autocomplete available:', typeof $.fn.autocomplete !==
                        'undefined');
                };
                script.onerror = function() {
                    console.error('Failed to load jQuery UI dynamically');
                };
                document.head.appendChild(script);
            } else {
                console.log('jQuery UI already loaded');
                console.log('jQuery UI autocomplete available');
            }
        }
        console.log('=== END SCRIPT LOADING DEBUG ===');
    });
    </script>

    <style>
    /* Custom styles to complement Tailwind */
    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .upload-area {
        border: 2px dashed #cbd5e0;
        transition: all 0.3s ease;
    }

    .upload-area:hover {
        border-color: #667eea;
        background-color: #f7fafc;
    }

    /* Table width constraints */
    .compact-table {
        table-layout: fixed;
        width: 100%;
    }

    .compact-table th,
    .compact-table td {
        padding: 0.75rem 0.5rem;
    }

    /* Remove column width constraints for better readability */
    .transaction-table {
        min-width: 100%;
        white-space: nowrap;
    }

    .transaction-table th,
    .transaction-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    /* Ensure text wrapping works properly */
    .break-words {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Mobile responsive improvements */
    @media (max-width: 768px) {
        .transaction-table {
            max-width: 100%;
            font-size: 0.75rem;
        }

        .transaction-table th,
        .transaction-table td {
            padding: 0.5rem 0.25rem;
        }

        /* Stack columns vertically on mobile for better readability */
        .mobile-stack {
            display: block;
        }

        .mobile-stack td {
            display: block;
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem 0;
        }

        .mobile-stack td:before {
            content: attr(data-label) ": ";
            font-weight: 600;
            color: #6b7280;
        }
    }

    /* jQuery UI Autocomplete styling */
    .ui-autocomplete {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
        z-index: 9999 !important;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background: white;
    }

    .ui-autocomplete .ui-menu-item {
        padding: 0;
        border: none;
        background: none;
    }

    .ui-autocomplete .ui-menu-item .autocomplete-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
        transition: all 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
    }

    .ui-autocomplete .ui-menu-item:last-child .autocomplete-item {
        border-bottom: none;
    }

    .ui-autocomplete .ui-menu-item:hover .autocomplete-item,
    .ui-autocomplete .ui-menu-item.ui-state-focus .autocomplete-item {
        background: #f1f5f9;
        color: #1d4ed8;
    }

    .ui-autocomplete .ui-menu-item .autocomplete-item .employee-name {
        font-weight: 500;
        color: #374151;
    }

    .ui-autocomplete .ui-menu-item .autocomplete-item .employee-id {
        font-size: 0.85rem;
        color: #6b7280;
        background: #f3f4f6;
        padding: 2px 8px;
        border-radius: 12px;
    }

    .ui-autocomplete .ui-menu-item:hover .autocomplete-item .employee-name,
    .ui-autocomplete .ui-menu-item.ui-state-focus .autocomplete-item .employee-name {
        color: #1d4ed8;
    }

    /* Override jQuery UI default styles */
    .ui-autocomplete .ui-menu-item div {
        background: none !important;
        border: none !important;
        font-weight: normal !important;
    }

    /* Modal transitions */
    #manualMatchModal {
        transition: opacity 0.3s ease-in-out;
    }

    #manualMatchModal.hidden {
        opacity: 0;
        pointer-events: none;
    }

    #manualMatchModal:not(.hidden) {
        opacity: 1;
        pointer-events: auto;
    }

    /* Modal backdrop transition */
    #modalBackdrop {
        transition: opacity 0.3s ease-in-out;
    }
    </style>
</head>

<body class="bg-gray-100 min-h-screen font-sans">
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="container mx-auto px-4 py-8">
            <!-- Page Header -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-3xl font-bold text-blue-900 mb-2">
                            <i class="fas fa-upload mr-3"></i>Bank Statement Upload & Analysis
                        </h1>
                        <p class="text-gray-600 text-lg">AI-powered bank statement processing and transaction matching
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="test_bank_statement_system.php"
                            class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors duration-200"
                            target="_blank">
                            <i class="fas fa-cog mr-2"></i>System Test
                        </a>
                        <a href="bank_statement_history.php"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-history mr-2"></i>View History
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <div class="flex flex-wrap gap-3">
                    <a href="unmatched_transactions.php"
                        class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-search mr-2"></i>View Unmatched Transactions
                    </a>
                    <a href="manual_transaction_matches.php"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-check-circle mr-2"></i>View Matched Transactions
                    </a>
                    <a href="bank_statement_files.php"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-file-alt mr-2"></i>View Uploaded Files
                    </a>
                </div>
            </div>

            <!-- Mode Selection -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-blue-900 mb-4">
                    <i class="fas fa-toggle-on mr-2"></i>Processing Mode
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors duration-200">
                        <input class="form-radio text-blue-600 mr-3" type="radio" name="processingMode" id="uploadMode"
                            value="upload" checked>
                        <div>
                            <div class="font-medium text-gray-900">Upload New Bank Statement</div>
                            <div class="text-sm text-gray-500">Process new bank statement files</div>
                        </div>
                    </label>
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 transition-colors duration-200">
                        <input class="form-radio text-blue-600 mr-3" type="radio" name="processingMode"
                            id="existingMode" value="existing">
                        <div>
                            <div class="font-medium text-gray-900">Load Existing Analysis</div>
                            <div class="text-sm text-gray-500">Load previously analyzed data</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Existing Files Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8" id="existingFilesCard" style="display: none;">
                <h2 class="text-xl font-semibold text-blue-900 mb-4">
                    <i class="fas fa-database mr-2"></i>Select Existing Bank Statement
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label for="existingPeriodFilter" class="block text-sm font-medium text-gray-700 mb-2">Filter by
                            Period:</label>
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="existingPeriodFilter">
                            <option value="">All periods...</option>
                            <?php 
                            mysqli_data_seek($periods_result, 0); // Reset result pointer
                            while ($period = mysqli_fetch_assoc($periods_result)) { ?>
                            <option value="<?php echo $period['id']; ?>">
                                <?php echo $period['PayrollPeriod'] . ' (' . $period['PhysicalMonth'] . ' ' . $period['PhysicalYear'] . ')'; ?>
                            </option>
                            <?php } ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-1" id="periodSyncInfo">Period will auto-sync with upload
                            selection</p>
                    </div>
                    <div>
                        <label for="existingFileSelect" class="block text-sm font-medium text-gray-700 mb-2">Select Bank
                            Statement:</label>
                        <select
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="existingFileSelect" disabled>
                            <option value="">Loading existing files...</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        id="loadExistingBtn" disabled>
                        <i class="fas fa-download mr-2"></i>Load Analysis
                    </button>
                    <button type="button"
                        class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                        onclick="refreshExistingFiles()">
                        <i class="fas fa-refresh mr-2"></i>Refresh List
                    </button>
                </div>
                <div id="existingFileInfo" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg"
                    style="display: none;">
                    <div class="text-sm">
                        <strong class="text-blue-900">File Information:</strong>
                        <div id="existingFileDetails" class="mt-2 text-blue-800"></div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8" id="uploadCard">
                <h2 class="text-xl font-semibold text-blue-900 mb-4">
                    <i class="fas fa-file-upload mr-2"></i>Upload Bank Statements
                </h2>

                <form id="uploadForm" enctype="multipart/form-data">
                    <?php if (!$openai_configured): ?>
                    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-amber-600 mt-1 mr-3"></i>
                            <div>
                                <h3 class="font-medium text-amber-900">OpenAI API Key Not Configured</h3>
                                <p class="text-amber-800 mt-1">Please add your OpenAI API key to the <code
                                        class="bg-amber-100 px-1 py-0.5 rounded">config.env</code> file to use this
                                    feature.</p>
                                <a href="config_manager.php"
                                    class="inline-flex items-center mt-3 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                    <i class="fas fa-cog mr-2"></i>Configure API Key
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="grid md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Select
                                Period:</label>
                            <select
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                id="period" name="period" required <?php echo !$openai_configured ? 'disabled' : ''; ?>>
                                <?php mysqli_data_seek($periods_result, 0); ?>
                                <option value="">Select a period...</option>
                                <?php while ($period = mysqli_fetch_assoc($periods_result)) { ?>
                                <option value="<?php echo $period['id']; ?>">
                                    <?php echo $period['PayrollPeriod'] . ' (' . $period['PhysicalMonth'] . ' ' . $period['PhysicalYear'] . ')'; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">OpenAI API Status:</label>
                            <div class="px-4 py-2 bg-gray-100 rounded-lg">
                                <?php if ($openai_configured): ?>
                                <span class="text-green-700">
                                    <i class="fas fa-check-circle mr-2"></i>API Key Configured
                                </span>
                                <?php else: ?>
                                <span class="text-red-700">
                                    <i class="fas fa-times-circle mr-2"></i>API Key Not Configured
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="upload-area border-2 border-dashed border-blue-400 rounded-2xl p-12 text-center bg-blue-50 hover:bg-blue-100 transition-all duration-300 cursor-pointer"
                        id="uploadArea">
                        <i class="fas fa-cloud-upload-alt text-6xl text-blue-500 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Drag & Drop files here or click to browse
                        </h3>
                        <p class="text-gray-600 mb-6">Supported formats: PDF, Excel (.xlsx, .xls), Images (.jpg, .jpeg,
                            .png)</p>
                        <input type="file" id="fileInput" name="files[]" multiple
                            accept=".pdf,.xlsx,.xls,.jpg,.jpeg,.png" class="hidden">
                        <button type="button"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
                            onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-folder-open mr-2"></i>Browse Files
                        </button>
                    </div>

                    <!-- Password Input for Protected PDFs -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg" id="passwordSection"
                        style="display: none;">
                        <div class="flex items-start">
                            <i class="fas fa-lock text-blue-600 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <h3 class="font-medium text-blue-900 mb-2">Password-Protected PDF Detected</h3>
                                <p class="text-blue-800 mb-4">Some of your PDF files appear to be password-protected.
                                    Please provide the password to continue.</p>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="pdfPassword"
                                            class="block text-sm font-medium text-gray-700 mb-2">PDF Password:</label>
                                        <input type="password"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            id="pdfPassword" placeholder="Enter PDF password">
                                    </div>
                                    <div class="flex items-end">
                                        <button type="button"
                                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                                            onclick="clearPassword()">
                                            <i class="fas fa-times mr-2"></i>Clear Password
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="fileList" class="mt-6"></div>

                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="uploadBtn" <?php echo !$openai_configured ? 'disabled' : ''; ?>>
                            <i class="fas fa-upload mr-2"></i>Upload & Analyze
                        </button>
                        <button type="button"
                            class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                            onclick="clearFiles()">
                            <i class="fas fa-trash mr-2"></i>Clear Files
                        </button>
                        <button type="button"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
                            onclick="debugTableStructure()">
                            <i class="fas fa-bug mr-2"></i>Debug Table Structure
                        </button>
                    </div>
                </form>
            </div>

            <!-- Analysis Results -->
            <div class="bg-white rounded-xl shadow-lg p-6" id="analysisCard" style="display: none;">
                <h2 class="text-xl font-semibold text-blue-900 mb-4">
                    <i class="fas fa-chart-bar mr-2"></i>Analysis Results
                </h2>
                <div id="analysisResults"></div>
            </div>
        </div>
    </div>

    <!-- Manual Match Modal -->
    <div class="fixed inset-0 z-50 hidden" id="manualMatchModal" tabindex="-1">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" id="modalBackdrop"></div>

            <!-- Modal panel -->
            <div
                class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <!-- Modal header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-900">Manual Name Matching</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeManualMatchModal()">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="mb-6">
                    <div id="manualMatchContent"></div>
                </div>

                <!-- Modal footer -->
                <div class="flex justify-end space-x-3">
                    <button type="button"
                        class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors duration-200"
                        onclick="closeManualMatchModal()">
                        Cancel
                    </button>
                    <button type="button"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
                        id="saveManualMatch">
                        Save Match
                    </button>
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
    let uploadedFileInfo = []; // Store file information for later recording
    let passwordProtectedFiles = [];
    let currentMode = 'upload'; // Track current processing mode
    let existingFiles = []; // Store existing files list
    let currentFileInfo = null; // Store current file information

    // Mode switching functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize existing files list
        loadExistingFiles();

        // Initial period synchronization
        synchronizePeriods();

        // Mode switching event listeners
        document.getElementById('uploadMode').addEventListener('change', function() {
            if (this.checked) {
                switchToUploadMode();
            }
        });

        document.getElementById('existingMode').addEventListener('change', function() {
            if (this.checked) {
                switchToExistingMode();
            }
        });

        // Period synchronization - when upload period changes, update existing period filter if in existing mode
        document.getElementById('period').addEventListener('change', function() {
            if (currentMode === 'existing') {
                const existingPeriodFilter = document.getElementById('existingPeriodFilter');
                if (existingPeriodFilter) {
                    existingPeriodFilter.value = this.value;
                    console.log('Updated existing period filter to match upload period:', this.value);

                    // Show user feedback
                    showPeriodSyncNotification('Period filter updated to match upload selection');

                    // Reload existing files with new period
                    loadExistingFiles();
                }
            }
        });

        // Existing file selection event listeners
        document.getElementById('existingPeriodFilter').addEventListener('change', function() {
            // Optionally synchronize with upload period for consistency
            const uploadPeriod = document.getElementById('period');
            if (uploadPeriod && this.value !== uploadPeriod.value) {
                console.log('Period filter changed to:', this.value,
                    '- consider updating upload period for consistency');
            }
            loadExistingFiles();
        });

        document.getElementById('existingFileSelect').addEventListener('change', function() {
            const selectedFileId = this.value;
            if (selectedFileId) {
                showExistingFileInfo(selectedFileId);
                document.getElementById('loadExistingBtn').disabled = false;
            } else {
                hideExistingFileInfo();
                document.getElementById('loadExistingBtn').disabled = true;
            }
        });

        // Load existing analysis button
        document.getElementById('loadExistingBtn').addEventListener('click', function() {
            loadExistingAnalysis();
        });
    });

    function switchToUploadMode() {
        currentMode = 'upload';
        document.getElementById('uploadCard').style.display = 'block';
        document.getElementById('existingFilesCard').style.display = 'none';
        document.getElementById('analysisCard').style.display = 'none';
    }

    function synchronizePeriods() {
        // Synchronize periods between upload and existing files sections
        const uploadPeriod = document.getElementById('period').value;
        const existingPeriodFilter = document.getElementById('existingPeriodFilter');

        if (uploadPeriod && existingPeriodFilter && existingPeriodFilter.value !== uploadPeriod) {
            existingPeriodFilter.value = uploadPeriod;
            console.log('Initial period synchronization:', uploadPeriod);
        }

        // Update the info text
        updatePeriodSyncInfo();
    }

    function showPeriodSyncNotification(message) {
        // Create a temporary notification
        const notification = document.createElement('div');
        notification.className =
            'fixed top-5 right-5 z-50 min-w-80 p-4 bg-blue-50 border border-blue-200 rounded-lg shadow-lg';
        notification.innerHTML = `
            <div class="flex items-start">
                <i class="fas fa-sync-alt text-blue-600 mt-1 mr-3"></i>
                <div class="flex-1 text-blue-800">${message}</div>
                <button type="button" class="ml-auto text-blue-600 hover:text-blue-800" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    function updatePeriodSyncInfo() {
        const uploadPeriod = document.getElementById('period').value;
        const existingPeriodFilter = document.getElementById('existingPeriodFilter');
        const syncInfo = document.getElementById('periodSyncInfo');

        if (syncInfo) {
            if (uploadPeriod && existingPeriodFilter && existingPeriodFilter.value === uploadPeriod) {
                syncInfo.textContent = '✓ Period synchronized with upload selection';
                syncInfo.className = 'text-success';
            } else if (uploadPeriod) {
                syncInfo.textContent = 'Period will auto-sync with upload selection';
                syncInfo.className = 'text-muted';
            } else {
                syncInfo.textContent = 'Please select a period in upload section first';
                syncInfo.className = 'text-warning';
            }
        }
    }

    function switchToExistingMode() {
        currentMode = 'existing';
        document.getElementById('uploadCard').style.display = 'none';
        document.getElementById('existingFilesCard').style.display = 'block';

        // Synchronize the period filter with the upload period selection
        const uploadPeriod = document.getElementById('period').value;
        const existingPeriodFilter = document.getElementById('existingPeriodFilter');

        if (uploadPeriod && existingPeriodFilter) {
            existingPeriodFilter.value = uploadPeriod;
            console.log('Synchronized period filter to:', uploadPeriod);
        }

        // Update the sync info display
        updatePeriodSyncInfo();

        loadExistingFiles();
    }

    function loadExistingFiles() {
        const periodFilter = document.getElementById('existingPeriodFilter').value;
        const selectElement = document.getElementById('existingFileSelect');

        // Show loading state
        selectElement.innerHTML = '<option value="">Loading existing files...</option>';
        selectElement.disabled = true;

        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_existing_files',
                    period_filter: periodFilter
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    existingFiles = data.files;
                    populateExistingFilesDropdown();
                } else {
                    selectElement.innerHTML = '<option value="">Error loading files</option>';
                    console.error('Error loading existing files:', data.message);
                }
            })
            .catch(error => {
                selectElement.innerHTML = '<option value="">Error loading files</option>';
                console.error('Error loading existing files:', error);
            });
    }

    function populateExistingFilesDropdown() {
        const selectElement = document.getElementById('existingFileSelect');

        if (existingFiles.length === 0) {
            selectElement.innerHTML = '<option value="">No analyzed files found</option>';
            selectElement.disabled = true;
            return;
        }

        let html = '<option value="">Select a bank statement...</option>';
        existingFiles.forEach(file => {
            const periodInfo = file.PayrollPeriod ?
                `${file.PayrollPeriod} (${file.PhysicalMonth} ${file.PhysicalYear})` : 'Unknown Period';
            const uploadDate = new Date(file.upload_date).toLocaleDateString();
            html +=
                `<option value="${file.id}">${file.filename} - ${periodInfo} - ${uploadDate} (${file.total_transactions} transactions)</option>`;
        });

        selectElement.innerHTML = html;
        selectElement.disabled = false;
    }

    function showExistingFileInfo(fileId) {
        const file = existingFiles.find(f => f.id == fileId);
        if (!file) return;

        const uploadDate = new Date(file.upload_date).toLocaleDateString();
        const periodInfo = file.PayrollPeriod ? `${file.PayrollPeriod} (${file.PhysicalMonth} ${file.PhysicalYear})` :
            'Unknown Period';

        const infoHtml = `
            <strong>Filename:</strong> ${file.filename}<br>
            <strong>Period:</strong> ${periodInfo}<br>
            <strong>Upload Date:</strong> ${uploadDate}<br>
            <strong>Total Transactions:</strong> ${file.total_transactions}<br>
            <strong>Matched:</strong> ${file.matched_transactions}<br>
            <strong>Unmatched:</strong> ${file.unmatched_transactions}
        `;

        document.getElementById('existingFileDetails').innerHTML = infoHtml;
        document.getElementById('existingFileInfo').style.display = 'block';
        currentFileInfo = file;
    }

    function hideExistingFileInfo() {
        document.getElementById('existingFileInfo').style.display = 'none';
        currentFileInfo = null;
    }

    function refreshExistingFiles() {
        loadExistingFiles();
    }

    function loadExistingAnalysis() {
        const selectedFileId = document.getElementById('existingFileSelect').value;
        if (!selectedFileId) {
            alert('Please select a bank statement to load.');
            return;
        }

        const loadBtn = document.getElementById('loadExistingBtn');
        const originalText = loadBtn.innerHTML;
        loadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Loading...';
        loadBtn.disabled = true;

        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'load_existing_analysis',
                    file_id: selectedFileId
                })
            })
            .then(response => response.json())
            .then(data => {
                loadBtn.innerHTML = originalText;
                loadBtn.disabled = false;

                if (data.success) {
                    // Set the analysis data and display results
                    analysisData = data.data;
                    uploadedFileInfo = [data.file_info]; // Store file info for processing

                    // Show analysis results
                    displayAnalysisResults(analysisData);

                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4';
                    alertDiv.innerHTML = `
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <div class="font-medium">Successfully loaded analysis for "${data.file_info.filename}" with ${data.data.length} transactions.</div>
                            </div>
                            <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

                    // Insert the alert before the analysis results
                    const analysisResults = document.getElementById('analysisResults');
                    if (analysisResults && analysisResults.parentNode) {
                        analysisResults.parentNode.insertBefore(alertDiv, analysisResults);
                    }

                    // Auto-dismiss after 5 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 5000);

                } else {
                    alert('Error loading analysis: ' + data.message);
                }
            })
            .catch(error => {
                loadBtn.innerHTML = originalText;
                loadBtn.disabled = false;
                console.error('Error loading existing analysis:', error);
                alert('An error occurred while loading the analysis.');
            });
    }

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
                uploadedFiles.push(file);
                displayFile(file);

                // Check if PDF is password-protected
                if (file.type === 'application/pdf') {
                    try {
                        const passwordCheck = await checkPDFPassword(file);
                        console.log(`Password check for ${file.name}:`, passwordCheck);
                        if (passwordCheck.isProtected) {
                            if (!passwordProtectedFiles.includes(file.name)) {
                                passwordProtectedFiles.push(file.name);
                            }
                            console.log(`Password-protected PDF detected: ${file.name}`);
                        }
                    } catch (error) {
                        console.log(`Error checking PDF password for ${file.name}:`, error);
                        // If we can't determine, assume it might be protected
                        if (!passwordProtectedFiles.includes(file.name)) {
                            passwordProtectedFiles.push(file.name);
                        }
                    }
                }

                // Auto-process Excel files immediately
                if (file.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
                    file.type === 'application/vnd.ms-excel') {
                    console.log(`Auto-processing Excel file: ${file.name}`);
                    processExcelFile(file);
                }
            } else {
                alert(`Invalid file type: ${file.name}. Please upload PDF, Excel, or image files only.`);
            }
        }
        updatePasswordSection();
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
        fileItem.className =
            'bg-white border-2 border-gray-200 rounded-lg p-4 mb-3 flex justify-between items-center hover:border-blue-300 transition-all duration-200';
        const isProtected = passwordProtectedFiles.includes(file.name);
        fileItem.innerHTML = `
                <div class="flex-1">
                    <div class="font-medium text-gray-900">${file.name}</div>
                    <div class="text-sm text-gray-500">${formatFileSize(file.size)}</div>
                    ${isProtected ? '<span class="inline-flex items-center px-2 py-1 mt-1 text-xs font-medium bg-amber-100 text-amber-800 rounded-full"><i class="fas fa-lock mr-1"></i>Protected</span>' : ''}
                </div>
                <div class="flex gap-2">
                    <button type="button" class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200" onclick="testPasswordExtraction('${file.name}')" title="Test extraction">
                        <i class="fas fa-play"></i>
                    </button>
                    <button type="button" class="inline-flex items-center px-3 py-2 ${isProtected ? 'bg-green-600 hover:bg-green-700' : 'bg-amber-600 hover:bg-amber-700'} text-white text-sm font-medium rounded-lg transition-colors duration-200" onclick="togglePasswordProtected('${file.name}')" title="Toggle password protection">
                        <i class="fas ${isProtected ? 'fa-unlock' : 'fa-lock'}"></i>
                    </button>
                    <button type="button" class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200" onclick="removeFile('${file.name}')">
                        <i class="fas fa-trash"></i>
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
        passwordProtectedFiles = [];
        updateFileList();
        document.getElementById('fileInput').value = '';
        document.getElementById('passwordSection').style.display = 'none';
        document.getElementById('pdfPassword').value = '';
    }

    function clearPassword() {
        document.getElementById('pdfPassword').value = '';
        document.getElementById('passwordSection').style.display = 'none';
    }

    function validatePassword() {
        const password = document.getElementById('pdfPassword').value;
        const issues = [];

        if (password.length === 0) {
            issues.push('Password is empty');
        }

        if (password.startsWith(' ') || password.endsWith(' ')) {
            issues.push('Password has leading/trailing spaces');
        }

        if (password.length < 3) {
            issues.push('Password seems too short');
        }

        if (issues.length > 0) {
            console.log('Password validation issues:', issues);
            return false;
        }

        return true;
    }

    function togglePasswordProtected(fileName) {
        if (passwordProtectedFiles.includes(fileName)) {
            passwordProtectedFiles = passwordProtectedFiles.filter(name => name !== fileName);
            console.log(`Removed ${fileName} from password-protected list`);
        } else {
            passwordProtectedFiles.push(fileName);
            console.log(`Added ${fileName} to password-protected list`);
        }
        updatePasswordSection();
        updateFileList(); // Refresh the file list to show updated lock icons
    }

    async function testPasswordExtraction(fileName) {
        const file = uploadedFiles.find(f => f.name === fileName);
        if (!file) {
            console.error('File not found:', fileName);
            return;
        }

        const password = document.getElementById('pdfPassword').value;
        console.log('Testing password extraction for:', fileName);
        console.log('Password provided:', password ? 'Yes' : 'No');
        console.log('Password length:', password ? password.length : 0);

        // Validate password first
        if (password && !validatePassword()) {
            alert(
                '⚠️ Password validation failed!\n\nPlease check for:\n• Extra spaces at beginning/end\n• Correct case sensitivity\n• All required characters'
            );
            return;
        }

        try {
            if (password) {
                console.log('Attempting extraction with password...');
                const pages = await window.pdfTextExtractor.extractTextByPages(file, password);
                console.log('Success! Extracted pages:', pages.length);
                alert(
                    `✅ Successfully extracted ${pages.length} pages with password!\n\nFile: ${fileName}\nPages: ${pages.length}`
                );
            } else {
                console.log('Attempting extraction without password...');
                const pages = await window.pdfTextExtractor.extractTextByPages(file);
                console.log('Success! Extracted pages:', pages.length);
                alert(
                    `✅ Successfully extracted ${pages.length} pages without password!\n\nFile: ${fileName}\nPages: ${pages.length}`
                );
            }
        } catch (error) {
            console.error('Test extraction failed:', error);
            let errorMessage = error.message;

            if (error.name === 'PasswordException') {
                if (error.code === 2) {
                    errorMessage =
                        `❌ Incorrect password!\n\nFile: ${fileName}\n\nPlease check your password and try again.\n\nCommon issues:\n• Check for extra spaces\n• Verify case sensitivity\n• Ensure no special characters are missing`;
                } else if (error.code === 1) {
                    errorMessage =
                        `❌ Password required!\n\nFile: ${fileName}\n\nThis PDF is password-protected but no password was provided.`;
                }
            }

            alert(errorMessage);
        }
    }

    function updatePasswordSection() {
        const passwordSection = document.getElementById('passwordSection');
        if (!passwordSection) {
            console.warn('Password section not found in DOM');
            return;
        }

        if (passwordProtectedFiles.length > 0) {
            passwordSection.style.display = 'block';
            const alertDiv = passwordSection.querySelector('.alert');

            if (alertDiv) {
                alertDiv.innerHTML = `
                    <i class="fas fa-lock me-2"></i>
                    <strong>Password-Protected PDF Detected</strong>
                    <p class="mb-2 mt-2">The following PDF files appear to be password-protected: <strong>${passwordProtectedFiles.join(', ')}</strong></p>
                    <p class="mb-2">Please provide the password to continue processing.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="pdfPassword" class="form-label">PDF Password:</label>
                            <input type="password" class="form-control" id="pdfPassword" placeholder="Enter PDF password">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearPassword()">
                                    <i class="fas fa-times me-1"></i> Clear Password
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                console.warn('Alert div not found in password section');
            }
        } else {
            passwordSection.style.display = 'none';
        }
    }

    async function checkPDFPassword(file) {
        try {
            const arrayBuffer = await readFileAsArrayBuffer(file);
            const pdf = await window['pdfjs-dist/build/pdf'].getDocument({
                data: arrayBuffer
            }).promise;
            return {
                isProtected: false,
                error: null
            };
        } catch (error) {
            if (error.name === 'PasswordException' || error.message.includes('password')) {
                return {
                    isProtected: true,
                    error: error
                };
            }
            return {
                isProtected: false,
                error: error
            };
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

    // Check for duplicate files
    async function checkForDuplicateFiles() {
        const duplicateFiles = [];

        for (const file of uploadedFiles) {
            try {
                // Calculate file hash
                const arrayBuffer = await readFileAsArrayBuffer(file);
                const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const fileHash = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

                // Check if file exists
                const response = await fetch('bank_statement_processor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'check_file_exists',
                        file_hash: fileHash
                    })
                });

                const data = await response.json();
                if (data.success && data.exists) {
                    duplicateFiles.push(
                        `${file.name} (uploaded on ${new Date(data.file_info.upload_date).toLocaleDateString()})`
                    );
                }
            } catch (error) {
                console.error('Error checking file hash for', file.name, error);
            }
        }

        return {
            hasDuplicates: duplicateFiles.length > 0,
            duplicateFiles: duplicateFiles
        };
    }

    // Calculate file hash for a given file
    async function calculateFileHash(file) {
        const arrayBuffer = await readFileAsArrayBuffer(file);
        const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // Form submission
    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        if (uploadedFiles.length === 0) {
            alert('Please select at least one file to upload.');
            return;
        }

        // Check for duplicate files first
        const duplicateCheck = await checkForDuplicateFiles();
        if (duplicateCheck.hasDuplicates) {
            const proceed = confirm(
                `The following files have already been analyzed:\n\n${duplicateCheck.duplicateFiles.join('\n')}\n\nDo you want to continue and re-analyze these files? This will overwrite the existing analysis.`
            );
            if (!proceed) {
                return;
            }
        }

        // Check if password is required but not provided
        if (passwordProtectedFiles.length > 0) {
            const password = document.getElementById('pdfPassword').value;
            if (!password) {
                alert('Password is required for the following PDF files: ' + passwordProtectedFiles.join(
                    ', '));
                return;
            }
        }

        // Also check if any PDF files exist and we have a password - might be manually marked
        const hasPDFFiles = uploadedFiles.some(file => file.type === 'application/pdf');
        const password = document.getElementById('pdfPassword').value;
        if (hasPDFFiles && password && passwordProtectedFiles.length === 0) {
            // If we have PDF files and a password but no files marked as protected,
            // mark all PDF files as protected
            uploadedFiles.forEach(file => {
                if (file.type === 'application/pdf' && !passwordProtectedFiles.includes(file
                        .name)) {
                    passwordProtectedFiles.push(file.name);
                }
            });
            console.log('Auto-marked PDF files as password-protected due to password presence');
        }

        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Analyzing PDFs...';

        try {
            // Initialize results container
            analysisData = [];
            displayAnalysisResults([]);

            // Show processing status
            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.innerHTML =
                '<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Processing PDFs page by page...</div>';

            const analysisCard = document.getElementById('analysisCard');
            analysisCard.style.display = 'block';

            // Process each file
            for (let i = 0; i < uploadedFiles.length; i++) {
                const file = uploadedFiles[i];

                if (file.type === 'application/pdf') {
                    await processPDFByPages(file, i);
                } else {
                    // Handle non-PDF files normally
                    await processSingleFile(file, i);
                }
            }

            // Final display update
            displayAnalysisResults(analysisData);

            // Save extracted data to database for persistence
            await saveExtractedDataToDatabase();

            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Analyze';

        } catch (error) {
            console.error('Error during processing:', error);
            alert('Error processing files: ' + error.message);
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> Upload & Analyze';
        }
    });

    // Save extracted data to database for persistence
    async function saveExtractedDataToDatabase() {
        console.log('saveExtractedDataToDatabase called with:', {
            analysisData: analysisData,
            uploadedFileInfo: uploadedFileInfo
        });

        if (!analysisData || analysisData.length === 0 || !uploadedFileInfo || uploadedFileInfo.length === 0) {
            console.log('No data to save to database');
            return;
        }

        try {
            console.log('Saving extracted data to database...');

            // Prepare file info with hash and path
            const fileInfo = uploadedFileInfo[0]; // Assuming single file for now

            // If we have uploaded files, use the first one to get hash and path
            if (uploadedFiles.length > 0) {
                fileInfo.file_hash = await calculateFileHash(uploadedFiles[0]);
                fileInfo.file_path = `uploads/bank_statements/${uploadedFiles[0].name}`;
            } else {
                // For single file uploads, the file info should already have these
                if (!fileInfo.file_hash) {
                    console.warn('No file hash available, using timestamp as fallback');
                    fileInfo.file_hash = `hash_${Date.now()}`;
                }
                if (!fileInfo.file_path) {
                    console.warn('No file path available, using default path');
                    fileInfo.file_path = `uploads/bank_statements/${fileInfo.filename}`;
                }
            }

            const requestBody = {
                action: 'save_extracted_data',
                file_info: fileInfo,
                transactions: analysisData
            };

            console.log('Sending save request with body:', requestBody);

            const response = await fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestBody)
            });

            const data = await response.json();
            console.log('Save response received:', data);

            if (data.success) {
                console.log('Successfully saved extracted data to database');

                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4';
                alertDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-database text-green-600 mt-1 mr-3"></i>
                        <div class="flex-1">
                            <div class="font-medium">Analysis data has been saved to the database for future reference. You can now load this analysis later without re-processing.</div>
                        </div>
                        <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                const resultsDiv = document.getElementById('analysisResults');
                resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);

            } else {
                console.error('Failed to save extracted data:', data.message);
            }
        } catch (error) {
            console.error('Error saving extracted data:', error);
        }
    }

    async function extractTableDataFromPage(page, file) {
        try {
            console.log('Attempting table-based extraction for page:', page.pageNumber);

            // Check if we have items with positioning information
            if (page.items && page.items.length > 0) {
                console.log(`Using existing items array with ${page.items.length} items`);
                const transactions = detectTableStructure(page.items, page.pageNumber);

                if (transactions.length > 0) {
                    console.log(`Successfully extracted ${transactions.length} transactions using table detection`);
                    return transactions;
                } else {
                    console.log('No transactions found using table detection');
                    return null;
                }
            } else {
                console.log('No items array found, falling back to AI analysis');
                return null;
            }

        } catch (error) {
            console.error('Error in table extraction:', error);
            return null;
        }
    }

    function detectTableStructure(textItems, pageNumber) {
        console.log('Detecting table structure for page', pageNumber);

        // Sort items by Y position (top to bottom), then by X position (left to right)
        const sortedItems = textItems.sort((a, b) => {
            if (Math.abs(a.y - b.y) < 5) { // Same row if Y difference < 5px
                return a.x - b.x; // Sort by X position within row
            }
            return a.y - b.y; // Sort by Y position (top to bottom) - FIXED: was b.y - a.y
        });

        // Group items into rows
        const rows = [];
        let currentRow = [];
        let lastY = null;

        console.log('Grouping items into rows...');
        sortedItems.forEach(item => {
            if (lastY === null || Math.abs(item.y - lastY) < 5) {
                // Same row
                currentRow.push(item);
            } else {
                // New row
                if (currentRow.length > 0) {
                    rows.push(currentRow);
                }
                currentRow = [item];
            }
            lastY = item.y;
        });
        if (currentRow.length > 0) {
            rows.push(currentRow);
        }

        console.log(`Grouped into ${rows.length} rows`);
        rows.forEach((row, idx) => {
            console.log(`Row ${idx}: ${row.map(item => item.text).join(' | ')}`);
        });

        // Now we need to align rows with the header structure
        // First, find the header row to get the expected column count
        let headerRow = null;
        let expectedColumns = 0;

        for (let i = 0; i < rows.length; i++) {
            const rowText = rows[i].map(item => item.text.toLowerCase()).join(' ');
            if (rowText.includes('date') && rowText.includes('description') &&
                (rowText.includes('credit') || rowText.includes('debit'))) {
                headerRow = rows[i];
                expectedColumns = headerRow.length;
                console.log(`Header row found at index ${i} with ${expectedColumns} columns`);
                break;
            }
        }

        if (headerRow) {
            // Reconstruct rows to match header structure
            const alignedRows = [];
            rows.forEach((row, rowIndex) => {
                if (row === headerRow) {
                    alignedRows.push(row);
                    return;
                }

                // Create a new row with the same number of columns as header
                const alignedRow = new Array(expectedColumns).fill(null);

                // Map items to their correct positions based on X coordinates
                row.forEach(item => {
                    // Find the closest header column position
                    let closestColumn = 0;
                    let minDistance = Infinity;

                    headerRow.forEach((headerItem, colIndex) => {
                        const distance = Math.abs(item.x - headerItem.x);
                        if (distance < minDistance) {
                            minDistance = distance;
                            closestColumn = colIndex;
                        }
                    });

                    alignedRow[closestColumn] = item;
                });

                alignedRows.push(alignedRow);
                console.log(
                    `Aligned row ${rowIndex}: ${alignedRow.map(item => item ? item.text : '[EMPTY]').join(' | ')}`
                );
            });

            rows.length = 0; // Clear original rows
            rows.push(...alignedRows); // Replace with aligned rows
        }

        // Analyze rows to find column headers and data
        let columnHeaders = null;
        let dataRows = [];
        let creditColumnIndex = -1;
        let debitColumnIndex = -1;
        let dateColumnIndex = -1;
        let descriptionColumnIndex = -1;
        let balanceColumnIndex = -1;

        // Look for header row (usually contains words like "Date", "Description", "Credit", "Debit", "Balance")
        rows.forEach((row, rowIndex) => {
            const rowText = row.map(item => item ? item.text.toLowerCase() : '').join(' ');

            console.log(`Analyzing row ${rowIndex}: "${rowText}"`);

            if (rowText.includes('date') || rowText.includes('description') ||
                rowText.includes('credit') || rowText.includes('debit') ||
                rowText.includes('balance') || rowText.includes('amount') ||
                rowText.includes('narration') || rowText.includes('particulars') ||
                rowText.includes('withdrawal') || rowText.includes('deposit')) {

                columnHeaders = row;
                console.log('=== HEADER ROW DETECTED ===');
                console.log('Header row content:', row.map(item => item.text));
                console.log('Row items with positions:', row.map((item, idx) =>
                    `[${idx}]: "${item.text}" (x:${item.x}, y:${item.y})`));

                // Identify column positions with more precise matching
                row.forEach((item, colIndex) => {
                    const text = item.text.toLowerCase();
                    console.log(`  Analyzing header item [${colIndex}]: "${item.text}" (${text})`);

                    if (text === 'credit' || text === 'cr') {
                        creditColumnIndex = colIndex;
                        console.log(`✅ Credit column found at index ${colIndex}: "${item.text}"`);
                    } else if (text === 'debit' || text === 'dr') {
                        debitColumnIndex = colIndex;
                        console.log(`✅ Debit column found at index ${colIndex}: "${item.text}"`);
                    } else if (text === 'date') {
                        dateColumnIndex = colIndex;
                        console.log(`✅ Date column found at index ${colIndex}: "${item.text}"`);
                    } else if (text === 'description' || text === 'narration' || text ===
                        'particulars') {
                        descriptionColumnIndex = colIndex;
                        console.log(`✅ Description column found at index ${colIndex}: "${item.text}"`);
                    } else if (text === 'balance') {
                        balanceColumnIndex = colIndex;
                        console.log(`✅ Balance column found at index ${colIndex}: "${item.text}"`);
                    } else if (text.includes('value date')) {
                        // Don't override the main date column
                        console.log(
                            `ℹ️ Value date column found at index ${colIndex}: "${item.text}" (keeping main date column)`
                        );
                    } else {
                        console.log(`❌ Unrecognized header: "${item.text}"`);
                    }
                });

                console.log('=== FINAL COLUMN MAPPING ===');
                console.log('Column indices:', {
                    credit: creditColumnIndex,
                    debit: debitColumnIndex,
                    date: dateColumnIndex,
                    description: descriptionColumnIndex,
                    balance: balanceColumnIndex
                });

                if (creditColumnIndex === -1 && debitColumnIndex === -1) {
                    console.warn(
                        '⚠️ WARNING: No credit or debit columns detected! This will cause fallback to AI analysis.'
                    );
                }
            } else if (columnHeaders && row.length > 0) {
                // This is a data row - only add if it has the right structure
                if (row.length === columnHeaders.length) {
                    dataRows.push(row);
                } else {
                    console.log(
                        `Skipping row ${rowIndex}: expected ${columnHeaders.length} columns, got ${row.length}`
                    );
                }
            }
        });

        // Process data rows to extract transactions
        const transactions = [];
        console.log(`=== PROCESSING ${dataRows.length} DATA ROWS ===`);
        console.log('Column indices for processing:', {
            credit: creditColumnIndex,
            debit: debitColumnIndex,
            date: dateColumnIndex,
            description: descriptionColumnIndex,
            balance: balanceColumnIndex
        });

        dataRows.forEach((row, rowIndex) => {
            if (row.length < 2) {
                console.log(`Row ${rowIndex}: Skipping - insufficient data (${row.length} items)`);
                return;
            }

            console.log(`\n--- Processing Row ${rowIndex} ---`);
            console.log('Row content:', row.map((item, idx) => `[${idx}]: "${item ? item.text : '[EMPTY]'}"`));

            let transaction = {
                pageNumber: pageNumber,
                rowIndex: rowIndex,
                date: null,
                description: '',
                amount: 0,
                type: null,
                balance: null,
                uniqueId: `page_${pageNumber}_row_${rowIndex}_${Date.now()}`
            };

            // Extract data based on column positions
            if (dateColumnIndex >= 0 && row[dateColumnIndex]) {
                transaction.date = row[dateColumnIndex].text.trim();
                console.log(`  Date: "${transaction.date}"`);
            }

            if (descriptionColumnIndex >= 0 && row[descriptionColumnIndex]) {
                transaction.description = row[descriptionColumnIndex].text.trim();
                console.log(`  Description: "${transaction.description}"`);
            }

            if (balanceColumnIndex >= 0 && row[balanceColumnIndex]) {
                const balanceText = row[balanceColumnIndex].text.trim();
                transaction.balance = parseFloat(balanceText.replace(/[^\d.-]/g, ''));
                console.log(`  Balance: ${transaction.balance}`);
            }

            // Determine transaction type and amount based on credit/debit columns
            // In bank statements, a transaction typically has an amount in only ONE column
            let hasCreditAmount = false;
            let hasDebitAmount = false;

            if (creditColumnIndex >= 0 && row[creditColumnIndex]) {
                const creditText = row[creditColumnIndex].text.trim();
                const creditAmount = parseFloat(creditText.replace(/[^\d.-]/g, ''));
                console.log(`  Credit column [${creditColumnIndex}]: "${creditText}" → ${creditAmount}`);
                if (creditAmount > 0) {
                    hasCreditAmount = true;
                    transaction.amount = creditAmount;
                    transaction.type = 'credit';
                    console.log(`    ✅ Credit transaction: ${creditAmount}`);
                }
            }

            if (debitColumnIndex >= 0 && row[debitColumnIndex]) {
                const debitText = row[debitColumnIndex].text.trim();
                const debitAmount = parseFloat(debitText.replace(/[^\d.-]/g, ''));
                console.log(`  Debit column [${debitColumnIndex}]: "${debitText}" → ${debitAmount}`);
                if (debitAmount > 0) {
                    hasDebitAmount = true;
                    transaction.amount = debitAmount;
                    transaction.type = 'debit';
                    console.log(`    ✅ Debit transaction: ${debitAmount}`);
                }
            }

            // Validate: a transaction should have amount in exactly one column
            if (hasCreditAmount && hasDebitAmount) {
                console.warn(
                    `⚠️ Row ${rowIndex}: Transaction has amounts in both credit and debit columns - skipping`
                );
                return;
            }

            // If we have a valid transaction with amount in exactly one column, add it
            if (transaction.amount > 0 && transaction.type && (hasCreditAmount !== hasDebitAmount)) {
                console.log(
                    `✅ Row ${rowIndex}: ${transaction.type.toUpperCase()} transaction - Amount: ${transaction.amount}, Column: ${transaction.type === 'credit' ? 'Credit' : 'Debit'}`
                );
                transactions.push(transaction);
            } else {
                console.log(
                    `❌ Row ${rowIndex}: Invalid transaction - Amount: ${transaction.amount}, Type: ${transaction.type}, HasCredit: ${hasCreditAmount}, HasDebit: ${hasDebitAmount}`
                );
            }
        });

        console.log(`Extracted ${transactions.length} transactions from page ${pageNumber}`);

        // Validate extracted transactions
        if (transactions.length > 0) {
            console.log('Transaction validation:');
            transactions.forEach((txn, idx) => {
                console.log(
                    `  ${idx + 1}. ${txn.type.toUpperCase()}: ${txn.amount} - ${txn.description || 'No description'}`
                );
            });
        }

        return transactions;
    }

    async function processPDFByPages(file, fileIndex) {
        try {
            console.log('Processing PDF by pages:', file.name);

            // Extract pages from PDF
            console.log('Starting PDF text extraction for:', file.name);
            console.log('PDF text extractor available:', typeof window.pdfTextExtractor);

            if (!window.pdfTextExtractor) {
                throw new Error('PDF text extractor not loaded');
            }

            // Check if this file needs a password
            const password = document.getElementById('pdfPassword').value;
            let pages;

            console.log('Password protected files:', passwordProtectedFiles);
            console.log('Current file:', file.name);
            console.log('Password provided:', password ? 'Yes' : 'No');
            console.log('File is in protected list:', passwordProtectedFiles.includes(file.name));

            if (passwordProtectedFiles.includes(file.name) && password) {
                console.log('Using password for PDF extraction');
                pages = await window.pdfTextExtractor.extractTextByPages(file, password);
            } else if (passwordProtectedFiles.includes(file.name) && !password) {
                throw new Error(`Password is required for ${file.name} but none was provided`);
            } else {
                console.log('Processing without password');
                try {
                    pages = await window.pdfTextExtractor.extractTextByPages(file);
                } catch (error) {
                    console.log('Initial extraction failed:', error);
                    // If extraction fails and we have a password, try with password
                    if (password && (error.message.includes('password') || error.message.includes('Password') ||
                            error.name === 'PasswordException')) {
                        console.log('Extraction failed with password error, trying with password');
                        pages = await window.pdfTextExtractor.extractTextByPages(file, password);
                    } else if (password) {
                        // If we have a password but the error doesn't mention password, still try with password
                        console.log('Trying with password as fallback');
                        try {
                            pages = await window.pdfTextExtractor.extractTextByPages(file, password);
                        } catch (passwordError) {
                            console.log('Password extraction also failed:', passwordError);
                            throw error; // Throw the original error
                        }
                    } else {
                        throw error;
                    }
                }
            }

            console.log(`Extracted ${pages.length} pages from ${file.name}`);
            console.log('Pages data:', pages);

            // Update status with file info
            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.innerHTML = `
                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
                    <div class="flex items-start">
                        <i class="fas fa-file-pdf text-blue-600 mt-1 mr-3"></i>
                        <div>
                            <div class="font-medium text-blue-900">Processing: ${file.name}</div>
                            <div class="text-sm text-blue-700 mt-1">Found ${pages.length} pages - Processing page by page...</div>
                        </div>
                    </div>
                </div>
                <div id="pageProgress"></div>
                <div id="currentResults"></div>
            `;

            // Ensure elements exist
            let pdfPageProgressDiv = document.getElementById('pageProgress');
            let pdfCurrentResultsDiv = document.getElementById('currentResults');

            let totalTransactions = 0;

            // Process each page
            for (let pageIndex = 0; pageIndex < pages.length; pageIndex++) {
                const page = pages[pageIndex];

                // Update page progress
                pdfPageProgressDiv = document.getElementById('pageProgress');
                if (pdfPageProgressDiv) {
                    pdfPageProgressDiv.innerHTML = `
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg mb-4">
                            <div class="flex items-start">
                                <i class="fas fa-spinner fa-spin text-amber-600 mt-1 mr-3"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-amber-900">Page ${page.pageNumber} of ${pages.length}</div>
                                    <div class="text-sm text-amber-700 mt-1">Processing page ${page.pageNumber}... (${pageIndex + 1}/${pages.length})</div>
                                    <div class="mt-3 bg-amber-200 rounded-full h-2">
                                        <div class="bg-amber-600 h-2 rounded-full transition-all duration-300" style="width: ${((pageIndex + 1) / pages.length) * 100}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Create form data for this page
                const formData = new FormData();
                formData.append('action', 'upload'); // Add the action parameter
                formData.append('period', document.getElementById('period').value);
                formData.append('files[]', file);
                formData.append('extracted_texts[]', page.text);
                formData.append('page_info', JSON.stringify({
                    fileName: page.fileName,
                    pageNumber: page.pageNumber,
                    totalPages: pages.length,
                    originalFile: file.name
                }));

                // Add password if provided
                const password = document.getElementById('pdfPassword').value;
                if (password) {
                    formData.append('pdf_password', password);
                }

                // Process this page
                console.log(`Processing page ${page.pageNumber} with ${page.text.length} characters of text`);

                // Try table-based extraction first
                let pageData = null;
                try {
                    pageData = await extractTableDataFromPage(page, file);
                    if (pageData && pageData.length > 0) {
                        console.log(`Table extraction successful: ${pageData.length} transactions found`);
                    } else {
                        console.log('Table extraction failed or no data found, falling back to AI analysis');
                        pageData = await uploadAndAnalyzePage(formData);
                    }
                } catch (error) {
                    console.log('Table extraction error, falling back to AI analysis:', error);
                    pageData = await uploadAndAnalyzePage(formData);
                }

                console.log(`Page ${page.pageNumber} returned ${pageData ? pageData.length : 0} transactions`);

                // Add page data to results
                if (pageData && pageData.length > 0) {
                    // Add unique identifiers to new transactions
                    const pageDataWithIds = pageData.map((transaction, index) => ({
                        ...transaction,
                        uniqueId: `tx_${Date.now()}_${analysisData.length + index}`
                    }));
                    analysisData = analysisData.concat(pageDataWithIds);
                    totalTransactions += pageData.length;

                    // Update current results display
                    pdfCurrentResultsDiv = document.getElementById('currentResults');
                    if (pdfCurrentResultsDiv) {
                        pdfCurrentResultsDiv.innerHTML = `
                            <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                                <div class="flex items-start">
                                    <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-green-900">Page ${page.pageNumber} Complete!</div>
                                        <div class="text-sm text-green-700 mt-1">Found ${pageData.length} transactions on this page. Total so far: ${totalTransactions} transactions</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // Update full display with current results
                    displayAnalysisResults(analysisData);
                } else {
                    // Update for pages with no transactions
                    pdfCurrentResultsDiv = document.getElementById('currentResults');
                    if (pdfCurrentResultsDiv) {
                        pdfCurrentResultsDiv.innerHTML = `
                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Page ${page.pageNumber} Complete!</strong><br>
                                <small>No transactions found on this page. Total so far: ${totalTransactions} transactions</small>
                            </div>
                        `;
                    }
                }

                // Small delay to prevent overwhelming the server
                await new Promise(resolve => setTimeout(resolve, 500));
            }

            // Final completion message
            pdfPageProgressDiv = document.getElementById('pageProgress');
            if (pdfPageProgressDiv) {
                pdfPageProgressDiv.innerHTML = `
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                            <div>
                                <div class="font-medium text-green-900">All Pages Complete!</div>
                                <div class="text-sm text-green-700 mt-1">Successfully processed all ${pages.length} pages of ${file.name}. Total transactions found: ${totalTransactions}</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            console.log(`Completed processing ${file.name} - ${totalTransactions} total transactions`);

            // Final display update
            displayAnalysisResults(analysisData);

            // Prepare file info for the main save operation
            if (analysisData.length > 0) {
                // Create a file info object based on the processed file
                const fileInfo = {
                    filename: file.name,
                    file_hash: await calculateFileHash(file),
                    file_path: `uploads/bank_statements/${file.name}`,
                    period_id: parseInt(document.getElementById('period').value),
                    uploaded_by: 1 // Default user ID
                };

                // Store this for the main save function (don't call save here)
                uploadedFileInfo = [fileInfo];

                console.log('Prepared file info for main save operation:', fileInfo);
            }

        } catch (error) {
            console.error('Error processing PDF by pages:', file.name, error);
            throw error;
        }
    }

    async function processSingleFile(file, fileIndex) {
        try {
            console.log('Processing single file:', file.name);

            // Update status
            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.innerHTML =
                `<div class="alert alert-info"><i class="fas fa-spinner fa-spin me-2"></i>Processing ${file.name}...</div>`;

            // Create form data for this file
            const formData = new FormData();
            formData.append('action', 'upload'); // Add the action parameter
            formData.append('period', document.getElementById('period').value);
            formData.append('files[]', file);
            formData.append('extracted_texts[]', ''); // Non-PDF files don't need extracted text

            // Add password if provided
            const password = document.getElementById('pdfPassword').value;
            if (password) {
                formData.append('pdf_password', password);
            }

            // Process this file
            const fileData = await uploadAndAnalyzePage(formData);

            // Add file data to results
            if (fileData && fileData.length > 0) {
                // Add unique identifiers to new transactions
                const fileDataWithIds = fileData.map((transaction, index) => ({
                    ...transaction,
                    uniqueId: `tx_${Date.now()}_${analysisData.length + index}`
                }));
                analysisData = analysisData.concat(fileDataWithIds);

                // Update display with current results
                displayAnalysisResults(analysisData);

                // Prepare file info for the main save operation
                if (fileData && fileData.length > 0) {
                    // Create a file info object based on the processed file
                    const fileInfo = {
                        filename: file.name,
                        file_hash: await calculateFileHash(file),
                        file_path: `uploads/bank_statements/${file.name}`,
                        period_id: parseInt(document.getElementById('period').value),
                        uploaded_by: 1 // Default user ID
                    };

                    // Store this for the main save function (don't call save here)
                    uploadedFileInfo = [fileInfo];

                    console.log('Prepared file info for main save operation:', fileInfo);
                }
            }

        } catch (error) {
            console.error('Error processing single file:', file.name, error);
            throw error;
        }
    }

    async function uploadAndAnalyzePage(formData) {
        return new Promise((resolve, reject) => {
            console.log('Sending formData to server...');

            // Log form data contents for debugging
            for (let [key, value] of formData.entries()) {
                if (key === 'extracted_texts[]') {
                    console.log(`${key}: ${value.substring(0, 200)}... (${value.length} chars)`);
                } else {
                    console.log(`${key}: ${value}`);
                }
            }

            fetch('bank_statement_processor.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Server response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Server response data:', data);
                    if (data.success) {
                        console.log('Resolving with data:', data.data || []);
                        resolve(data.data || []);
                    } else {
                        console.error('Server error:', data.message);
                        resolve([]); // Continue processing other pages even if one fails
                    }
                })
                .catch(error => {
                    console.error('Network error:', error);
                    resolve([]); // Continue processing other pages even if one fails
                });
        });
    }

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
                    // Store file information for later recording
                    if (data.file_info) {
                        // Ensure uploadedFileInfo is always an array
                        if (Array.isArray(data.file_info)) {
                            uploadedFileInfo = data.file_info;
                        } else {
                            uploadedFileInfo = [data.file_info];
                        }
                        console.log('Stored file info for later recording:', uploadedFileInfo);
                    }

                    // Add unique identifiers to each transaction
                    analysisData = data.data.map((transaction, index) => ({
                        ...transaction,
                        uniqueId: `tx_${Date.now()}_${index}` // Add unique identifier
                    }));
                    displayAnalysisResults(analysisData);

                    // Save extracted data to database for persistence
                    saveExtractedDataToDatabase();
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
        console.log('displayAnalysisResults called with:', data);

        const resultsDiv = document.getElementById('analysisResults');
        const analysisCard = document.getElementById('analysisCard');

        if (!Array.isArray(data) || data.length === 0) {
            resultsDiv.innerHTML =
                '<div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i>No transactions found in the uploaded file.</div>';
            analysisCard.style.display = 'block';
            return;
        }

        // Count matched and unmatched transactions
        const matchedTransactions = data.filter(t => t.matched);
        const unmatchedTransactions = data.filter(t => !t.matched);
        const processedTransactions = data.filter(t => t.processed);
        const pendingTransactions = data.filter(t => !t.processed);

        console.log('Filtered transactions:', {
            total: data.length,
            matched: matchedTransactions.length,
            unmatched: unmatchedTransactions.length,
            processed: processedTransactions.length,
            pending: pendingTransactions.length
        });

        // Log a few examples of matched and unmatched transactions
        console.log('Sample matched transactions:', matchedTransactions.slice(0, 2));
        console.log('Sample unmatched transactions:', unmatchedTransactions.slice(0, 2));

        // Preserve progress display if it exists
        let pageProgressDiv = document.getElementById('pageProgress');
        let currentResultsDiv = document.getElementById('currentResults');

        let html = '<div class="space-y-6">';

        // Summary with page information
        html += '<div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">';
        html += '<div class="text-blue-900">';
        html +=
            `<div class="font-semibold mb-2">Analysis Summary: ${data.length} transactions found, ${matchedTransactions.length} matched, ${unmatchedTransactions.length} unmatched</div>`;
        html +=
            `<div class="mb-2">Processing Status: ${processedTransactions.length} processed, ${pendingTransactions.length} pending</div>`;
        html +=
            `<div class="text-sm text-blue-700 mb-2">Note: Each transaction has a unique ID (shown in parentheses) to help identify specific transactions when there are duplicates.</div>`;
        html +=
            `<div class="text-sm text-blue-700">Processed transactions are highlighted in green and cannot be modified. Only pending transactions can be processed.</div>`;

        // Add page information if available
        const pageInfo = data.filter(t => t.page_info).map(t => t.page_info);
        if (pageInfo.length > 0) {
            const uniquePages = [...new Set(pageInfo.map(p => p.pageNumber))].sort((a, b) => a - b);
            html += `<div class="text-sm text-blue-700 mt-2">Pages processed: ${uniquePages.join(', ')}</div>`;
        }

        html += '</div>';
        html += '</div>';

        // Matched transactions
        if (matchedTransactions.length > 0) {
            html += '<div class="bg-white border border-gray-200 rounded-lg p-6">';
            html +=
                '<h6 class="text-lg font-semibold text-green-700 mb-4"><i class="fas fa-check-circle mr-2"></i> Matched Transactions</h6>';
            html += '<div class="overflow-x-auto">';
            html += '<table class="min-w-full divide-y divide-gray-200 transaction-table">';
            html += '<thead class="bg-gray-50"><tr>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><input type="checkbox" id="checkAllMatched" onchange="toggleAllMatched(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"></th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member ID</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matched Name</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
            html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

            matchedTransactions.forEach((transaction, index) => {
                const pageNumber = transaction.page_info ? transaction.page_info.pageNumber : 'N/A';
                const isProcessed = transaction.processed || false;
                const processedBadge = isProcessed ?
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2"><i class="fas fa-check mr-1"></i>Processed</span>' :
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 ml-2"><i class="fas fa-clock mr-1"></i>Pending</span>';
                const processedToTable = transaction.processed_to_table ?
                    ` (${transaction.processed_to_table})` : '';

                html += `<tr class="${isProcessed ? 'bg-green-50' : 'hover:bg-gray-50'}">`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap"><input type="checkbox" class="matched-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="${index}" ${isProcessed ? 'disabled' : 'checked'}></td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${pageNumber}</span></td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.date || 'N/A'}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap">`;
                html += `<div class="text-sm font-medium text-gray-900">${transaction.name}</div>`;
                html +=
                    `<div class="text-sm text-gray-500">(${transaction.uniqueId ? transaction.uniqueId.split('_').pop() : 'N/A'})</div>`;
                html += `${processedBadge}${processedToTable}`;
                html += `</td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.member_id || 'N/A'}</td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.member_name || 'N/A'}</td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm ${transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'}">`;
                html +=
                    `${transaction.type === 'credit' ? '+' : '-'}₦${parseFloat(transaction.amount).toLocaleString()}`;
                html += `</td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${transaction.type}</span></td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.description || 'N/A'}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">`;
                html += `<div class="flex flex-col space-y-2">`;
                html +=
                    `<button class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors duration-200 ${isProcessed ? 'opacity-50 cursor-not-allowed' : ''}" onclick="reclassifyTransaction(${index}, 'matched')" title="Reclassify transaction type" ${isProcessed ? 'disabled' : ''}>`;
                html += `<i class="fas fa-exchange-alt mr-1"></i> Reclassify`;
                html += `</button>`;
                html +=
                    `<button class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded transition-colors duration-200 ${isProcessed ? 'opacity-50 cursor-not-allowed' : ''}" onclick="openManualMatchModal('${transaction.name}', ${transaction.amount}, '${transaction.type}', '${transaction.uniqueId}')" title="Manual match this transaction" ${isProcessed ? 'disabled' : ''}>`;
                html += `<i class="fas fa-user-edit mr-1"></i> Manual Match`;
                html += `</button>`;
                html += `</div>`;
                html += `</td>`;
                html += `</tr>`;
            });

            html += '</tbody></table></div></div>';
        }

        // Unmatched transactions
        if (unmatchedTransactions.length > 0) {
            html += '<div class="bg-white border border-gray-200 rounded-lg p-6">';
            html +=
                '<h6 class="text-lg font-semibold text-amber-700 mb-4"><i class="fas fa-exclamation-triangle mr-2"></i> Unmatched Transactions</h6>';
            html += '<div class="overflow-x-auto">';
            html += '<table class="min-w-full divide-y divide-gray-200 transaction-table">';
            html += '<thead class="bg-gray-50"><tr>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><input type="checkbox" id="checkAllUnmatched" onchange="toggleAllUnmatched(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"></th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>';
            html +=
                '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
            html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';

            unmatchedTransactions.forEach((transaction, index) => {
                const pageNumber = transaction.page_info ? transaction.page_info.pageNumber : 'N/A';
                html += `<tr class="hover:bg-gray-50">`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap"><input type="checkbox" class="unmatched-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="${index}"></td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">${pageNumber}</span></td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.date || 'N/A'}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap">`;
                html += `<div class="text-sm font-medium text-gray-900">${transaction.name}</div>`;
                html +=
                    `<div class="text-sm text-gray-500">(${transaction.uniqueId ? transaction.uniqueId.split('_').pop() : 'N/A'})</div>`;
                html += `</td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm ${transaction.type === 'credit' ? 'text-green-600' : 'text-red-600'}">`;
                html +=
                    `${transaction.type === 'credit' ? '+' : '-'}₦${parseFloat(transaction.amount).toLocaleString()}`;
                html += `</td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${transaction.type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">${transaction.type}</span></td>`;
                html +=
                    `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${transaction.description || 'N/A'}</td>`;
                html += `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">`;
                html += `<div class="flex flex-col space-y-2">`;
                html +=
                    `<button class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors duration-200" onclick="reclassifyTransaction(${index}, 'unmatched')" title="Reclassify transaction type">`;
                html += `<i class="fas fa-exchange-alt mr-1"></i> Reclassify`;
                html += `</button>`;
                html +=
                    `<button class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded transition-colors duration-200" onclick="openManualMatchModal('${transaction.name}', ${transaction.amount}, '${transaction.type}', '${transaction.uniqueId}')">`;
                html += `<i class="fas fa-user-edit mr-1"></i> Manual Match`;
                html += `</button>`;
                html += `</div>`;
                html += `</td>`;
                html += `</tr>`;
            });

            html += '</tbody></table></div></div>';
        }

        html += '</div>';

        // Add bulk reclassification controls
        if (data.length > 0) {
            html += '<div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">';
            html +=
                '<h6 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-cogs mr-2"></i> Bulk Actions</h6>';
            html += '<div class="grid md:grid-cols-2 gap-4 mb-6">';
            html +=
                '<button class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-medium rounded-lg transition-all duration-200" onclick="bulkReclassifyTransactions(\'credit\')">';
            html += '<i class="fas fa-exchange-alt mr-2"></i> Reclassify All as Credit';
            html += '</button>';
            html +=
                '<button class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white font-medium rounded-lg transition-all duration-200" onclick="bulkReclassifyTransactions(\'debit\')">';
            html += '<i class="fas fa-exchange-alt mr-2"></i> Reclassify All as Debit';
            html += '</button>';
            html += '</div>';
            html += '<div class="grid md:grid-cols-2 gap-4">';
            html +=
                '<button class="inline-flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200" onclick="processAllTransactions()">';
            html += '<i class="fas fa-save mr-2"></i> Process All Transactions';
            html += '</button>';
            html +=
                '<button class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200" onclick="processSelectedTransactions()">';
            html += '<i class="fas fa-check-square mr-2"></i> Process Selected Only';
            html += '</button>';
            html += '</div>';
            html +=
                '<div class="mt-4 text-sm text-gray-600 text-center">Matched transactions go directly to database, unmatched go to unmatched list</div>';
            html += '</div>';
        }

        resultsDiv.innerHTML = html;
        analysisCard.style.display = 'block';
    }

    function reclassifyTransaction(index, section) {
        const newType = section === 'matched' ?
            (analysisData.filter(t => t.matched)[index].type === 'credit' ? 'debit' : 'credit') :
            (analysisData.filter(t => !t.matched)[index].type === 'credit' ? 'debit' : 'credit');

        if (confirm(
                `Are you sure you want to reclassify this transaction from ${section === 'matched' ? analysisData.filter(t => t.matched)[index].type : analysisData.filter(t => !t.matched)[index].type} to ${newType}?`
            )) {

            // Get the transaction details
            const transaction = section === 'matched' ?
                analysisData.filter(t => t.matched)[index] :
                analysisData.filter(t => !t.matched)[index];

            const oldType = transaction.type;

            // Update the transaction type in frontend
            if (section === 'matched') {
                analysisData.filter(t => t.matched)[index].type = newType;
            } else {
                analysisData.filter(t => !t.matched)[index].type = newType;
            }

            // Save to database
            saveReclassificationToDatabase(transaction, oldType, newType);

            // Refresh the display
            displayAnalysisResults(analysisData);

            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4';
            alertDiv.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                    <div class="flex-1">
                        <div class="font-medium">Transaction reclassified successfully from ${oldType} to ${newType}</div>
                    </div>
                    <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
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

    function saveReclassificationToDatabase(transaction, oldType, newType) {
        // Save reclassification to database
        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'save_reclassification',
                    transaction_name: transaction.name,
                    transaction_amount: transaction.amount,
                    old_type: oldType,
                    new_type: newType,
                    unique_id: transaction.uniqueId || null,
                    matched: transaction.matched || false
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Reclassification saved to database successfully');
                } else {
                    console.error('Failed to save reclassification to database:', data.message);
                    // Revert the change if database save failed
                    if (transaction.matched) {
                        const matchedIndex = analysisData.findIndex(t => t.matched && t.uniqueId === transaction
                            .uniqueId);
                        if (matchedIndex !== -1) {
                            analysisData[matchedIndex].type = oldType;
                        }
                    } else {
                        const unmatchedIndex = analysisData.findIndex(t => !t.matched && t.uniqueId === transaction
                            .uniqueId);
                        if (unmatchedIndex !== -1) {
                            analysisData[unmatchedIndex].type = oldType;
                        }
                    }
                    displayAnalysisResults(analysisData);
                    alert('Failed to save reclassification to database. Changes have been reverted.');
                }
            })
            .catch(error => {
                console.error('Error saving reclassification to database:', error);
                // Revert the change if database save failed
                if (transaction.matched) {
                    const matchedIndex = analysisData.findIndex(t => t.matched && t.uniqueId === transaction
                        .uniqueId);
                    if (matchedIndex !== -1) {
                        analysisData[matchedIndex].type = oldType;
                    }
                } else {
                    const unmatchedIndex = analysisData.findIndex(t => !t.matched && t.uniqueId === transaction
                        .uniqueId);
                    if (unmatchedIndex !== -1) {
                        analysisData[unmatchedIndex].type = oldType;
                    }
                }
                displayAnalysisResults(analysisData);
                alert('Error saving reclassification to database. Changes have been reverted.');
            });
    }

    function bulkReclassifyTransactions(newType) {
        const count = analysisData.length;
        if (confirm(`Are you sure you want to reclassify all ${count} transactions as ${newType}?`)) {

            // Save all reclassifications to database
            const promises = analysisData.map(transaction => {
                const oldType = transaction.type;
                transaction.type = newType;
                return saveReclassificationToDatabase(transaction, oldType, newType);
            });

            // Wait for all database saves to complete
            Promise.all(promises).then(() => {
                // Refresh the display
                displayAnalysisResults(analysisData);

                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4';
                alertDiv.innerHTML = `
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                        <div class="flex-1">
                            <div class="font-medium">All ${count} transactions have been reclassified as ${newType}</div>
                        </div>
                        <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                const resultsDiv = document.getElementById('analysisResults');
                resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);

                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 3000);
            }).catch(error => {
                console.error('Error saving bulk reclassifications:', error);
                alert(
                    'Some reclassifications may not have been saved to the database. Please check the console for details.'
                );
            });
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
        const selectedMatched = Array.from(document.querySelectorAll('.matched-checkbox:checked')).map(cb => parseInt(cb
            .value));
        const selectedUnmatched = Array.from(document.querySelectorAll('.unmatched-checkbox:checked')).map(cb =>
            parseInt(cb.value));

        const matchedTransactions = analysisData.filter(t => t.matched);
        const unmatchedTransactions = analysisData.filter(t => !t.matched);

        const selectedTransactions = [
            ...selectedMatched.map(index => matchedTransactions[index]),
            ...selectedUnmatched.map(index => unmatchedTransactions[index])
        ].filter(t => t && !t.processed); // Remove any undefined entries and already processed transactions

        if (selectedTransactions.length === 0) {
            alert(
                'Please select at least one pending transaction to process. Processed transactions cannot be reprocessed.'
            );
            return;
        }

        if (confirm(
                `Are you sure you want to process ${selectedTransactions.length} selected transactions? This action cannot be undone.`
            )) {
            // Show loading state
            const processBtn = document.querySelector('button[onclick="processSelectedTransactions()"]');
            const originalText = processBtn.innerHTML;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            processBtn.disabled = true;

            console.log('Processing selected transactions with file info:', uploadedFileInfo);
            fetch('bank_statement_processor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'process_transactions',
                        transactions: selectedTransactions,
                        period: period,
                        file_info: uploadedFileInfo // Pass file information for recording
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;

                    if (data.success) {
                        alert(
                            `Success! ${data.processed_count} transactions processed, ${data.skipped_count} skipped, ${data.unmatched_count} unmatched.`
                        );
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

        // Filter out already processed transactions
        const pendingTransactions = analysisData.filter(t => !t.processed);
        if (pendingTransactions.length === 0) {
            alert('No pending transactions to process. All transactions have already been processed.');
            return;
        }

        const period = document.getElementById('period').value;
        if (!period) {
            alert('Please select a period first.');
            return;
        }

        if (confirm(
                `Are you sure you want to process all ${pendingTransactions.length} pending transactions? This action cannot be undone.`
            )) {
            // Show loading state
            const processBtn = document.querySelector('button[onclick="processAllTransactions()"]');
            const originalText = processBtn.innerHTML;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
            processBtn.disabled = true;

            console.log('Processing transactions with file info:', uploadedFileInfo);
            fetch('bank_statement_processor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'process_transactions',
                        transactions: pendingTransactions,
                        period: period,
                        file_info: uploadedFileInfo // Pass file information for recording
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button
                    processBtn.innerHTML = originalText;
                    processBtn.disabled = false;

                    if (data.success) {
                        alert(
                            `Success! ${data.processed_count} transactions processed, ${data.skipped_count} skipped, ${data.unmatched_count} unmatched.`
                        );
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

    function openManualMatchModal(name, amount, type, uniqueId = null) {
        console.log('openManualMatchModal called with:', {
            name,
            amount,
            type,
            uniqueId
        });

        currentManualMatch = {
            name,
            amount,
            type,
            uniqueId
        };

        console.log('currentManualMatch set to:', currentManualMatch);

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
            .then(response => {
                console.log('Search response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Search response data:', data);
                if (data.success) {
                    console.log('Search successful, opening modal...');
                    displayManualMatchModal(name, amount, type);
                } else {
                    console.error('Search failed:', data.message);
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while searching for employees.');
            });
    }

    function displayManualMatchModal(name, amount, type) {
        console.log('displayManualMatchModal called with:', {
            name,
            amount,
            type
        });

        const modalContent = document.getElementById('manualMatchContent');
        console.log('Modal content element:', modalContent);
        let html = `
                <div class="mb-6">
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                        <div class="font-medium text-gray-900">Transaction:</div>
                        <div class="text-lg font-semibold ${type === 'credit' ? 'text-green-600' : 'text-red-600'}">${name} - ${type === 'credit' ? '+' : '-'}₦${amount.toLocaleString()}</div>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Employee:</label>
                    <div class="relative">
                        <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" id="manualCoopIdInput" placeholder="Type employee name or ID..." autocomplete="off">
                        <input type="hidden" id="manualCoopId" value="">
                    </div>
                    <small class="text-gray-500 mt-1" id="autocompleteHelp">Type to search for employees in the database...</small>
                </div>
            `;

        modalContent.innerHTML = html;

        // Wait for DOM to be updated, then initialize autocomplete
        setTimeout(() => {
            console.log('Initializing autocomplete after DOM update...');
            initializeAutocomplete();

            // Add input event listener to handle manual clearing
            const autocompleteInput = document.getElementById('manualCoopIdInput');
            if (autocompleteInput) {
                autocompleteInput.addEventListener('input', function() {
                    if (this.value.trim() === '') {
                        // User cleared the input manually
                        const hiddenInput = document.getElementById('manualCoopId');
                        if (hiddenInput) {
                            hiddenInput.value = '';
                        }

                        // Reset help text
                        const helpText = document.getElementById('autocompleteHelp');
                        if (helpText) {
                            helpText.textContent = 'Type to search for employees in the database...';
                            helpText.className = 'text-muted';
                        }
                    }
                });
            }
        }, 100);

        const modalElement = document.getElementById('manualMatchModal');
        console.log('Modal element:', modalElement);

        // Show the modal
        modalElement.classList.remove('hidden');
        console.log('Modal shown');
    }



    function initializeAutocomplete() {
        console.log('=== initializeAutocomplete START ===');

        const autocompleteInput = document.getElementById('manualCoopIdInput');
        const hiddenInput = document.getElementById('manualCoopId');

        console.log('autocompleteInput element:', autocompleteInput);
        console.log('hiddenInput element:', hiddenInput);

        if (!autocompleteInput) {
            console.error('Autocomplete input not found!');
            return;
        }

        // Check if jQuery is available
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded!');
            return;
        }

        // Check if jQuery UI autocomplete is available, if not wait for it
        if (typeof $.fn.autocomplete === 'undefined') {
            console.log('jQuery UI autocomplete not loaded yet, waiting...');

            // Wait for jQuery UI to load
            let attempts = 0;
            const maxAttempts = 50; // 5 seconds max wait

            const waitForJQueryUI = setInterval(() => {
                attempts++;
                if (typeof $.fn.autocomplete !== 'undefined') {
                    console.log('jQuery UI autocomplete now available after waiting');
                    clearInterval(waitForJQueryUI);
                    initializeAutocomplete(); // Retry initialization
                } else if (attempts >= maxAttempts) {
                    console.error('jQuery UI autocomplete failed to load after waiting');
                    clearInterval(waitForJQueryUI);
                    return;
                }
            }, 100);

            return;
        }

        console.log('jQuery and jQuery UI autocomplete are available');

        // Destroy any existing autocomplete
        if ($(autocompleteInput).autocomplete('instance')) {
            console.log('Destroying existing autocomplete instance');
            $(autocompleteInput).autocomplete('destroy');
        }

        // Use jQuery UI autocomplete with live database search
        try {
            $(autocompleteInput).autocomplete({
                source: function(request, response) {
                    console.log('Autocomplete source called with term:', request.term);

                    // Show loading state in help text
                    const helpText = document.getElementById('autocompleteHelp');
                    if (helpText) {
                        helpText.textContent = 'Searching...';
                        helpText.className = 'text-info';
                    }

                    // Make live AJAX call to search database
                    fetch('bank_statement_processor.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=search_members&search_term=' + encodeURIComponent(request
                                .term)
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Live search response:', data);
                            if (data.success && data.employees) {
                                // Update help text with results count
                                if (helpText) {
                                    if (data.employees.length > 0) {
                                        helpText.textContent =
                                            `Found ${data.employees.length} employee(s)`;
                                        helpText.className = 'text-success';
                                    } else {
                                        helpText.textContent = 'No employees found';
                                        helpText.className = 'text-warning';
                                    }
                                }
                                response(data.employees);
                            } else {
                                console.error('Live search failed:', data.message);
                                if (helpText) {
                                    helpText.textContent = 'Search failed: ' + data.message;
                                    helpText.className = 'text-danger';
                                }
                                response([]); // Return empty array on error
                            }
                        })
                        .catch(error => {
                            console.error('Live search error:', error);
                            if (helpText) {
                                helpText.textContent = 'Search error occurred';
                                helpText.className = 'text-danger';
                            }
                            response([]); // Return empty array on error
                        });
                },
                minLength: 2, // Start searching after 2 characters
                delay: 300, // 300ms delay to avoid too many requests
                autoFocus: true,
                select: function(event, ui) {
                    console.log('Employee selected:', ui.item);
                    autocompleteInput.value = ui.item.name;
                    hiddenInput.value = ui.item.member_id;

                    // Update help text to show selection
                    const helpText = document.getElementById('autocompleteHelp');
                    if (helpText) {
                        helpText.textContent = `Selected: ${ui.item.name} (ID: ${ui.item.member_id})`;
                        helpText.className = 'text-success';
                    }

                    return false; // Prevent default behavior
                }
            });

            // Custom rendering
            $(autocompleteInput).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div class='autocomplete-item'>" +
                        "<span class='employee-name'>" + item.name + "</span>" +
                        "<span class='employee-id'>" + item.member_id + "</span>" +
                        "</div>")
                    .appendTo(ul);
            };

            console.log('jQuery UI autocomplete with live search initialized successfully');

        } catch (error) {
            console.error('Error initializing autocomplete:', error);
        }

        console.log('=== initializeAutocomplete END ===');
    }

    function updateEmployeeList(employees) {
        // This function is no longer needed with live search
        // Keeping it for backward compatibility but it doesn't do anything
        console.log('updateEmployeeList called but not needed with live search');
    }

    function clearSearch() {
        // Clear autocomplete
        const autocompleteInput = document.getElementById('manualCoopIdInput');
        const hiddenInput = document.getElementById('manualCoopId');

        if (autocompleteInput) {
            autocompleteInput.value = '';
            hiddenInput.value = '';
        }

        // Update help text to show ready state
        const helpText = document.getElementById('autocompleteHelp');
        if (helpText) {
            helpText.textContent = 'Type to search for employees in the database...';
            helpText.className = 'text-muted'; // Reset to default styling
        }
    }

    document.getElementById('saveManualMatch').addEventListener('click', function() {
        console.log('Save manual match button clicked');

        const coopId = document.getElementById('manualCoopId').value;
        if (!coopId) {
            alert('Please select an employee.');
            return;
        }

        // Find the selected employee name from the autocomplete input
        const selectedEmployeeName = document.getElementById('manualCoopIdInput').value.trim();

        console.log('Selected employee:', {
            coopId,
            selectedEmployeeName
        });
        console.log('Current manual match:', currentManualMatch);

        // Find the transaction in analysisData and update it
        const transactionName = currentManualMatch.name;
        const transactionAmount = currentManualMatch.amount;
        const transactionType = currentManualMatch.type;

        console.log('Looking for transaction:', {
            transactionName,
            transactionAmount,
            transactionType
        });
        console.log('Current analysisData:', analysisData);

        // Find the exact transaction using unique identifier if available, otherwise use details
        let transactionIndex = -1;

        if (currentManualMatch.uniqueId) {
            // Use unique identifier for precise matching
            transactionIndex = analysisData.findIndex(t => t.uniqueId === currentManualMatch.uniqueId);
            console.log('Using unique ID for matching:', currentManualMatch.uniqueId);
        } else {
            // Fallback to detail matching for unmatched transactions
            transactionIndex = analysisData.findIndex(t =>
                t.name === transactionName &&
                parseFloat(t.amount) === parseFloat(transactionAmount) &&
                t.type === transactionType &&
                !t.matched // Only match unmatched transactions
            );
            console.log('Using detail matching as fallback');
        }

        console.log('Found transaction at index:', transactionIndex);

        if (transactionIndex !== -1) {
            console.log('Transaction found, updating...');
            console.log('Before update:', analysisData[transactionIndex]);

            // Save manual match to database first
            saveManualMatchToDatabase(transactionName, coopId, selectedEmployeeName, transactionAmount,
                transactionType, currentManualMatch.uniqueId);

            // Update the transaction with match information
            analysisData[transactionIndex].matched = true;
            analysisData[transactionIndex].member_id = coopId;
            analysisData[transactionIndex].member_name = selectedEmployeeName;

            console.log('After update:', analysisData[transactionIndex]);

            // Refresh the display to show the updated transaction
            displayAnalysisResults(analysisData);

            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4';
            alertDiv.innerHTML = `
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                    <div class="flex-1">
                        <div class="font-medium">Transaction "${transactionName}" successfully matched to "${selectedEmployeeName}" (ID: ${coopId})</div>
                    </div>
                    <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            const resultsDiv = document.getElementById('analysisResults');
            resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        } else {
            console.error('Transaction not found in analysisData');
            alert('Error: Could not find the transaction to update. Please try again.');
        }

        // Close the modal
        closeManualMatchModal();
    });

    function saveManualMatchToDatabase(transactionName, coopId, selectedEmployeeName, transactionAmount,
        transactionType, uniqueId) {
        // Save manual match to database
        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'manual_match',
                    transaction_name: transactionName,
                    member_id: coopId,
                    transaction_amount: transactionAmount,
                    transaction_type: transactionType,
                    unique_id: uniqueId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Manual match saved to database successfully');
                } else {
                    console.error('Failed to save manual match to database:', data.message);
                    alert('Warning: Manual match was not saved to database. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error saving manual match to database:', error);
                alert('Warning: Manual match was not saved to database. Please try again.');
            });
    }

    function closeManualMatchModal() {
        const modalElement = document.getElementById('manualMatchModal');
        if (modalElement) {
            modalElement.classList.add('hidden');
        }
    }

    // Add event listeners for modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Close modal when clicking backdrop
        const modalBackdrop = document.getElementById('modalBackdrop');
        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', closeManualMatchModal);
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modalElement = document.getElementById('manualMatchModal');
                if (modalElement && !modalElement.classList.contains('hidden')) {
                    closeManualMatchModal();
                }
            }
        });
    });
    </script>

    <!-- Test function for manual match -->
    <script>
    // Test function to manually trigger a match
    window.testManualMatch = function() {
        console.log('Testing manual match functionality...');

        if (!analysisData || analysisData.length === 0) {
            console.log('No analysis data available');
            return;
        }

        // Find first unmatched transaction
        const unmatchedTransaction = analysisData.find(t => !t.matched);
        if (!unmatchedTransaction) {
            console.log('No unmatched transactions found');
            return;
        }

        console.log('Found unmatched transaction:', unmatchedTransaction);

        // Simulate manual match
        unmatchedTransaction.matched = true;
        unmatchedTransaction.member_id = '999';
        unmatchedTransaction.member_name = 'TEST USER';

        console.log('Updated transaction:', unmatchedTransaction);

        // Refresh display
        displayAnalysisResults(analysisData);

        console.log('Display refreshed - check if transaction moved to matched section');
    };

    // Test function to check current state
    window.checkAnalysisData = function() {
        console.log('Current analysisData:', analysisData);
        if (analysisData && analysisData.length > 0) {
            const matched = analysisData.filter(t => t.matched);
            const unmatched = analysisData.filter(t => !t.matched);
            console.log('Matched:', matched.length, 'Unmatched:', unmatched.length);
        }
    };

    // Debug function to check transaction types
    window.debugTransactionTypes = function() {
        if (!analysisData || analysisData.length === 0) {
            console.log('No analysis data available');
            return;
        }

        console.log('=== TRANSACTION TYPE ANALYSIS ===');
        const creditTransactions = analysisData.filter(t => t.type === 'credit');
        const debitTransactions = analysisData.filter(t => t.type === 'debit');
        const invalidTransactions = analysisData.filter(t => !t.type || !['credit', 'debit'].includes(t.type));

        console.log(`Total transactions: ${analysisData.length}`);
        console.log(`Credit transactions: ${creditTransactions.length}`);
        console.log(`Debit transactions: ${debitTransactions.length}`);
        console.log(`Invalid transactions: ${invalidTransactions.length}`);

        if (invalidTransactions.length > 0) {
            console.log('Invalid transactions:', invalidTransactions);
        }

        // Sample of each type
        if (creditTransactions.length > 0) {
            console.log('Sample credit transactions:', creditTransactions.slice(0, 3));
        }
        if (debitTransactions.length > 0) {
            console.log('Sample debit transactions:', debitTransactions.slice(0, 3));
        }
    };

    // Debug function to check column detection
    window.debugColumnDetection = function() {
        console.log('=== COLUMN DETECTION DEBUG ===');
        console.log('This function can be called after uploading a file to see column detection results');
        console.log('Check the console logs during file processing for detailed column detection information');
    };

    // Excel file processing function
    function processExcelFile(file) {
        console.log(`Processing Excel file: ${file.name}`);

        // Load SheetJS library dynamically if not already loaded
        if (typeof XLSX === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
            script.onload = () => processExcelFile(file);
            script.onerror = () => {
                alert('Failed to load Excel processing library. Please try again.');
            };
            document.head.appendChild(script);
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {
                    type: 'array'
                });

                // Get the first sheet
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];

                // Convert to JSON with header row
                const jsonData = XLSX.utils.sheet_to_json(worksheet, {
                    header: 1
                });

                if (jsonData.length < 2) {
                    alert('Excel file must have at least a header row and one data row.');
                    return;
                }

                // Extract transactions using table structure
                const transactions = extractTransactionsFromExcelTable(jsonData, file.name);

                if (transactions.length > 0) {
                    console.log(`Successfully extracted ${transactions.length} transactions from Excel file`);

                    // Add unique identifiers
                    const transactionsWithIds = transactions.map((transaction, index) => ({
                        ...transaction,
                        uniqueId: `excel_${Date.now()}_${index}`,
                        fileName: file.name
                    }));

                    // Add to analysis data
                    analysisData = analysisData.concat(transactionsWithIds);

                    // Display results
                    displayAnalysisResults(analysisData);

                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 mb-4';
                    alertDiv.innerHTML = `
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <div class="font-medium">Excel file processed successfully!</div>
                                <div class="text-sm">Found ${transactions.length} transactions using table structure.</div>
                            </div>
                            <button type="button" class="ml-auto text-green-600 hover:text-green-800" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

                    const resultsDiv = document.getElementById('analysisResults');
                    if (resultsDiv) {
                        resultsDiv.insertBefore(alertDiv, resultsDiv.firstChild);
                    }
                } else {
                    alert('No transactions found in the Excel file. Please check the format.');
                }

            } catch (error) {
                console.error('Error processing Excel file:', error);
                alert('Error processing Excel file: ' + error.message);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function extractTransactionsFromExcelTable(jsonData, fileName) {
        console.log('Extracting transactions from Excel table structure');

        const headers = jsonData[0];
        const dataRows = jsonData.slice(1);

        // Find column indices
        let creditColumnIndex = -1;
        let debitColumnIndex = -1;
        let dateColumnIndex = -1;
        let descriptionColumnIndex = -1;
        let balanceColumnIndex = -1;

        headers.forEach((header, index) => {
            if (!header) return;

            const headerText = header.toString().toLowerCase();
            if (headerText.includes('credit') || headerText.includes('cr') || headerText.includes('deposit') ||
                headerText.includes('inflow')) {
                creditColumnIndex = index;
                console.log(`Excel Credit column found at index ${index}: "${header}"`);
            } else if (headerText.includes('debit') || headerText.includes('dr') || headerText.includes(
                    'withdrawal') || headerText.includes('outflow')) {
                debitColumnIndex = index;
                console.log(`Excel Debit column found at index ${index}: "${header}"`);
            } else if (headerText.includes('date') || headerText.includes('value date')) {
                dateColumnIndex = index;
                console.log(`Excel Date column found at index ${index}: "${header}"`);
            } else if (headerText.includes('description') || headerText.includes('narration') || headerText
                .includes('particulars') || headerText.includes('details')) {
                descriptionColumnIndex = index;
                console.log(`Excel Description column found at index ${index}: "${header}"`);
            } else if (headerText.includes('balance')) {
                balanceColumnIndex = index;
                console.log(`Excel Balance column found at index ${index}: "${header}"`);
            }
        });

        console.log('Excel column indices:', {
            credit: creditColumnIndex,
            debit: debitColumnIndex,
            date: dateColumnIndex,
            description: descriptionColumnIndex,
            balance: balanceColumnIndex
        });

        // Process data rows
        const transactions = [];
        dataRows.forEach((row, rowIndex) => {
            if (!row || row.length < 2) return;

            let transaction = {
                pageNumber: 1, // Excel files are single page
                rowIndex: rowIndex,
                date: null,
                description: '',
                amount: 0,
                type: null,
                balance: null,
                fileName: fileName
            };

            // Extract data based on column positions
            if (dateColumnIndex >= 0 && row[dateColumnIndex]) {
                transaction.date = row[dateColumnIndex].toString().trim();
            }

            if (descriptionColumnIndex >= 0 && row[descriptionColumnIndex]) {
                transaction.description = row[descriptionColumnIndex].toString().trim();
            }

            if (balanceColumnIndex >= 0 && row[balanceColumnIndex]) {
                const balanceText = row[balanceColumnIndex].toString().trim();
                transaction.balance = parseFloat(balanceText.replace(/[^\d.-]/g, ''));
            }

            // Determine transaction type and amount based on credit/debit columns
            // In bank statements, a transaction typically has an amount in only ONE column
            let hasCreditAmount = false;
            let hasDebitAmount = false;

            if (creditColumnIndex >= 0 && row[creditColumnIndex]) {
                const creditText = row[creditColumnIndex].toString().trim();
                const creditAmount = parseFloat(creditText.replace(/[^\d.-]/g, ''));
                if (creditAmount > 0) {
                    hasCreditAmount = true;
                    transaction.amount = creditAmount;
                    transaction.type = 'credit';
                }
            }

            if (debitColumnIndex >= 0 && row[debitColumnIndex]) {
                const debitText = row[debitColumnIndex].toString().trim();
                const debitAmount = parseFloat(debitText.replace(/[^\d.-]/g, ''));
                if (debitAmount > 0) {
                    hasDebitAmount = true;
                    transaction.amount = debitAmount;
                    transaction.type = 'debit';
                }
            }

            // Validate: a transaction should have amount in exactly one column
            if (hasCreditAmount && hasDebitAmount) {
                console.warn(
                    `Row ${rowIndex}: Transaction has amounts in both credit and debit columns - skipping`);
                return;
            }

            // If we have a valid transaction with amount in exactly one column, add it
            if (transaction.amount > 0 && transaction.type && (hasCreditAmount !== hasDebitAmount)) {
                console.log(
                    `Row ${rowIndex}: ${transaction.type.toUpperCase()} transaction - Amount: ${transaction.amount}, Column: ${transaction.type === 'credit' ? 'Credit' : 'Debit'}`
                );
                transactions.push(transaction);
            }
        });

        console.log(`Extracted ${transactions.length} transactions from Excel file`);

        // Validate extracted transactions
        if (transactions.length > 0) {
            console.log('Excel Transaction validation:');
            transactions.forEach((txn, idx) => {
                console.log(
                    `  ${idx + 1}. ${txn.type.toUpperCase()}: ${txn.amount} - ${txn.description || 'No description'}`
                );
            });
        }

        return transactions;
    }

    // Debug function to test table structure detection
    function debugTableStructure() {
        console.log('=== DEBUGGING TABLE STRUCTURE ===');
        console.log('This function tests the table structure detection with a sample bank statement');
        console.log('It includes the LOLU WUNMI GLOBAL VENTURES transaction to verify credit/debit detection');
        console.log('');

        // Get the current file input
        const fileInput = document.getElementById('fileInput');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            console.log('❌ No files selected');
            console.log('Using test data instead...');
        } else {
            const file = fileInput.files[0];
            console.log('Testing file:', file.name);
        }

        // Create a test page object to simulate what we'd get from PDF extraction
        const testPage = {
            pageNumber: 1,
            text: `DATE   DESCRIPTION   DEBIT   CREDIT   VALUE DATE   BALANCE
01/06/2025   TRF FROM TOLANIKAWO JEJELOLA ADEBAJO//TRF TO VICTORY SAGAMU REMO COOP MULTI SOC LTD//Savings 150,000.00 01/06/2025   16,563,025.93
01/06/2025   FGN ELECTRONIC MONEY TRANSFER LEVY   2,250.00   01/06/2025   16,560,775.93
02/06/2025   NIP/ABN/THEOPHILUS AIVBELOSUOGHENE UWANOGHO/TRFRentFRM THEOPHILUS AIVBELOSUOGHENE UWANOGHO TO VICTORY SAGAMU REMO COOP M 200,000.00 02/06/2025   16,795,775.93
03/06/2025   Loan to Omolade v /CIB//NIP TFR TO FATADE OMOLADE VICTORIA/GTB   2,000,000.00   03/06/2025   15,510,775.93
03/06/2025   LOLU WUNMI GLOBAL VENTURES   200,000.00   03/06/2025   15,310,775.93`,
            items: [
                // Simulate the items array that would come from PDF extraction
                {
                    text: 'DATE',
                    x: 0,
                    y: 0
                },
                {
                    text: 'DESCRIPTION',
                    x: 100,
                    y: 0
                },
                {
                    text: 'DEBIT',
                    x: 400,
                    y: 0
                },
                {
                    text: 'CREDIT',
                    x: 500,
                    y: 0
                },
                {
                    text: 'VALUE DATE',
                    x: 600,
                    y: 0
                },
                {
                    text: 'BALANCE',
                    x: 700,
                    y: 0
                },
                {
                    text: '01/06/2025',
                    x: 0,
                    y: 30
                },
                {
                    text: 'TRF FROM TOLANIKAWO JEJELOLA ADEBAJO//TRF TO VICTORY SAGAMU REMO COOP MULTI SOC LTD//Savings',
                    x: 100,
                    y: 30
                },
                {
                    text: '150,000.00',
                    x: 500,
                    y: 30
                },
                {
                    text: '01/06/2025',
                    x: 600,
                    y: 30
                },
                {
                    text: '16,563,025.93',
                    x: 700,
                    y: 30
                },
                {
                    text: '01/06/2025',
                    x: 0,
                    y: 60
                },
                {
                    text: 'FGN ELECTRONIC MONEY TRANSFER LEVY',
                    x: 100,
                    y: 60
                },
                {
                    text: '2,250.00',
                    x: 400,
                    y: 60
                },
                {
                    text: '01/06/2025',
                    x: 600,
                    y: 60
                },
                {
                    text: '16,560,775.93',
                    x: 700,
                    y: 60
                },
                {
                    text: '02/06/2025',
                    x: 0,
                    y: 90
                },
                {
                    text: 'NIP/ABN/THEOPHILUS AIVBELOSUOGHENE UWANOGHO/TRFRentFRM THEOPHILUS AIVBELOSUOGHENE UWANOGHO TO VICTORY SAGAMU REMO COOP M',
                    x: 100,
                    y: 90
                },
                {
                    text: '200,000.00',
                    x: 500,
                    y: 90
                },
                {
                    text: '02/06/2025',
                    x: 600,
                    y: 90
                },
                {
                    text: '16,795,775.93',
                    x: 700,
                    y: 90
                },
                {
                    text: '03/06/2025',
                    x: 0,
                    y: 120
                },
                {
                    text: 'Loan to Omolade v /CIB//NIP TFR TO FATADE OMOLADE VICTORIA/GTB',
                    x: 100,
                    y: 120
                },
                {
                    text: '2,000,000.00',
                    x: 400,
                    y: 120
                },
                {
                    text: '03/06/2025',
                    x: 600,
                    y: 120
                },
                {
                    text: '15,510,775.93',
                    x: 700,
                    y: 120
                },
                {
                    text: '03/06/2025',
                    x: 0,
                    y: 150
                },
                {
                    text: 'LOLU WUNMI GLOBAL VENTURES',
                    x: 100,
                    y: 150
                },
                {
                    text: '200,000.00',
                    x: 500,
                    y: 150
                },
                {
                    text: '03/06/2025',
                    x: 600,
                    y: 150
                },
                {
                    text: '15,310,775.93',
                    x: 700,
                    y: 150
                }
            ]
        };

        console.log('Test page data:', testPage);

        // Test the table structure detection
        const result = extractTableDataFromPage(testPage);
        console.log('Extraction result:', result);

        return result;
    }
    </script>
</body>

</html>