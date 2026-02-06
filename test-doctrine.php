#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

echo "=== Doctrine 3.x Validation Test ===\n\n";

// Test 1: Check Doctrine is loaded
echo "1. Doctrine ORM Version: ";
try {
    echo class_exists('Doctrine\ORM\Version') ? 'Loaded' : 'NOT LOADED';
    if (class_exists('Doctrine\ORM\Version')) {
        $version = \Doctrine\ORM\Version::VERSION;
        echo " ($version)";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test 2: MySQL Connection
echo "2. MySQL Connection: ";
try {
    $host = $_ENV['DATABASE_HOST'] ?? 'mysql';
    $user = $_ENV['DATABASE_USER'] ?? 'root';
    $pass = $_ENV['DATABASE_PASSWORD'] ?? 'api';
    $db = $_ENV['DATABASE_NAME'] ?? 'api';

    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_error) {
        echo "FAILED - " . $mysqli->connect_error . "\n";
    } else {
        echo "OK\n";
        $mysqli->close();
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// Test 3: Check for Domain layer Doctrine imports
echo "3. Domain Layer Clean: ";
$credentialsFile = __DIR__ . '/src/App/User/Domain/ValueObject/Auth/Credentials.php';
$content = file_get_contents($credentialsFile);
if (strpos($content, 'use Doctrine') !== false) {
    echo "FAILED - Doctrine imports found!\n";
} else {
    echo "OK - No Doctrine imports\n";
}

echo "\n=== Test Complete ===\n";
