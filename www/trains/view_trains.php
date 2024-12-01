<?php
require '/var/www/html/db_connection.php';

// Обработка запроса на удаление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_train_id'])) {
    $train_id = $_POST['delete_train_id'];

    // Начало транзакции
    $conn->begin_transaction();

    try {
        // Удаление билетов, связанных с поездом
        $sql_delete_tickets = "DELETE FROM tickets WHERE train_id = ?";
        $stmt_delete_tickets = $conn->prepare($sql_delete_tickets);
        $stmt_delete_tickets->bind_param("i", $train_id);
        $stmt_delete_tickets->execute();
        $stmt_delete_tickets->close();

        // Удаление поезда
        $sql_delete_train = "DELETE FROM trains WHERE train_id = ?";
        $stmt_delete_train = $conn->prepare($sql_delete_train);
        $stmt_delete_train->bind_param("i", $train_id);
        
        if ($stmt_delete_train->execute()) {
            $conn->commit(); // Фиксация транзакции
            $message = "<div class='message success'>Train and associated tickets deleted successfully.</div>";
        } else {
            throw new Exception($stmt_delete_train->error);
        }

        $stmt_delete_train->close();
    } catch (Exception $e) {
        $conn->rollback(); // Откат транзакции в случае ошибки
        $message = "<div class='message error'>Error: " . $e->getMessage() . "</div>";
    }
}

// Обработка запроса на обновление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_train_id'])) {
    $train_id = $_POST['update_train_id'];
    $train_number = $_POST['train_number'];
    $departure_station_id = $_POST['departure_station_id'];
    $arrival_station_id = $_POST['arrival_station_id'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];

    $sql = "UPDATE trains SET train_number = ?, departure_station_id = ?, arrival_station_id = ?, departure_time = ?, arrival_time = ? WHERE train_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siissi", $train_number, $departure_station_id, $arrival_station_id, $departure_time, $arrival_time, $train_id);
    
    if ($stmt->execute()) {
        $message = "<div class='message success'>Train updated successfully.</div>";
    } else {
        $message = "<div class='message error'>Error: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}

$sql = "SELECT * FROM trains";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Trains</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .back-button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .delete-button,
        .update-button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        .update-button {
            background-color: #28a745;
            color: white;
        }

        .update-button:hover {
            background-color: #218838;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }

        .modal-content {
            background-color: #fff;
            padding: 30px;
            margin: 10% auto;
            width: 40%;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .modal-content input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .modal-content button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .modal-content button:hover {
            background-color: #0056b3;
        }

        .close {
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 20px;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Список поездов</h1>
        <a href="http://localhost" class="back-button">Вернуться назад</a>

        <?php
        if (isset($message)) {
            echo $message;
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Номер поезда</th>
                    <th>ID Пункта отправления</th>
                    <th>ID Пункта прибытия</th>
                    <th>Время отправления</th>
                    <th>Время прибытия</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["train_id"] . "</td>";
                        echo "<td>" . $row["train_number"] . "</td>";
                        echo "<td>" . $row["departure_station_id"] . "</td>";
                        echo "<td>" . $row["arrival_station_id"] . "</td>";
                        echo "<td>" . $row["departure_time"] . "</td>";
                        echo "<td>" . $row["arrival_time"] . "</td>";
                        echo "<td>
                                <form method='post' style='display:inline;'>
                                    <input type='hidden' name='delete_train_id' value='" . $row["train_id"] . "'>
                                    <button type='submit' class='delete-button'>Удалить</button>
                                </form>
                                <button class='update-button' onclick=\"openModal(" . $row["train_id"] . ", '" . $row["train_number"] . "', " . $row["departure_station_id"] . ", " . $row["arrival_station_id"] . ", '" . $row["departure_time"] . "', '" . $row["arrival_time"] . "')\">Изменить</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Нет данных для отображения.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Modal для обновления данных поезда -->
        <div id="updateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Изменить данные поезда</h2>
                <form id="updateForm" method="post">
                    <input type="hidden" name="update_train_id" id="update_train_id">
                    <input type="text" name="train_number" id="train_number" placeholder="Номер поезда" required>
                    <input type="number" name="departure_station_id" id="departure_station_id" placeholder="ID пункта отправления" required>
                    <input type="number" name="arrival_station_id" id="arrival_station_id" placeholder="ID пункта прибытия" required>
                    <input type="datetime-local" name="departure_time" id="departure_time" placeholder="Время отправления" required>
                    <input type="datetime-local" name="arrival_time" id="arrival_time" placeholder="Время прибытия" required>
                    <button type="submit">Сохранить изменения</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id, trainNumber, departureStationId, arrivalStationId, departureTime, arrivalTime) {
            document.getElementById("update_train_id").value = id;
            document.getElementById("train_number").value = trainNumber;
            document.getElementById("departure_station_id").value = departureStationId;
            document.getElementById("arrival_station_id").value = arrivalStationId;
            document.getElementById("departure_time").value = departureTime;
            document.getElementById("arrival_time").value = arrivalTime;
            document.getElementById("updateModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("updateModal").style.display = "none";
        }
    </script>
</body>
</html>
