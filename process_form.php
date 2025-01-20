<?php
// Database connection settings
$servername = "localhost";  // or your database host
$username = "root";         // your MySQL username
$password = "";             // your MySQL password
$dbname = "your_database";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];    // Get the email
  $password = $_POST['password'];  // Get the password
  $checkbox = isset($_POST['check']) ? 1 : 0;  // Check if the checkbox is checked
  $file = $_FILES['file'];      // Handle file upload

  // Ensure the upload directory exists and is writable
  $upload_dir = 'uploads/';
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);  // Create the directory with full permissions if it doesn't exist
  }

  // File upload validation
  $allowed_types = [
      'image/jpeg',
      'image/png',
      'application/pdf',
      'application/vnd.ms-powerpoint', // For older PowerPoint files
      'application/vnd.openxmlformats-officedocument.presentationml.presentation' // For newer PowerPoint files
  ];
  
  if (in_array($file['type'], $allowed_types) && $file['size'] < 2000000) { // Limit file size to 2MB
    // Handle file upload (example: move file to "uploads" folder)
    $file_name = basename($file['name']);
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
      // Insert form data into MySQL database using prepared statements
      $stmt = $conn->prepare("INSERT INTO users (email, password, file_path, check_box) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("sssi", $email, $password, $file_path, $checkbox);

      if ($stmt->execute()) {
          // Output JavaScript for alerts and redirection
          echo '<script>
                  alert("Thanks for participating!");
                  alert("Your data has been successfully submitted.");
                  window.location.href = "index.html";
                  document.getElementById("uploadStatus").style.display = "block"; // Show the upload status button
                </script>';
      } else {
        echo '<script>alert("Error: ' . $stmt->error . '");</script>';
      }
      $stmt->close();
    } else {
      echo '<script>alert("Error uploading file.");</script>';
    }
  } else {
    echo '<script>alert("Invalid file type or size exceeds limit.");</script>';
  }
}

$conn->close();
?>
