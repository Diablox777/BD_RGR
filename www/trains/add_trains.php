<?php
require '/var/www/html/db_connection.php';

// Получаем данные из таблицы stations
$sql_stations = "SELECT station_id, station_name FROM stations";
$result_stations = $conn->query($sql_stations);

$stations = [];
if ($result_stations->num_rows > 0) {
    while ($row = $result_stations->fetch_assoc()) {
        $stations[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_number = "42" . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $departure_station_id = $_POST['departure_station_id'];
    $arrival_station_id = $_POST['arrival_station_id'];
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];

    if (!empty($departure_station_id) && !empty($arrival_station_id) && !empty($departure_time) && !empty($arrival_time)) {
        if ($departure_station_id == $arrival_station_id) {
            $message = "<div class='message error'>Отправление и прибытие не могут быть одной и той же станцией!</div>";
        } else {
            $sql = "INSERT INTO trains (train_number, departure_station_id, arrival_station_id, departure_time, arrival_time) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiss", $train_number, $departure_station_id, $arrival_station_id, $departure_time, $arrival_time);
            
            if ($stmt->execute()) {
                $message = "<div class='message success'>Новый поезд успешно добавлен.</div>";
            } else {
                $message = "<div class='message error'>Ошибка: " . $stmt->error . "</div>";
            }
            
            $stmt->close();
        }
    } else {
        $message = "<div class='message error'>Все поля обязательны для заполнения!</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление поезда</title>
    <style>
        /* Основные стили */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f7fc;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            width: 80%;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .container:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2.5rem;
            color: #4e8df7;
            text-align: center;
            margin-bottom: 30px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4e8df7;
            color: white;
            border-radius: 8px;
            font-size: 1.1rem;
            text-align: center;
            text-decoration: none;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #3578e5;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-size: 1.1rem;
            font-weight: 500;
            color: #555;
        }

        select, input[type="text"], input[type="datetime-local"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 2px solid #ddd;
            border-radius: 8px;
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
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .back-button {
                width: 100%;
                text-align: center;
            }

            input[type="submit"], select, input[type="datetime-local"], input[type="text"] {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Добавление поезда</h1>
        <a href="http://localhost" class="back-button">Вернуться назад</a>

        <?php if (count($stations) < 2): ?>
            <div class="message error">Для добавления поезда необходимо хотя бы две станции.</div>
        <?php else: ?>
            <form method="post">
                <label for="departure_station_id">Станция отправления:</label>
                <select id="departure_station_id" name="departure_station_id" required>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= $station['station_id'] ?>"><?= $station['station_name'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="arrival_station_id">Станция прибытия:</label>
                <select id="arrival_station_id" name="arrival_station_id" required>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= $station['station_id'] ?>"><?= $station['station_name'] ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="departure_time">Время отправления:</label>
                <input type="datetime-local" id="departure_time" name="departure_time" required>

                <label for="arrival_time">Время прибытия:</label>
                <input type="datetime-local" id="arrival_time" name="arrival_time" required>

                <input type="submit" value="Добавить поезд">
            </form>
        <?php endif; ?>

        <?php if (isset($message)) echo $message; ?>
    </div>

</body>
</html>
