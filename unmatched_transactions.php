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
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unmatched Transactions Management</title>
    
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
        
        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
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
        
        .select2-container--default .select2-selection--single {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            height: 45px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 45px;
            padding-left: 15px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 43px;
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
                <h1><i class="fas fa-search"></i> Unmatched Transactions Management</h1>
                <p class="mb-0">Review and match transactions that couldn't be automatically identified</p>
            </div>
            
            <!-- Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <a href="ai_bank_statement_upload.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Upload
                    </a>
                    <a href="manual_transaction_matches.php" class="btn btn-warning ms-2">
                        <i class="fas fa-check-circle"></i> View Matched Transactions
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number" id="totalCount">-</div>
                        <div>Total Unmatched</div>
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
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by name or description...">
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
                                <th>Description</th>
                                <th>Period</th>
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
    
    <!-- Manual Match Modal -->
    <div class="modal fade" id="manualMatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manual Member Matching</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                    action: 'get_unmatched_transactions',
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
                tbody.html('<tr><td colspan="7" class="text-center">No unmatched transactions found</td></tr>');
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
                        <td>${escapeHtml(transaction.transaction_description || 'N/A')}</td>
                        <td>${transaction.period_id || 'N/A'}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="openManualMatchModal('${escapeHtml(transaction.transaction_name)}', ${transaction.transaction_amount}, '${transaction.transaction_type}', ${transaction.id})">
                                <i class="fas fa-user-plus"></i> Match
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
        
        function openManualMatchModal(name, amount, type, transactionId) {
            currentTransaction = {
                id: transactionId,
                name: name,
                amount: amount,
                type: type
            };
            
            // Search for employees
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
                    showAlert('Error searching employees: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error searching employees: ' + error.message, 'danger');
            });
        }
        
        function displayManualMatchModal(name, amount, type, employees) {
            const modalContent = document.getElementById('manualMatchContent');
            let html = `
                <div class="mb-3">
                    <strong>Transaction:</strong> ${escapeHtml(name)} - ${type === 'credit' ? '+' : '-'}₦${amount.toLocaleString()}
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
                    <select class="form-control" id="manualCoopId" size="8" style="max-height: 300px; overflow-y: auto;">
                        <option value="">Select an employee...</option>
            `;

            employees.forEach(employee => {
                html += `<option value="${employee.member_id}">${escapeHtml(employee.name)} (${employee.member_id})</option>`;
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
                    }, 500);
                }
            });

            const modal = new bootstrap.Modal(document.getElementById('manualMatchModal'));
            modal.show();
        }
        
        function searchEmployees() {
            const searchTerm = document.getElementById('employeeSearch').value.trim();
            if (!searchTerm) {
                showAlert('Please enter a search term.', 'warning');
                return;
            }

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
                if (data.success) {
                    updateEmployeeList(data.employees);
                } else {
                    showAlert('Error searching employees: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error searching employees: ' + error.message, 'danger');
            });
        }
        
        function updateEmployeeList(employees) {
            const select = document.getElementById('manualCoopId');
            select.innerHTML = '<option value="">Select an employee...</option>';
            
            employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.member_id;
                option.textContent = `${employee.name} (${employee.member_id})`;
                select.appendChild(option);
            });
        }
        
        function clearSearch() {
            document.getElementById('employeeSearch').value = '';
            // Reset to original search
            openManualMatchModal(currentTransaction.name, currentTransaction.amount, currentTransaction.type, currentTransaction.id);
        }
        
        // Save manual match
        document.getElementById('saveManualMatch').addEventListener('click', function() {
            const memberId = document.getElementById('manualCoopId').value;
            if (!memberId) {
                showAlert('Please select an employee.', 'warning');
                return;
            }
            
            if (!currentTransaction) {
                showAlert('No transaction selected.', 'danger');
                return;
            }
            
            // Save the match
            fetch('bank_statement_processor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'manual_match',
                    transaction_name: currentTransaction.name,
                    member_id: memberId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Transaction matched successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('manualMatchModal')).hide();
                    loadTransactions(); // Refresh the list
                } else {
                    showAlert('Error saving match: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                showAlert('Error saving match: ' + error.message, 'danger');
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
