<?php
require '/var/www/html/db_connection.php';

// Обработка запроса на удаление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket_id'])) {
    $ticket_id = $_POST['delete_ticket_id'];

    // Начало транзакции
    $conn->begin_transaction();

    try {
        // Получаем информацию о билете
        $sql_get_ticket = "SELECT customer_id FROM tickets WHERE ticket_id = ?";
        $stmt_get_ticket = $conn->prepare($sql_get_ticket);
        $stmt_get_ticket->bind_param("i", $ticket_id);
        $stmt_get_ticket->execute();
        $result_get_ticket = $stmt_get_ticket->get_result();
        $ticket_info = $result_get_ticket->fetch_assoc();
        $stmt_get_ticket->close();

        if (!$ticket_info) {
            throw new Exception("Билет не найден.");
        }

        $customer_id = $ticket_info['customer_id'];

        // Удаление билета
        $sql_delete_ticket = "DELETE FROM tickets WHERE ticket_id = ?";
        $stmt_delete_ticket = $conn->prepare($sql_delete_ticket);
        $stmt_delete_ticket->bind_param("i", $ticket_id);
        $stmt_delete_ticket->execute();
        $stmt_delete_ticket->close();

        // Проверяем, остались ли у покупателя другие билеты
        $sql_check_tickets = "SELECT COUNT(*) AS ticket_count FROM tickets WHERE customer_id = ?";
        $stmt_check_tickets = $conn->prepare($sql_check_tickets);
        $stmt_check_tickets->bind_param("i", $customer_id);
        $stmt_check_tickets->execute();
        $result_check_tickets = $stmt_check_tickets->get_result();
        $ticket_count = $result_check_tickets->fetch_assoc()['ticket_count'];
        $stmt_check_tickets->close();

        if ($ticket_count == 0) {
            // Удаляем покупателя, если у него больше нет билетов
            $sql_delete_customer = "DELETE FROM customers WHERE customer_id = ?";
            $stmt_delete_customer = $conn->prepare($sql_delete_customer);
            $stmt_delete_customer->bind_param("i", $customer_id);
            $stmt_delete_customer->execute();
            $stmt_delete_customer->close();
        }

        $conn->commit(); // Фиксация транзакции
        $message = "<div class='message success'>Билет и связанная информация удалены успешно.</div>";
    } catch (Exception $e) {
        $conn->rollback(); // Откат транзакции в случае ошибки
        $message = "<div class='message error'>Ошибка: " . $e->getMessage() . "</div>";
    }
}

// Обработка запроса на обновление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket_id'])) {
    $ticket_id = $_POST['update_ticket_id'];
    $train_id = $_POST['train_id'];
    $seat_number = $_POST['seat_number'];
    $price = $_POST['price'];

    $sql = "UPDATE tickets SET train_id = ?, seat_number = ?, price = ? WHERE ticket_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidi", $train_id, $seat_number, $price, $ticket_id);
    
    if ($stmt->execute()) {
        $message = "<div class='message success'>Билет успешно обновлен.</div>";
    } else {
        $message = "<div class='message error'>Ошибка: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}

$sql = "SELECT t.ticket_id, t.train_id, t.seat_number, t.price, c.customer_name, c.email 
        FROM tickets t
        JOIN customers c ON t.customer_id = c.customer_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список билетов</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f1f5f9;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 20px;
        }
        .back-button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        .message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
            font-size: 1.1rem;
        }
        td {
            font-size: 1rem;
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .actions {
            display: flex;
            justify-content: space-between;
        }
        .delete-button, .update-button {
            background: none;
            border: none;
            cursor: pointer;
            color: #007bff;
            font-size: 1.2rem;
        }
        .delete-button:hover, .update-button:hover {
            color: #dc3545;
        }
        .delete-button::after {
            content: "🗑️";
        }
        .update-button::after {
            content: "✏️";
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            width: 400px;
            position: relative;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            color: #aaa;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .modal input, .modal button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .modal button {
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
        }
        .modal button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Список билетов</h1>
        <a href="http://localhost" class="back-button">Вернуться назад</a>
        <?php
        if (isset($message)) {
            echo $message;
        }
        ?>
        <table>
            <tr>
                <th>ID</th>
                <th>ID поезда</th>
                <th>Номер места</th>
                <th>Цена</th>
                <th>ФИО покупателя</th>
                <th>Email</th>
                <th>Действия</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['ticket_id']}</td>
                        <td>{$row['train_id']}</td>
                        <td>{$row['seat_number']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['email']}</td>
                        <td class='actions'>
                            <button class='update-button' onclick='openUpdateModal({$row['ticket_id']})'></button>
                            <form action='' method='POST' style='display:inline-block'>
                                <input type='hidden' name='delete_ticket_id' value='{$row['ticket_id']}'>
                                <button class='delete-button' type='submit'></button>
                            </form>
                        </td>
                    </tr>";
                }
            }
            ?>
        </table>
    </div>

    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeUpdateModal()">&times;</span>
            <h2>Обновить билет</h2>
            <form method="POST">
                <input type="hidden" name="update_ticket_id" id="update_ticket_id">
                <label for="train_id">ID поезда</label>
                <input type="number" name="train_id" id="train_id" required>
                <label for="seat_number">Номер места</label>
                <input type="number" name="seat_number" id="seat_number" required>
                <label for="price">Цена</label>
                <input type="number" name="price" id="price" step="0.01" required>
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(ticket_id, train_id, seat_number, price) {
            document.getElementById('update_ticket_id').value = ticket_id;
            document.getElementById('train_id').value = train_id;
            document.getElementById('seat_number').value = seat_number;
            document.getElementById('price').value = price;
            document.getElementById('updateModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        // Закрытие модального окна при клике вне его
        window.onclick = function(event) {
            if (event.target == document.getElementById('updateModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>