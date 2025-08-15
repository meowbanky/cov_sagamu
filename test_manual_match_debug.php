<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Match Debug Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manual Match Debug Test</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Test Manual Match</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary" onclick="testManualMatch()">
                            Test Manual Match Modal
                        </button>
                        <button class="btn btn-secondary" onclick="testSearchEmployees()">
                            Test Employee Search
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Console Log</h5>
                    </div>
                    <div class="card-body">
                        <div id="consoleLog" style="height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; font-family: monospace; font-size: 12px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Match Modal -->
    <div class="modal fade" id="manualMatchModal" tabindex="-1" aria-labelledby="manualMatchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manualMatchModalLabel">Manual Member Matching</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="manualMatchContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveManualMatch">Save Match</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function log(message) {
            const consoleDiv = document.getElementById('consoleLog');
            const timestamp = new Date().toLocaleTimeString();
            consoleDiv.innerHTML += `[${timestamp}] ${message}\n`;
            consoleDiv.scrollTop = consoleDiv.scrollHeight;
            console.log(message);
        }

        function testManualMatch() {
            log('Testing manual match modal...');
            
            // Simulate the openManualMatchModal function
            const name = 'JOHN DOE';
            const amount = 50000;
            const type = 'credit';
            
            log(`Searching for: ${name}`);
            
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
                log(`Response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                log(`Response data: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    displayManualMatchModal(name, amount, type, data.employees);
                } else {
                    log(`Error: ${data.message}`);
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                log(`Fetch error: ${error.message}`);
                console.error('Error:', error);
                alert('An error occurred while searching for employees.');
            });
        }

        function testSearchEmployees() {
            log('Testing employee search...');
            
            const searchTerm = 'JOHN';
            log(`Searching for: ${searchTerm}`);
            
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
            .then(response => {
                log(`Response status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                log(`Response data: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    log(`Found ${data.employees.length} employees`);
                    data.employees.forEach(emp => {
                        log(`- ${emp.name} (ID: ${emp.member_id})`);
                    });
                } else {
                    log(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                log(`Fetch error: ${error.message}`);
                console.error('Error:', error);
            });
        }

        function displayManualMatchModal(name, amount, type, employees) {
            log(`Displaying modal with ${employees.length} employees`);
            
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
            log('Modal content updated');

            const modal = new bootstrap.Modal(document.getElementById('manualMatchModal'));
            modal.show();
            log('Modal shown');
        }

        function searchEmployees() {
            const searchTerm = document.getElementById('employeeSearch').value.trim();
            log(`Searching for: ${searchTerm}`);
            
            if (!searchTerm) {
                log('No search term provided');
                alert('Please enter a search term.');
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
                log(`Search response: ${JSON.stringify(data)}`);
                
                if (data.success) {
                    updateEmployeeList(data.employees);
                } else {
                    log(`Search error: ${data.message}`);
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                log(`Search fetch error: ${error.message}`);
                console.error('Error:', error);
                alert('An error occurred while searching for employees.');
            });
        }

        function updateEmployeeList(employees) {
            log(`Updating employee list with ${employees.length} results`);
            
            const selectElement = document.getElementById('manualCoopId');
            const searchTerm = document.getElementById('employeeSearch').value.trim();

            selectElement.innerHTML = '<option value="">Select an employee...</option>';

            if (employees.length === 0) {
                selectElement.innerHTML += '<option value="" disabled>No employees found</option>';
            } else {
                employees.forEach(employee => {
                    selectElement.innerHTML +=
                        `<option value="${employee.member_id}">${employee.name} (${employee.member_id})</option>`;
                });
            }

            log('Employee list updated');
        }

        function clearSearch() {
            log('Clearing search');
            document.getElementById('employeeSearch').value = '';
            // For this test, we'll just show a message
            log('Search cleared');
        }

        // Add event listener for save button
        document.getElementById('saveManualMatch').addEventListener('click', function() {
            const coopId = document.getElementById('manualCoopId').value;
            log(`Save button clicked. Selected ID: ${coopId}`);
            
            if (!coopId) {
                log('No employee selected');
                alert('Please select an employee.');
                return;
            }

            const selectElement = document.getElementById('manualCoopId');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const selectedEmployeeName = selectedOption.text.split(' (')[0];

            log(`Selected employee: ${selectedEmployeeName} (ID: ${coopId})`);
            alert(`Match saved: ${selectedEmployeeName} (ID: ${coopId})`);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('manualMatchModal'));
            modal.hide();
        });

        // Initialize
        log('Manual Match Debug Test initialized');
    </script>
</body>
</html> 