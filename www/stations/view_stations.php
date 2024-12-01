<?php
require '/var/www/html/db_connection.php';

// Обработка запроса на удаление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_station_id'])) {
    $station_id = $_POST['delete_station_id'];

    $conn->begin_transaction();

    try {
        $sql_delete_tickets = "DELETE t FROM tickets t JOIN trains tr ON t.train_id = tr.train_id WHERE tr.departure_station_id = ? OR tr.arrival_station_id = ?";
        $stmt_delete_tickets = $conn->prepare($sql_delete_tickets);
        $stmt_delete_tickets->bind_param("ii", $station_id, $station_id);
        $stmt_delete_tickets->execute();
        $stmt_delete_tickets->close();

        $sql_delete_trains = "DELETE FROM trains WHERE departure_station_id = ? OR arrival_station_id = ?";
        $stmt_delete_trains = $conn->prepare($sql_delete_trains);
        $stmt_delete_trains->bind_param("ii", $station_id, $station_id);
        $stmt_delete_trains->execute();
        $stmt_delete_trains->close();

        $sql_delete_station = "DELETE FROM stations WHERE station_id = ?";
        $stmt_delete_station = $conn->prepare($sql_delete_station);
        $stmt_delete_station->bind_param("i", $station_id);

        if ($stmt_delete_station->execute()) {
            $conn->commit();
            $message = "<div class='message success'>Station and associated trains and tickets deleted successfully.</div>";
        } else {
            throw new Exception($stmt_delete_station->error);
        }

        $stmt_delete_station->close();
    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='message error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Обработка запроса на обновление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_station_id'])) {
    $station_id = $_POST['update_station_id'];
    $station_name = $_POST['station_name'];
    $location = $_POST['location'];

    $sql = "UPDATE stations SET station_name = ?, location = ? WHERE station_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $station_name, $location, $station_id);

    if ($stmt->execute()) {
        $message = "<div class='message success'>Station updated successfully.</div>";
    } else {
        $message = "<div class='message error'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

$sql = "SELECT * FROM stations";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Stations</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(120deg, #d9e4f5, #f5e0dc);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            width: 100%;
            background-color: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background: #5bc0de;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #31b0d5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fdfdfd;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #555;
        }
        th {
            background: #f7f7f7;
            font-weight: 700;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .no-stations {
            text-align: center;
            padding: 20px;
            color: #888;
            font-style: italic;
        }
        .button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }
        .delete-button {
            background: #e74c3c;
        }
        .delete-button:hover {
            background: #c0392b;
        }
        .update-button {
            background: #3498db;
        }
        .update-button:hover {
            background: #2980b9;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-weight: bold;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .error {
            background: #fce4ec;
            color: #c62828;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            margin: 10% auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .modal-content h2 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #333;
        }
        .modal-content input {
            width: calc(100% - 16px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        .modal-content button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .modal-content .close {
            font-size: 24px;
            color: #aaa;
            float: right;
            cursor: pointer;
        }
        .modal-content .close:hover {
            color: #333;
        }
        .modal-content .save-button {
            background: #43a047;
            color: #fff;
        }
        .modal-content .save-button:hover {
            background: #388e3c;
        }
        .modal-content .cancel-button {
            background: #e74c3c;
            color: #fff;
            margin-top: 10px;
        }
        .modal-content .cancel-button:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Stations</h1>
        <a href="http://localhost" class="back-button">Back to Home</a>
        <?php if (isset($message)) echo $message; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Station Name</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['station_id']}</td>
                        <td>{$row['station_name']}</td>
                        <td>{$row['location']}</td>
                        <td>
                            <form method='post' style='display:inline;'>
                                <input type='hidden' name='delete_station_id' value='{$row['station_id']}'>
                                <button class='button delete-button'>Delete</button>
                            </form>
                            <button class='button update-button' onclick=\"openModal({$row['station_id']}, '{$row['station_name']}', '{$row['location']}')\">Edit</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-stations'>No stations found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Station</h2>
            <form method="post">
                <input type="hidden" id="update_station_id" name="update_station_id">
                <input type="text" id="station_name" name="station_name" placeholder="Station Name" required>
                <input type="text" id="location" name="location" placeholder="Location" required>
                <button type="submit" class="save-button">Save</button>
                <button type="button" class="cancel-button" onclick="closeModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, name, location) {
            document.getElementById('update_station_id').value = id;
            document.getElementById('station_name').value = name;
            document.getElementById('location').value = location;
            document.getElementById('updateModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        window.onclick = function (event) {
            if (event.target === document.getElementById('updateModal')) {
                closeModal();
            }
        };
    </script>
</body>
</html>
<?php $conn->close(); ?>
