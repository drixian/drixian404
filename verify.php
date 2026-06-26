<?php
// 1. Start a secure session to remember that this user passed the test
session_start();

// 2. Only allow POST requests (form submissions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Grab the reCAPTCHA token sent by the form
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // CRITICAL: Replace this placeholder with your actual Google Secret Key!
    $secret_key = '6Le7zDYtAAAAAAjUtrs-xLkGW3hlaYubRgYcWasC'; 

    // 3. Prepare the payload to verify with Google's API
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret'   => $secret_key,
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] // Optional, but helps track bot networks
    ];

    // Format the data for a standard POST request
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    // 4. Send the request to Google
    $context  = stream_context_create($options);
    $verify = @file_get_contents($url, false, $context);
    
    // Decode the JSON response from Google
    $captcha_success = json_decode($verify);

    // 5. Evaluate the result
    if ($captcha_success && $captcha_success->success) {
        // Human confirmed! Save it to their session
        $_SESSION['verified_human'] = true;
        
        // Send them straight into the main site
        header('Location: main.php');
        exit();
    } else {
        // Captcha failed or was ignored
        echo "<h3>Verification failed. Please ensure you checked the 'I am not a robot' box.</h3>";
        echo "<p><a href='index.html'>Click here to try again</a></p>";
    }
} else {
    // If someone tries to access drixian.org/verify.php directly, boot them back to the gate
    header('Location: index.html');
    exit();
}
?>
