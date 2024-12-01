<?php
require '/var/www/html/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $station_name = $_POST['station_name'];
    $location = $_POST['location'];

    // Проверка, что поля не пустые
    if (!empty($station_name) && !empty($location)) {
        // Регулярное выражение для проверки, что вводится только слово с большой буквы
        $regex = '/^[A-Z][a-z]*$/';

        if (preg_match($regex, $station_name) && preg_match($regex, $location)) {
            $sql = "INSERT INTO stations (station_name, location) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $station_name, $location);

            if ($stmt->execute()) {
                $message = "<div class='message success'>New station added successfully</div>";
            } else {
                $message = "<div class='message error'>Error: " . $stmt->error . "</div>";
            }

            $stmt->close();
        } else {
            $message = "<div class='message error'>Both fields must start with a capital letter and contain only letters!</div>";
        }
    } else {
        $message = "<div class='message error'>Both fields are required!</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Station</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #eceff1, #90caf9);
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 500px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            padding: 30px;
            text-align: center;
        }
        h1 {
            color: #1e88e5;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #1976d2;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #0d47a1;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            text-align: left;
            font-weight: bold;
            color: #455a64;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #cfd8dc;
            border-radius: 8px;
            background-color: #f5f5f5;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus {
            border-color: #1e88e5;
            background-color: #ffffff;
        }
        input[type="submit"] {
            padding: 12px;
            background-color: #43a047;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background-color: #2e7d32;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add a New Station</h1>
        <a href="http://localhost" class="back-button">Back to Home</a>
        <form method="post">
            <label for="station_name">Station Name</label>
            <input type="text" id="station_name" name="station_name" required>
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required>
            <input type="submit" value="Add Station">
        </form>

        <?php
        if (isset($message)) {
            echo $message;
        }
        ?>
    </div>
</body>
</html>