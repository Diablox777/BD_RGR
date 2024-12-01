<?php
require '/var/www/html/db_connection.php';

// Обработка запроса на удаление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_customer_id'])) {
    $customer_id = $_POST['delete_customer_id'];

    // Начало транзакции
    $conn->begin_transaction();

    try {
        // Удаление билетов, связанных с покупателем
        $sql_delete_tickets = "DELETE FROM tickets WHERE customer_id = ?";
        $stmt_delete_tickets = $conn->prepare($sql_delete_tickets);
        $stmt_delete_tickets->bind_param("i", $customer_id);
        $stmt_delete_tickets->execute();
        $stmt_delete_tickets->close();

        // Удаление покупателя
        $sql_delete_customer = "DELETE FROM customers WHERE customer_id = ?";
        $stmt_delete_customer = $conn->prepare($sql_delete_customer);
        $stmt_delete_customer->bind_param("i", $customer_id);
        
        if ($stmt_delete_customer->execute()) {
            $conn->commit(); // Фиксация транзакции
            $message = "<div class='message success'>Покупатель и связанные билеты успешно удалены.</div>";
        } else {
            throw new Exception($stmt_delete_customer->error);
        }

        $stmt_delete_customer->close();
    } catch (Exception $e) {
        $conn->rollback(); // Откат транзакции в случае ошибки
        $message = "<div class='message error'>Ошибка: " . $e->getMessage() . "</div>";
    }
}

// Обработка запроса на обновление
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer_id'])) {
    $customer_id = $_POST['update_customer_id'];
    $customer_name = $_POST['customer_name'];
    $email = $_POST['email'];

    $sql = "UPDATE customers SET customer_name = ?, email = ? WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $customer_name, $email, $customer_id);
    
    if ($stmt->execute()) {
        $message = "<div class='message success'>Покупатель успешно обновлен.</div>";
    } else {
        $message = "<div class='message error'>Ошибка: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}

$sql = "SELECT * FROM customers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers List</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #ff5722;
            --text-color: #333;
            --background-color: #f9f9f9;
            --font-family: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: var(--text-color);
            margin-bottom: 1rem;
            font-size: 1.75rem;
            font-weight: 600;
        }

        .back-button {
            display: block;
            margin: 0 auto 1rem;
            background-color: var(--secondary-color);
            color: white;
            text-align: center;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }

        .back-button:hover {
            background-color: #e64a19;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .delete-button, .update-button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .delete-button {
            background-color: var(--secondary-color);
        }

        .delete-button:hover {
            background-color: #e64a19;
        }

        .update-button {
            background-color: var(--primary-color);
        }

        .update-button:hover {
            background-color: #388e3c;
        }

        .message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-align: center;
        }

        .success {
            background-color: #d9fcd9;
            color: var(--primary-color);
        }

        .error {
            background-color: #ffd7d7;
            color: var(--secondary-color);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            margin: 10% auto;
            padding: 2rem;
            position: relative;
        }

        .modal-content h2 {
            margin-bottom: 1rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .modal-content label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .modal-content input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }

        .modal-content button {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-color);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #388e3c;
        }

        .modal-content .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Список покупателей</h1>
        <a href="http://localhost" class="back-button">Вернуться назад</a>
        <?php if (isset($message)) echo $message; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ФИО покупателя</th>
                    <th>Email</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["customer_id"] . "</td>";
                        echo "<td>" . $row["customer_name"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>
                                <form method='post' style='display:inline;'>
                                    <input type='hidden' name='delete_customer_id' value='" . $row["customer_id"] . "'>
                                    <button type='submit' class='delete-button'>Удалить</button>
                                </form>
                                <button class='update-button' onclick=\"openModal(" . $row["customer_id"] . ", '" . $row["customer_name"] . "', '" . $row["email"] . "')\">Редактировать</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align: center;'>Покупатели не найдены</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="updateModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Редактировать покупателя</h2>
            <form method="post">
                <input type="hidden" id="update_customer_id" name="update_customer_id">
                <label for="customer_name">ФИО покупателя:</label>
                <input type="text" id="customer_name" name="customer_name" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <button type="submit">Сохранить изменения</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(customer_id, customer_name, email) {
            document.getElementById('update_customer_id').value = customer_id;
            document.getElementById('customer_name').value = customer_name;
            document.getElementById('email').value = email;
            document.getElementById('updateModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('updateModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target === document.getElementById('updateModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
