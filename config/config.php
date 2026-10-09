<?php
// config/config.php
// Global Application Settings

// Dynamic Base URL detection for absolute paths
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$folder = 'campuspulse'; // Change this if your folder name is different in htdocs
define('BASE_URL', $protocol . "://" . $domain . "/" . $folder . "/");

// Absolute path to root directory (useful for includes)
define('ROOT_PATH', dirname(__DIR__) . '/');

// Application Details
define('APP_NAME', 'CampusPulse');
define('COLLEGE_NAME', 'College of Engineering'); // Example College

// Security
define('ADMIN_REGISTRATION_KEY', 'CAMPUSPULSE2026'); // Required to register as an admin

// Smart Priority Thresholds
define('SIMILARITY_THRESHOLD', 70); // Percentage
?>