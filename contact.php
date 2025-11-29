<?php
// contact.php
header('Content-Type: text/plain; charset=utf-8');

// Read POST
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    echo "All fields are required.";
    exit;
}

// Try to insert into MySQL if configured
$DB_HOST = ''; // e.g. '127.0.0.1'
$DB_NAME = ''; // e.g. 'portfolio'
$DB_USER = ''; // e.g. 'root'
$DB_PASS = ''; // db password

if ($DB_HOST && $DB_NAME && $DB_USER) {
    try {
        $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message) VALUES (:name, :email, :message)");
        $stmt->execute(['name'=>$name, 'email'=>$email, 'message'=>$message]);
        echo "Thanks {$name}, your message has been saved.";
        exit;
    } catch (Exception $e) {
        // if DB fails, fall back to CSV file below
        error_log("DB error: " . $e->getMessage());
    }
}

// Fallback: append to a CSV file (contacts.csv)
$csvFile = __DIR__ . '/contacts.csv';
$now = date('Y-m-d H:i:s');
$line = [$now, $name, $email, str_replace(["\r","\n"], [' ',' '], $message)];
$fp = fopen($csvFile, 'a');
if ($fp) {
    fputcsv($fp, $line);
    fclose($fp);
    echo "Thanks {$name}, your message has been recorded (contacts.csv).";
    exit;
} else {
    http_response_code(500);
    echo "Unable to save message.";
    exit;
}
