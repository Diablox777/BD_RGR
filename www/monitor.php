<?php
require '/var/www/html/db_connection.php';

// Получаем список станций
$sql_stations = "SELECT station_name FROM stations";
$result_stations = $conn->query($sql_stations);

$stations = [];
if ($result_stations->num_rows > 0) {
    while ($row = $result_stations->fetch_assoc()) {
        $stations[] = $row['station_name'];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мониторинг</title>
    <style>
        /* Основные стили */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f8fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1, h2 {
            color: #4e8df7;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        h1 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .back-button {
            background-color: #4e8df7;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 8px;
            display: block;
            text-align: center;
            width: 200px;
            margin: 0 auto 30px auto;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #3578e5;
        }

        form {
            margin-top: 30px;
        }

        form h2 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 1rem;
            font-weight: 500;
            color: #555;
        }

        select, input[type="text"], input[type="datetime-local"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
            color: #333;
            background-color: #f5f6f8;
            transition: border-color 0.3s ease;
        }

        select:focus, input[type="text"]:focus, input[type="datetime-local"]:focus {
            border-color: #4e8df7;
            outline: none;
        }

        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }

        .message {
            padding: 15px;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
        }

        .message.success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .message.error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
            }

            .back-button {
                width: 100%;
            }

            form h2 {
                font-size: 1.2rem;
            }

            label {
                font-size: 0.9rem;
            }

            input[type="submit"] {
                font-size: 1rem;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h1>Мониторинг</h1>
        <a href="http://localhost" class="back-button">Вернуться назад</a>

        <!-- Форма для запроса 1 -->
        <form method="post" action="">
            <h2>Запрос 1: Поезда после определенной даты</h2>
            <label for="departure_time">Выберите дату отправления:</label>
            <input type="datetime-local" id="departure_time" name="departure_time" required>
            <input type="hidden" name="query" value="1">
            <input type="submit" value="Выполнить запрос">
        </form>

        <!-- Форма для запроса 2 -->
        <form method="post" action="">
            <h2>Запрос 2: Поезда через выбранную станцию</h2>
            <label for="station">Выберите станцию:</label>
            <select id="station" name="station" required>
                <option value="">Выберите станцию</option>
                <?php foreach ($stations as $station): ?>
                    <option value="<?= $station ?>"><?= $station ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="query" value="2">
            <input type="submit" value="Выполнить запрос">
        </form>

        <!-- Форма для запроса 3 -->
        <form method="post" action="">
            <h2>Запрос 3: Поезд с определенным номером</h2>
            <label for="train_number">Номер поезда:</label>
            <input type="text" id="train_number" name="train_number" required>
            <input type="hidden" name="query" value="3">
            <input type="submit" value="Выполнить запрос">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $query = $_POST['query'];

            switch ($query) {
                case '1':
                    $departure_time = $_POST['departure_time'];
                    $sql = "SELECT train_number, departure_time 
                            FROM trains 
                            WHERE departure_time > '$departure_time';";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        echo "<h2>Результат запроса 1:</h2>";
                        while($row = $result->fetch_assoc()) {
                            echo "Поезд: " . $row["train_number"] . ", Отправление: " . $row["departure_time"] . "<br>";
                        }
                    } else {
                        echo "<div class='message error'>Нет данных для отображения.</div>";
                    }
                    break;

                case '2':
                    $station_name = $_POST['station'];
                    $sql = "SELECT tr.train_number, tr.departure_time, tr.arrival_time 
                            FROM trains tr 
                            JOIN stations ds ON tr.departure_station_id = ds.station_id 
                            JOIN stations ts ON tr.arrival_station_id = ts.station_id 
                            WHERE ds.station_name = '$station_name' OR ts.station_name = '$station_name';";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        echo "<h2>Результат запроса 2:</h2>";
                        while($row = $result->fetch_assoc()) {
                            echo "Поезд: " . $row["train_number"] . ", Отправление: " . $row["departure_time"] . ", Прибытие: " . $row["arrival_time"] . "<br>";
                        }
                    } else {
                        echo "<div class='message error'>Нет данных для отображения.</div>";
                    }
                    break;

                case '3':
                    $train_number = $_POST['train_number'];
                    $sql = "SELECT t.ticket_id, t.seat_number, t.price, c.customer_name
                            FROM tickets t 
                            JOIN trains tr ON t.train_id = tr.train_id 
                            JOIN customers c ON t.customer_id = c.customer_id 
                            WHERE tr.train_number = '$train_number';";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        echo "<h2>Результат запроса 3:</h2>";
                        while($row = $result->fetch_assoc()) {
                            echo "Билет ID: " . $row["ticket_id"] . ", Место: " . $row["seat_number"] . ", Цена: " . $row["price"] . ", Покупатель: " . $row["customer_name"] . "<br>";
                        }
                    } else {
                        echo "<div class='message error'>Нет данных для отображения.</div>";
                    }
                    break;

                default:
                    echo "<div class='message error'>Неверный запрос.</div>";
                    break;
            }

            $conn->close();
        }
        ?>
    </div>
</body>
</html>
