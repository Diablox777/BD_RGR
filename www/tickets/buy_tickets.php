<?php
require '/var/www/html/db_connection.php';

// Получаем данные о поездах
$sql_trains = "
    SELECT trains.train_id, trains.train_number, departure_stations.station_name AS departure_station, arrival_stations.station_name AS arrival_station
    FROM trains
    JOIN stations AS departure_stations ON trains.departure_station_id = departure_stations.station_id
    JOIN stations AS arrival_stations ON trains.arrival_station_id = arrival_stations.station_id
";
$result_trains = $conn->query($sql_trains);

$trains = [];
if ($result_trains->num_rows > 0) {
    while ($row = $result_trains->fetch_assoc()) {
        $trains[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Покупка билетов</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #f7f9fc, #e9eef5);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            width: 100%;
            max-width: 700px;
            background: #ffffff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-button {
            display: block;
            text-align: center;
            padding: 12px 20px;
            background: #6c63ff;
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            margin: 10px auto 20px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #564eea;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-size: 14px;
            color: #34495e;
            font-weight: 600;
        }
        input[type="text"], input[type="email"], select, input[type="number"] {
            width: 100%;
            padding: 14px;
            font-size: 14px;
            border: 1px solid #dcdde1;
            border-radius: 8px;
            background: #f9f9f9;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus, input[type="email"]:focus, select:focus {
            border-color: #6c63ff;
            outline: none;
        }
        input[type="submit"] {
            padding: 14px;
            background: #6c63ff;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background: #564eea;
        }
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 34px;
            margin-top: 10px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 34px;
            transition: 0.4s;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: #fff;
            border-radius: 50%;
            transition: 0.4s;
        }
        input:checked + .slider {
            background-color: #6c63ff;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .message {
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            font-weight: bold;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .error {
            background-color: #fce4ec;
            color: #c62828;
        }
        .seat-selection select {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Покупка билетов</h1>
        <a href="http://localhost" class="back-button">Вернуться назад</a>
        <?php if (empty($trains)): ?>
            <div class="message error">Нет доступных поездов.</div>
        <?php else: ?>
            <form action="process_buy_tickets.php" method="post">
                <label for="wagon_type">Тип вагона:</label>
                <label class="toggle-switch">
                    <input type="checkbox" id="wagon_type_toggle" checked>
                    <span class="slider"></span>
                </label>
                <input type="hidden" name="wagon_type" id="wagon_type" value="Купе">
                <label for="train_id">Выберите поезд:</label>
                <select name="train_id" id="train_id" required onchange="updateSeats(this.value)">
                    <?php foreach ($trains as $train): ?>
                        <option value="<?= $train['train_id'] ?>"><?= $train['departure_station'] ?> - <?= $train['arrival_station'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="customer_name">Ваше ФИО:</label>
                <input type="text" name="customer_name" id="customer_name" required pattern="^(?:[А-ЯЁ][а-яё]*\s?){1,3}$">
                <label for="email">Ваш email:</label>
                <input type="email" name="email" id="email">
                <div id="seats">
                    <label for="seat_type">Тип места:</label>
                    <select id="seat_type" required onchange="updateSeatNumbers(this.value, occupiedSeats)">
                        <option value="">Выберите тип места</option>
                        <option value="Верхние">Верхние</option>
                        <option value="Нижние">Нижние</option>
                        <option value="Боковые">Боковые</option>
                    </select>
                    <label for="seat_number">Выберите место:</label>
                    <select id="seat_number" name="seat_number" required>
                        <option value="">Выберите место</option>
                    </select>
                </div>
                <input type="submit" onclick="validateEmail()" value="Купить билет">
            </form>
        <?php endif; ?>
    </div>

    <script>
        function validateEmail() {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const email = document.getElementById('email').value;

    if (!emailRegex.test(email)) {
        alert('Неверный формат email');
        return false;
    }
}

        let occupiedSeats = [];

        function updateSeats(train_id) {
            const wagonType = document.getElementById('wagon_type').value;
            fetch(`get_seats.php?train_id=${train_id}&wagon_type=${wagonType}`)
                .then(response => response.json())
                .then(data => {
                    occupiedSeats = data;
                    updateSeatNumbers(document.getElementById('seat_type').value, occupiedSeats);
                });
        }

        function updateSeatNumbers(seatType, occupiedSeats) {
            const seatNumberSelect = document.getElementById('seat_number');
            seatNumberSelect.innerHTML = '<option value="">Выберите место</option>';
            let start, end;
            const wagonType = document.getElementById('wagon_type').value;

            if (seatType === 'Верхние' || seatType === 'Нижние') {
                start = seatType === 'Верхние' ? 2 : 1;
                end = 36;
            } else if (seatType === 'Боковые') {
                start = 37;
                end = 54;
            }

            for (let i = start; i <= end; i += 2) {
                if (!occupiedSeats.includes(i)) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.text = `Место ${i}`;
                    seatNumberSelect.appendChild(option);
                }
            }
        }

        document.getElementById('wagon_type_toggle').addEventListener('change', function() {
            document.getElementById('wagon_type').value = this.checked ? 'Купе' : 'Плацкарт';
            updateSeats(document.getElementById('train_id').value);
        });

        document.getElementById('train_id').addEventListener('change', function() {
            updateSeats(this.value);
        });

        document.getElementById('seat_type').addEventListener('change', function() {
            updateSeatNumbers(this.value, occupiedSeats);
        });

        updateSeats(document.getElementById('train_id').value);
    </script>
</body>
</html>
