<?php
require '/var/www/html/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_id = $_POST['train_id'];
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];
    $seat_number = $_POST['seat_number'];
    $wagon_type = $_POST['wagon_type'];

    // Проверка наличия пользователя в таблице customers
    $sql_check_customer = "SELECT customer_id FROM customers WHERE email = ?";
    $stmt_check_customer = $conn->prepare($sql_check_customer);

    if ($stmt_check_customer === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }

    $stmt_check_customer->bind_param("s", $email);
    $stmt_check_customer->execute();
    $result_check_customer = $stmt_check_customer->get_result();

    if ($result_check_customer->num_rows > 0) {
        $row = $result_check_customer->fetch_assoc();
        $customer_id = $row['customer_id'];
    } else {
        // Добавление нового пользователя в таблицу customers
        $sql_insert_customer = "INSERT INTO customers (customer_name, email) VALUES (?, ?)";
        $stmt_insert_customer = $conn->prepare($sql_insert_customer);

        if ($stmt_insert_customer === false) {
            die("Ошибка подготовки запроса: " . $conn->error);
        }

        $stmt_insert_customer->bind_param("ss", $customer_name, $email);
        $stmt_insert_customer->execute();
        $customer_id = $stmt_insert_customer->insert_id;
    }

    // Генерация случайной цены
    $price = rand(5000, 15000);

    // Добавление билета в таблицу tickets
    $sql_insert_ticket = "INSERT INTO tickets (train_id, customer_id, seat_number, price, wagon_type) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert_ticket = $conn->prepare($sql_insert_ticket);

    if ($stmt_insert_ticket === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }

    $stmt_insert_ticket->bind_param("iiids", $train_id, $customer_id, $seat_number, $price, $wagon_type);

    if ($stmt_insert_ticket->execute()) {
        $message = "<div class='message success'>Билет успешно куплен.</div>";
    } else {
        $message = "<div class='message error'>Ошибка: " . $stmt_insert_ticket->error . "</div>";
    }

    $stmt_insert_ticket->close();
    $stmt_check_customer->close();

    // Проверка на существование $stmt_insert_customer перед закрытием
    if (isset($stmt_insert_customer)) {
        $stmt_insert_customer->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Обработка покупки билетов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
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
        .back-button {
            float: center;
            background-color: #5bc0de;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inherit;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }
        .back-button:hover {
            background-color: #31b0d5;
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="http://localhost" class="back-button">Вернуться назад</a>
        <?php
        if (isset($message)) {
            echo $message;
        }
        ?>
    </div>
</body>
</html>