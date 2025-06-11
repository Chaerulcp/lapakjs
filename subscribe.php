<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    try {
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        
        if (!$email) {
            throw new Exception('Email tidak valid');
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            throw new Exception('Email sudah terdaftar dalam newsletter kami');
        }

        // Insert new subscriber
        $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->execute([$email]);

        $response['success'] = true;
        $response['message'] = 'Terima kasih telah berlangganan newsletter kami!';
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    // If it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // If it's a regular form submission
    if ($response['success']) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?subscribe=success');
    } else {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?subscribe=error&message=' . urlencode($response['message']));
    }
    exit;
}

// If accessed directly without POST
header('Location: index.php');
exit;
