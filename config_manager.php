<?php
session_start();
require_once('Connections/coop.php');
require_once('config/EnvConfig.php');

// Check if user is logged in
if (!isset($_SESSION['SESS_FIRST_NAME'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    $message_type = '';
    
    try {
        $config_content = "# Database Configuration\n";
        $config_content .= "DB_HOST=" . $_POST['db_host'] . "\n";
        $config_content .= "DB_NAME=" . $_POST['db_name'] . "\n";
        $config_content .= "DB_USER=" . $_POST['db_user'] . "\n";
        $config_content .= "DB_PASSWORD=" . $_POST['db_password'] . "\n\n";
        
        $config_content .= "# OpenAI Configuration\n";
        $config_content .= "OPENAI_API_KEY=" . $_POST['openai_key'] . "\n\n";
        
        $config_content .= "# Application Configuration\n";
        $config_content .= "APP_NAME=" . $_POST['app_name'] . "\n";
        $config_content .= "APP_ENV=" . $_POST['app_env'] . "\n";
        $config_content .= "APP_DEBUG=" . $_POST['app_debug'] . "\n\n";
        
        $config_content .= "# File Upload Configuration\n";
        $config_content .= "MAX_FILE_SIZE=" . $_POST['max_file_size'] . "\n";
        $config_content .= "ALLOWED_FILE_TYPES=" . $_POST['allowed_file_types'] . "\n\n";
        
        $config_content .= "# Security Configuration\n";
        $config_content .= "SESSION_TIMEOUT=" . $_POST['session_timeout'] . "\n";
        $config_content .= "ENCRYPTION_KEY=" . $_POST['encryption_key'] . "\n";
        
        if (file_put_contents('config.env', $config_content)) {
            $message = "Configuration updated successfully!";
            $message_type = "success";
        } else {
            throw new Exception("Failed to write configuration file");
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Load current configuration
$config = EnvConfig::load();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Manager</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <style>
    .config-section {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .config-section h5 {
        color: #495057;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fa fa-cog"></i> Configuration Manager</h2>
                    <a href="bank_statement_upload.php" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to Upload
                    </a>
                </div>

                <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <i
                        class="fa fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Database Configuration -->
                    <div class="config-section">
                        <h5><i class="fa fa-database"></i> Database Configuration</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_host">Database Host:</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host"
                                        value="<?php echo $config['DB_HOST'] ?? 'localhost'; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_name">Database Name:</label>
                                    <input type="text" class="form-control" id="db_name" name="db_name"
                                        value="<?php echo $config['DB_NAME'] ?? 'emmaggic_coop'; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_user">Database User:</label>
                                    <input type="text" class="form-control" id="db_user" name="db_user"
                                        value="<?php echo $config['DB_USER'] ?? 'emmaggic_root'; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_password">Database Password:</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password"
                                        value="<?php echo $config['DB_PASSWORD'] ?? 'Oluwaseyi'; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OpenAI Configuration -->
                    <div class="config-section">
                        <h5><i class="fa fa-robot"></i> OpenAI Configuration</h5>
                        <div class="form-group">
                            <label for="openai_key">OpenAI API Key:</label>
                            <input type="password" class="form-control" id="openai_key" name="openai_key"
                                value="<?php echo $config['OPENAI_API_KEY'] ?? 'your_openai_api_key_here'; ?>" required>
                            <small class="form-text text-muted">
                                Get your API key from <a href="https://platform.openai.com/api-keys"
                                    target="_blank">OpenAI Platform</a>
                            </small>
                        </div>
                    </div>

                    <!-- Application Configuration -->
                    <div class="config-section">
                        <h5><i class="fa fa-application"></i> Application Configuration</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="app_name">Application Name:</label>
                                    <input type="text" class="form-control" id="app_name" name="app_name"
                                        value="<?php echo $config['APP_NAME'] ?? 'Cooperative Management System'; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="app_env">Environment:</label>
                                    <select class="form-control" id="app_env" name="app_env">
                                        <option value="production"
                                            <?php echo ($config['APP_ENV'] ?? 'production') === 'production' ? 'selected' : ''; ?>>
                                            Production</option>
                                        <option value="development"
                                            <?php echo ($config['APP_ENV'] ?? '') === 'development' ? 'selected' : ''; ?>>
                                            Development</option>
                                        <option value="testing"
                                            <?php echo ($config['APP_ENV'] ?? '') === 'testing' ? 'selected' : ''; ?>>
                                            Testing</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="app_debug">Debug Mode:</label>
                                    <select class="form-control" id="app_debug" name="app_debug">
                                        <option value="false"
                                            <?php echo ($config['APP_DEBUG'] ?? 'false') === 'false' ? 'selected' : ''; ?>>
                                            Disabled</option>
                                        <option value="true"
                                            <?php echo ($config['APP_DEBUG'] ?? '') === 'true' ? 'selected' : ''; ?>>
                                            Enabled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload Configuration -->
                    <div class="config-section">
                        <h5><i class="fa fa-upload"></i> File Upload Configuration</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_file_size">Maximum File Size:</label>
                                    <input type="text" class="form-control" id="max_file_size" name="max_file_size"
                                        value="<?php echo $config['MAX_FILE_SIZE'] ?? '10MB'; ?>">
                                    <small class="form-text text-muted">e.g., 10MB, 100MB</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="allowed_file_types">Allowed File Types:</label>
                                    <input type="text" class="form-control" id="allowed_file_types"
                                        name="allowed_file_types"
                                        value="<?php echo $config['ALLOWED_FILE_TYPES'] ?? 'pdf,xlsx,xls,jpg,jpeg,png'; ?>">
                                    <small class="form-text text-muted">Comma-separated list</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Configuration -->
                    <div class="config-section">
                        <h5><i class="fa fa-shield"></i> Security Configuration</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="session_timeout">Session Timeout (seconds):</label>
                                    <input type="number" class="form-control" id="session_timeout"
                                        name="session_timeout"
                                        value="<?php echo $config['SESSION_TIMEOUT'] ?? '3600'; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="encryption_key">Encryption Key:</label>
                                    <input type="text" class="form-control" id="encryption_key" name="encryption_key"
                                        value="<?php echo $config['ENCRYPTION_KEY'] ?? 'your_encryption_key_here'; ?>">
                                    <small class="form-text text-muted">Used for data encryption</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fa fa-save"></i> Save Configuration
                        </button>
                        <a href="test_bank_statement_system.php" class="btn btn-info btn-lg ml-2" target="_blank">
                            <i class="fa fa-cog"></i> Test Configuration
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
    // Show/hide password functionality
    document.getElementById('openai_key').addEventListener('focus', function() {
        this.type = 'text';
    });

    document.getElementById('openai_key').addEventListener('blur', function() {
        if (this.value === 'your_openai_api_key_here') {
            this.type = 'password';
        }
    });

    document.getElementById('db_password').addEventListener('focus', function() {
        this.type = 'text';
    });

    document.getElementById('db_password').addEventListener('blur', function() {
        this.type = 'password';
    });
    </script>
</body>

</html>