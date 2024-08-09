<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files (adjust the path as necessary)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$servername = "localhost"; // Replace with your server name
$username = "root";        // Replace with your MySQL username
$password = "";            // Replace with your MySQL password
$dbname = "contact_form";  // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Name = htmlspecialchars(trim($_POST['Name']));
    $Email = htmlspecialchars(trim($_POST['Email']));
    $Mobile = htmlspecialchars(trim($_POST['Mobile']));
    $Message = htmlspecialchars(trim($_POST['Message']));

    // Validate inputs (basic example)
    if (empty($Name) || empty($Email) || empty($Message)) {
        http_response_code(400);
        echo 'Please fill all required fields.';
        exit;
    }

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO submissions (name, email, mobile, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $Name, $Email, $Mobile, $Message);

    // Attempt to execute the prepared statement
    if ($stmt->execute()) {
        // Email settings with PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.gmail.com';               // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = '';            // SMTP username
            $mail->Password   = '';                         // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;           // Enable TLS encryption
            $mail->Port       = 587;                                    // TCP port to connect to

            // Enable verbose debug output
            $mail->SMTPDebug = 2; // Debug output level (0 = off, 1 = client messages, 2 = client and server messages)

            // Recipients
            $mail->setFrom($Email, $Name);                              // Set the "From" address
            $mail->addAddress('');             // Add a recipient

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = "Contact Form Submission from $Name";
            $mail->Body    = "Name: $Name<br>Email: $Email<br>Phone Number: $Mobile<br><br>Message:<br>" . nl2br($Message);
            $mail->AltBody = "Name: $Name\nEmail: $Email\nPhone Number: $Mobile\n\nMessage:\n$Message";

            $mail->send();
            http_response_code(200);
            echo 'Email sent successfully and data stored in database.';
        } catch (Exception $e) {
            http_response_code(500);
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        http_response_code(500);
        echo 'Database insertion failed.';
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo 'Method not allowed.';
}
?>
