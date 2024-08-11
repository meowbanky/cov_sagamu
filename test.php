<?php

phpinfo();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the Composer autoload file
require __DIR__ . '/vendor/autoload.php';

// Check if the TCPDF class is available
if (class_exists('TCPDF')) {
    echo 'TCPDF class is available.';
} else {
    echo 'TCPDF class not found.';
}
