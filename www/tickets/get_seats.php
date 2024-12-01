<?php
require '/var/www/html/db_connection.php';

if (isset($_GET['train_id']) && isset($_GET['wagon_type'])) {
    $train_id = $_GET['train_id'];
    $wagon_type = $_GET['wagon_type'];

    $sql_occupied_seats = "SELECT seat_number FROM tickets WHERE train_id = ? AND wagon_type = ?";
    $stmt_occupied_seats = $conn->prepare($sql_occupied_seats);

    if ($stmt_occupied_seats === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }

    $stmt_occupied_seats->bind_param("is", $train_id, $wagon_type);
    $stmt_occupied_seats->execute();
    $result_occupied_seats = $stmt_occupied_seats->get_result();

    $occupied_seats = [];
    while ($row = $result_occupied_seats->fetch_assoc()) {
        $occupied_seats[] = $row['seat_number'];
    }

    echo json_encode($occupied_seats);
}

$conn->close();
?>