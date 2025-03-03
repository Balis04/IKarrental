<?php
require_once 'storage.php';

$jsonIO = new JsonIO('cars.json'); 
$storage = new Storage($jsonIO);

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id === null) {
    die('Hibás kérés! Nincs azonosító megadva.');
}

$car = $storage->findById($id);

if ($car === null) {
    die('Nem található ilyen autó.');
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="index.css" />
    <title><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> részletei</title>
</head>
<body>
<header>
    <a href="index.php"><h1>iKarRental</h1></a>
    <div class="actions">
        <?php
        session_start(); 

        if (isset($_SESSION['user'])) { 
            echo '<span>Bejelentkezve mint: ' . $_SESSION['user']['name'] . '</span>'; 
            echo '<a href="logout.php">Kijelentkezés</a>'; 
        } else {
            echo '<a href="login.php">Bejelentkezés</a>'; 
            echo '<a href="register.php">Regisztráció</a>'; 
        }
        ?>
    </div>
    </header>
    <main>
        <h1 id="reszlet">Autó részletei</h1> 
        <div class="car-detail">
            <div class="car-image">
                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
            </div>
            <div class="car-info">
                <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                <div class="price"><?= htmlspecialchars(number_format($car['daily_price_huf'], 0, '', ' ')) ?> Ft / nap</div>
                <p><?= htmlspecialchars($car['passengers']) ?> férőhely</p>
                <p>Évjárat: <?= htmlspecialchars($car['year']) ?></p>
                <p>Váltó típusa: <?= htmlspecialchars($car['transmission']) ?></p>
                <p>Üzemanyag: <?= htmlspecialchars($car['fuel_type']) ?></p>
                <form class="booking-form" method="POST" action="booking.php">
                    <input type="hidden" name="car_id" value="<?= $id ?>">
                    <label for="start_date">Kezdő dátum:</label>
                    <input type="date" id="start_date" name="start_date" required>
                    <label for="end_date">Végző dátum:</label>
                    <input type="date" id="end_date" name="end_date" required><br>
                    <button class="booking-button" type="submit">Foglalás</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
