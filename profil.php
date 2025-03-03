<?php
include_once "storage.php";
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];

$jsonIO = new JsonIO('foglalasok.json');
$bookings = new Storage($jsonIO);

$carIO = new JsonIO('cars.json');
$cars = new Storage($carIO);

$userBookings = $bookings->findMany(function($booking) use ($user) {
    return $booking['user_email'] === $user['email'];
});

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Foglalásaim</title>
    <link rel="stylesheet" type="text/css" href="index.css" />
</head>
<body>
<header>
    <a href="index.php"><h1>iKarRental</h1></a>
    <div class="actions">
        <a href="logout.php">Kijelentkezés</a>
    </div>
</header>
<h1>Üdvözlünk, <?= htmlspecialchars($user['name']) ?>!</h1>
<div class="car-list">
<?php if (empty($userBookings)): ?>
    <p>Még nincs foglalásod.</p>
<?php else: ?>
    <?php foreach ($userBookings as $booking): ?>
        <?php 
        $car = $cars->findMany(function($c) use ($booking) {
            return $c['id'] == $booking['car_id'];
        });
        $car = reset($car); 
        ?>
        <?php if ($car): ?>
            <div class="car">
                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                <p><?= htmlspecialchars($car['transmission']) . ' - ' . htmlspecialchars($car['passengers']) ?></p>
                <p> <?= htmlspecialchars($booking['start_date']) ?> - <?= htmlspecialchars($booking['end_date']) ?>
                </p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php endif; ?>
        </div>
</body>
</html>