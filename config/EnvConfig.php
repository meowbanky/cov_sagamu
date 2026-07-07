<?php

class EnvConfig {
    private static $config = null;

    /**
     * Path to the environment file. Consolidated onto a single .env (the same
     * file phpdotenv loads); falls back to the legacy config.env if .env is
     * absent, so nothing breaks mid-migration.
     */
    private static function envFilePath() {
        $env = __DIR__ . '/../.env';
        $legacy = __DIR__ . '/../config.env';
        return file_exists($env) ? $env : $legacy;
    }

    /**
     * Load configuration from the .env file
     */
    private static function loadConfig() {
        if (self::$config === null) {
            self::$config = [];

            $config_file = self::envFilePath();
            
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
     * Get Google Maps API key
     */
    public static function getGoogleMapsApiKey() {
        return self::get('GOOGLE_MAPS_API_KEY', '');
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
     * Get API secret key
     */
    public static function getAPISecret() {
        return self::get('API_SECRET', '');
    }
    
    /**
     * Get email configuration
     */
    public static function getMailHost() {
        return self::get('MAIL_HOST', 'localhost');
    }
    
    public static function getMailPort() {
        return (int) self::get('MAIL_PORT', 587);
    }
    
    public static function getMailUsername() {
        return self::get('MAIL_USERNAME', '');
    }
    
    public static function getMailPassword() {
        return self::get('MAIL_PASSWORD', '');
    }
    
    public static function getMailEncryption() {
        return self::get('MAIL_ENCRYPTION', 'tls');
    }
    
    public static function getMailFromAddress() {
        return self::get('MAIL_FROM_ADDRESS', 'noreply@localhost');
    }
    
    public static function getMailFromName() {
        return self::get('MAIL_FROM_NAME', 'Cooperative Society');
    }
    
    /**
     * Get all email configuration as array
     */
    public static function getMailConfig() {
        return [
            'host' => self::getMailHost(),
            'port' => self::getMailPort(),
            'username' => self::getMailUsername(),
            'password' => self::getMailPassword(),
            'encryption' => self::getMailEncryption(),
            'from_address' => self::getMailFromAddress(),
            'from_name' => self::getMailFromName(),
        ];
    }
    
    /**
     * Check if email is configured
     */
    public static function hasMailConfig() {
        $host = self::getMailHost();
        $username = self::getMailUsername();
        return !empty($host) && $host !== 'localhost' && !empty($username);
    }
    
    /**
     * Set a configuration value
     */
    public static function set($key, $value) {
        self::loadConfig();
        self::$config[$key] = $value;
    }
    
    /**
     * Save configuration to file.
     *
     * Surgical, in-place update: existing lines are replaced only when their
     * value actually changed, new keys are appended, and all comments,
     * ordering and untouched lines are preserved. (The previous version
     * rewrote the whole file and dropped every comment.)
     */
    public static function saveConfig() {
        self::loadConfig();
        $config_file = self::envFilePath();

        $lines = file_exists($config_file)
            ? file($config_file, FILE_IGNORE_NEW_LINES)
            : [];
        $seen = [];

        $unquote = function ($v) {
            $v = trim($v);
            if (strlen($v) >= 2 &&
                (($v[0] === '"' && substr($v, -1) === '"') ||
                 ($v[0] === "'" && substr($v, -1) === "'"))) {
                $v = substr($v, 1, -1);
            }
            return $v;
        };

        // Encode a value so phpdotenv can parse it. Bare values containing
        // whitespace, comment or quote characters must be double-quoted,
        // otherwise phpdotenv throws "unexpected whitespace" and fatals.
        $encode = function ($v) {
            $v = (string) $v;
            if ($v === '' || preg_match('/[\s#"\']/', $v)) {
                return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $v) . '"';
            }
            return $v;
        };

        foreach ($lines as $i => $line) {
            $trimmed = ltrim($line);
            if ($trimmed === '' || $trimmed[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            $key = trim(explode('=', $line, 2)[0]);
            if (!array_key_exists($key, self::$config)) {
                continue;
            }
            $seen[$key] = true;
            $currentValue = $unquote(explode('=', $line, 2)[1]);
            // Only rewrite the line when the value genuinely changed, so
            // untouched entries keep their exact original formatting.
            if ((string) $currentValue !== (string) self::$config[$key]) {
                $lines[$i] = $key . '=' . $encode(self::$config[$key]);
            }
        }

        // Append any brand-new keys that were not already in the file.
        foreach (self::$config as $key => $value) {
            if (empty($seen[$key])) {
                $lines[] = $key . '=' . $encode($value);
            }
        }

        return file_put_contents($config_file, implode("\n", $lines) . "\n", LOCK_EX);
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