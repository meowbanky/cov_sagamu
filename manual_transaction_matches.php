<?php
session_start();
require_once('config/EnvConfig.php');

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
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matched Transactions Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-container {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        margin: 20px;
        padding: 30px;
        backdrop-filter: blur(10px);
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: center;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .search-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .table-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .table {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        padding: 15px;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.1);
        transform: scale(1.01);
    }

    .pagination {
        justify-content: center;
        margin-top: 20px;
    }

    .page-link {
        border-radius: 10px;
        margin: 0 5px;
        border: none;
        color: #667eea;
        font-weight: 600;
    }

    .page-link:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .loading {
        display: none;
        text-align: center;
        padding: 20px;
    }

    .spinner-border {
        color: #667eea;
    }

    .alert {
        border-radius: 10px;
        border: none;
        margin-bottom: 20px;
    }

    .alert-success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
    }

    .modal-body {
        padding: 25px;
    }

    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    @media (max-width: 768px) {
        .main-container {
            margin: 10px;
            padding: 20px;
        }

        .table-responsive {
            font-size: 14px;
        }

        .btn {
            padding: 8px 12px;
            font-size: 14px;
        }

        .stats-number {
            font-size: 2rem;
        }
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="main-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-check-circle"></i> Matched Transactions Management</h1>
                <p class="mb-0">Review and process transactions that have been matched to members</p>
            </div>

            <!-- Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <a href="ai_bank_statement_upload.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Upload
                    </a>
                    <a href="unmatched_transactions.php" class="btn btn-warning ms-2">
                        <i class="fas fa-search"></i> View Unmatched Transactions
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number" id="totalCount">-</div>
                        <div>Total Matched</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number" id="creditCount">-</div>
                        <div>Credit Transactions</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number" id="debitCount">-</div>
                        <div>Debit Transactions</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number" id="periodCount">-</div>
                        <div>Periods</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-section">
                <div class="row">
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label">Search Transactions</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput"
                                placeholder="Search by name or description...">
                            <button class="btn btn-primary" type="button" onclick="searchTransactions()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="periodFilter" class="form-label">Filter by Period</label>
                        <select class="form-control" id="periodFilter">
                            <option value="">All Periods</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="typeFilter" class="form-label">Filter by Type</label>
                        <select class="form-control" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="credit">Credit</option>
                            <option value="debit">Debit</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="loadTransactions()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div id="alertContainer"></div>

            <!-- Loading -->
            <div class="loading" id="loading">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading transactions...</p>
            </div>

            <!-- Transactions Table -->
            <div class="table-container" id="tableContainer" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover" id="transactionsTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction Name</th>
                                <th>Amount</th>
                                <th>Type</th>
                                <th>Member</th>
                                <th>Period</th>
                                <th>Matched By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTableBody">
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Transaction pagination">
                    <ul class="pagination" id="pagination">
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Process Transaction Modal -->
    <div class="modal fade" id="processTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="processTransactionContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="processTransaction">Process Transaction</button>
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

    <script>
    let currentPage = 1;
    let totalPages = 1;
    let currentTransaction = null;

    // Initialize page
    $(document).ready(function() {
        loadTransactions();
        setupEventListeners();
    });

    function setupEventListeners() {
        // Search on Enter key
        $('#searchInput').on('keypress', function(e) {
            if (e.key === 'Enter') {
                searchTransactions();
            }
        });

        // Filter changes
        $('#periodFilter, #typeFilter').on('change', function() {
            currentPage = 1;
            loadTransactions();
        });

        // Debounced search
        let searchTimeout;
        $('#searchInput').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                currentPage = 1;
                searchTransactions();
            }, 500);
        });
    }

    function loadTransactions() {
        showLoading(true);

        const search = $('#searchInput').val().trim();
        const periodFilter = $('#periodFilter').val();
        const typeFilter = $('#typeFilter').val();

        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_matched_transactions',
                    page: currentPage,
                    limit: 50,
                    search: search,
                    period_filter: periodFilter,
                    type_filter: typeFilter
                })
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                if (data.success) {
                    displayTransactions(data.transactions);
                    updatePagination(data.current_page, data.total_pages);
                    updateStats(data);
                    updatePeriodFilter(data.periods);
                } else {
                    showAlert('Error loading transactions: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showLoading(false);
                showAlert('Error loading transactions: ' + error.message, 'danger');
            });
    }

    function searchTransactions() {
        currentPage = 1;
        loadTransactions();
    }

    function displayTransactions(transactions) {
        const tbody = $('#transactionsTableBody');
        tbody.empty();

        if (transactions.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center">No matched transactions found</td></tr>');
            return;
        }

        transactions.forEach(transaction => {
            const row = `
                    <tr>
                        <td>${transaction.transaction_date || 'N/A'}</td>
                        <td>${escapeHtml(transaction.transaction_name)}</td>
                        <td class="text-${transaction.transaction_type === 'credit' ? 'success' : 'danger'}">
                            <strong>₦${parseFloat(transaction.transaction_amount).toLocaleString()}</strong>
                        </td>
                        <td>
                            <span class="badge bg-${transaction.transaction_type === 'credit' ? 'success' : 'danger'}">
                                ${transaction.transaction_type.toUpperCase()}
                            </span>
                        </td>
                        <td>${escapeHtml(transaction.member_name || 'N/A')}</td>
                        <td>${transaction.period_id || 'N/A'}</td>
                        <td>${escapeHtml(transaction.matched_by || 'N/A')}</td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="processTransaction(${transaction.id})">
                                <i class="fas fa-check"></i> Process
                            </button>
                            <button class="btn btn-danger btn-sm ms-1" onclick="deleteTransaction(${transaction.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                `;
            tbody.append(row);
        });

        $('#tableContainer').show();
    }

    function updatePagination(currentPage, totalPages) {
        const pagination = $('#pagination');
        pagination.empty();

        if (totalPages <= 1) return;

        // Previous button
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        pagination.append(`
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                </li>
            `);

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentPage ? 'active' : '';
            pagination.append(`
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                    </li>
                `);
        }

        // Next button
        const nextDisabled = currentPage === totalPages ? 'disabled' : '';
        pagination.append(`
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                </li>
            `);
    }

    function changePage(page) {
        currentPage = page;
        loadTransactions();
    }

    function updateStats(data) {
        $('#totalCount').text(data.total_count || 0);

        // Calculate type counts
        const creditCount = data.transactions.filter(t => t.transaction_type === 'credit').length;
        const debitCount = data.transactions.filter(t => t.transaction_type === 'debit').length;

        $('#creditCount').text(creditCount);
        $('#debitCount').text(debitCount);
        $('#periodCount').text(data.periods ? data.periods.length : 0);
    }

    function updatePeriodFilter(periods) {
        const select = $('#periodFilter');
        const currentValue = select.val();

        // Keep existing options but update the period options
        select.find('option:not(:first)').remove();

        if (periods && periods.length > 0) {
            periods.forEach(period => {
                const selected = period === currentValue ? 'selected' : '';
                select.append(`<option value="${period}" ${selected}>${period}</option>`);
            });
        }
    }

    function processTransaction(transactionId) {
        currentTransaction = {
            id: transactionId
        };

        const modalContent = document.getElementById('processTransactionContent');
        modalContent.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Process Transaction</strong>
                    <p class="mb-0 mt-2">This will insert the transaction into the appropriate database table (contributions or loans) and remove it from the pending matches.</p>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="confirmProcess" required>
                    <label class="form-check-label" for="confirmProcess">
                        I confirm that this transaction should be processed
                    </label>
                </div>
            `;

        const modal = new bootstrap.Modal(document.getElementById('processTransactionModal'));
        modal.show();
    }

    function deleteTransaction(transactionId) {
        if (confirm('Are you sure you want to delete this matched transaction? This action cannot be undone.')) {
            // Call backend to delete transaction
            fetch('bank_statement_processor.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'delete_matched_transaction',
                        transaction_id: transactionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        loadTransactions(); // Refresh the list
                    } else {
                        showAlert('Error deleting transaction: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Error deleting transaction: ' + error.message, 'danger');
                });
        }
    }

    // Process transaction button handler
    document.getElementById('processTransaction').addEventListener('click', function() {
        const confirmed = document.getElementById('confirmProcess').checked;
        if (!confirmed) {
            showAlert('Please confirm that you want to process this transaction.', 'warning');
            return;
        }

        if (!currentTransaction) {
            showAlert('No transaction selected.', 'danger');
            return;
        }

        // Call backend to process transaction
        fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'process_matched_transaction',
                    transaction_id: currentTransaction.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('processTransactionModal')).hide();
                    loadTransactions(); // Refresh the list
                } else {
                    showAlert('Error processing transaction: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error processing transaction: ' + error.message, 'danger');
            });
    });

    function showLoading(show) {
        if (show) {
            $('#loading').show();
            $('#tableContainer').hide();
        } else {
            $('#loading').hide();
        }
    }

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertId = 'alert-' + Date.now();

        const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" id="${alertId}" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

        alertContainer.innerHTML = alertHtml;

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>
</body>

</html>