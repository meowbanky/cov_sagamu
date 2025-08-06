<?php

class EnvConfig {
    private static $config = null;
    
    /**
     * Load configuration from config.env file
     */
    private static function loadConfig() {
        if (self::$config === null) {
            self::$config = [];
            
            $config_file = __DIR__ . '/../config.env';
            
            if (file_exists($config_file)) {
                $lines = file($config_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                
                foreach ($lines as $line) {
                    // Skip comments
                    if (strpos(trim($line), '#') === 0) {
                        continue;
                    }
                    
                    // Parse key=value pairs
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $key = trim($key);
                        $value = trim($value);
                        
                        // Remove quotes if present
                        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                            $value = substr($value, 1, -1);
                        }
                        
                        self::$config[$key] = $value;
                    }
                }
            }
        }
    }
    
    /**
     * Get a configuration value
     */
    public static function get($key, $default = null) {
        self::loadConfig();
        return isset(self::$config[$key]) ? self::$config[$key] : $default;
    }
    
    /**
     * Get database host
     */
    public static function getDBHost() {
        return self::get('DB_HOST', 'localhost');
    }
    
    /**
     * Get database name
     */
    public static function getDBName() {
        return self::get('DB_NAME', 'emmaggic_cofv');
    }
    
    /**
     * Get database user
     */
    public static function getDBUser() {
        return self::get('DB_USER', 'emmaggic_root');
    }
    
    /**
     * Get database password
     */
    public static function getDBPassword() {
        return self::get('DB_PASSWORD', 'Oluwaseyi');
    }
    
    /**
     * Get OpenAI API key
     */
    public static function getOpenAIKey() {
        return self::get('OPENAI_API_KEY', '');
    }
    

    /**
     * Check if OpenAI key is configured
     */
    public static function hasOpenAIKey() {
        $key = self::getOpenAIKey();
        return !empty($key) && strlen($key) > 20; // Basic validation that it's a real API key
    }
    
    /**
     * Get application name
     */
    public static function getAppName() {
        return self::get('APP_NAME', 'Cooperative Management System');
    }
    
    /**
     * Get application environment
     */
    public static function getAppEnv() {
        return self::get('APP_ENV', 'production');
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebug() {
        return self::get('APP_DEBUG', 'false') === 'true';
    }
    
    /**
     * Get maximum file size
     */
    public static function getMaxFileSize() {
        return self::get('MAX_FILE_SIZE', '10MB');
    }
    
    /**
     * Get allowed file types
     */
    public static function getAllowedFileTypes() {
        $types = self::get('ALLOWED_FILE_TYPES', 'pdf,xlsx,xls,jpg,jpeg,png');
        return explode(',', $types);
    }
    
    /**
     * Get session timeout
     */
    public static function getSessionTimeout() {
        return (int) self::get('SESSION_TIMEOUT', 3600);
    }
    
    /**
     * Get encryption key
     */
    public static function getEncryptionKey() {
        return self::get('ENCRYPTION_KEY', 'your_encryption_key_here');
    }
    
    /**
     * Set a configuration value
     */
    public static function set($key, $value) {
        self::loadConfig();
        self::$config[$key] = $value;
    }
    
    /**
     * Save configuration to file
     */
    public static function saveConfig() {
        $config_file = __DIR__ . '/../config.env';
        $content = "# Configuration File\n";
        $content .= "# Generated on " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach (self::$config as $key => $value) {
            $content .= "$key=$value\n";
        }
        
        return file_put_contents($config_file, $content);
    }
    
    /**
     * Update OpenAI API key
     */
    public static function updateOpenAIKey($key) {
        self::set('OPENAI_API_KEY', $key);
        return self::saveConfig();
    }
    
    /**
     * Get all configuration as array
     */
    public static function getAll() {
        self::loadConfig();
        return self::$config;
    }
    
    /**
     * Validate configuration
     */
    public static function validate() {
        $errors = [];
        
        if (!self::hasOpenAIKey()) {
            $errors[] = 'OpenAI API key is not configured';
        }
        
        if (empty(self::getDBHost())) {
            $errors[] = 'Database host is not configured';
        }
        
        if (empty(self::getDBName())) {
            $errors[] = 'Database name is not configured';
        }
        
        if (empty(self::getDBUser())) {
            $errors[] = 'Database user is not configured';
        }
        
        return $errors;
    }
    
    /**
     * Test OpenAI API connection
     */
    public static function testOpenAI() {
        if (!self::hasOpenAIKey()) {
            return ['success' => false, 'message' => 'OpenAI API key not configured'];
        }
        
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get('https://api.openai.com/v1/models', [
                'headers' => [
                    'Authorization' => 'Bearer ' . self::getOpenAIKey(),
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10
            ]);
            
            if ($response->getStatusCode() === 200) {
                return ['success' => true, 'message' => 'OpenAI API connection successful'];
            } else {
                return ['success' => false, 'message' => 'OpenAI API connection failed'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'OpenAI API connection error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Test database connection
     */
    public static function testDatabase() {
        try {
            $host = self::getDBHost();
            $dbname = self::getDBName();
            $user = self::getDBUser();
            $password = self::getDBPassword();
            
            $connection = mysqli_connect($host, $user, $password, $dbname);
            
            if ($connection) {
                mysqli_close($connection);
                return ['success' => true, 'message' => 'Database connection successful'];
            } else {
                return ['success' => false, 'message' => 'Database connection failed'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()];
        }
    }
} 