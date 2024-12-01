<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Station RGR</title>
    <link rel="shortcut icon" href="/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="/assets/css/bulma.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .hero {
            background-color: #4e8df7;
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        .hero .title {
            font-size: 3rem;
            font-weight: bold;
        }

        .hero .subtitle {
            font-size: 1.5rem;
            margin-top: 10px;
        }

        .section {
            padding: 40px 20px;
        }

        .columns {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .column {
            flex: 1;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: all 0.3s ease-in-out;
        }

        .column:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .column h3 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
        }

        .content ul {
            list-style-type: none;
            padding: 0;
        }

        .content ul li {
            margin: 15px 0;
            font-size: 1.1rem;
            color: #555;
        }

        .content ul li a {
            color: #4e8df7;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .content ul li a:hover {
            color: #2c6de3;
        }

        footer {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: #4e8df7;
        }

        .message {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .message.success {
            border-color: #4e8df7;
            color: #4e8df7;
        }

        .message.error {
            border-color: #d9534f;
            color: #d9534f;
        }

    </style>
</head>
<body>
    <section class="hero is-medium is-info is-bold">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">РГР по Базам Данных</h1>
                <h2 class="subtitle">Шаблон личного проекта по биллингу и построению путей сообщения электропоездов</h2>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="columns">
                <!-- Left Column: Environment -->
                <div class="column">
                    <h3 class="title is-3 has-text-centered">Environment</h3>
                    <div class="content">
                        <ul>
                            <li><?= apache_get_version(); ?></li>
                            <li>PHP <?= phpversion(); ?></li>
                            <li>
                                <?php
                                $link = mysqli_connect("database", "root", $_ENV['MYSQL_ROOT_PASSWORD'], null);

                                // check connection
                                if (mysqli_connect_errno()) {
                                    printf("MySQL connection failed: %s", mysqli_connect_error());
                                } else {
                                    // print server version
                                    printf("MySQL Server %s", mysqli_get_server_info($link));
                                }
                                // close connection
                                mysqli_close($link);
                                ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column: Quick Links -->
                <div class="column">
                    <h3 class="title is-3 has-text-centered">Quick Links</h3>
                    <div class="content">
                        <ul>
                            <li><a href="monitor.php">Запросы</a></li>
                            <li><a href="stations/add_stations.php">Добавить Станции</a></li>
                            <li><a href="stations/view_stations.php">Посмотреть Станции</a></li>
                            <li><a href="trains/add_trains.php">Добавить Поезд</a></li>
                            <li><a href="trains/view_trains.php">Посмотреть Поезд</a></li>
                            <li><a href="tickets/buy_tickets.php">Купить Билет</a></li>
                            <li><a href="tickets/view_tickets.php">Посмотреть Билеты</a></li>
                            <li><a href="customers/view_customers.php">Посмотреть Покупателей</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="content has-text-centered">
            <p>&copy; 2024 Station RGR | All Rights Reserved</p>
        </div>
    </footer>
</body>
</html>
