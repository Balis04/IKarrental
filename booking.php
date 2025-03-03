<?php
session_start();
require_once 'storage.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$isBookingSuccessful = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carId = isset($_POST['car_id']) ? (int)$_POST['car_id'] : null;
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : null;
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : null;

    if ($carId === null || !$startDate || !$endDate) {
        $isBookingSuccessful = false;
    }

    if (strtotime($startDate) === false || strtotime($endDate) === false || strtotime($startDate) > strtotime($endDate)) {
        $isBookingSuccessful = false;
    }

    $jsonIO = new JsonIO('foglalasok.json');
    $storage = new Storage($jsonIO);

    $carIO = new JsonIO('cars.json');
    $cars = new Storage($carIO);

    $car = $cars->findById($carId);

    $bookings = $storage->findAll();

    foreach ($bookings as $booking) {
        if ($booking['car_id'] == $carId &&
            !(strtotime($endDate) < strtotime($booking['start_date']) || strtotime($startDate) > strtotime($booking['end_date']))) {
            $isBookingSuccessful = false;
        }
    }

    $newBooking = [
        'car_id' => $carId,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'user_email' => $_SESSION['user']['email']
    ];

    if($isBookingSuccessful){
        $storage->add($newBooking);
    }
}
?>


<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foglalás státusza</title>
    <link rel="stylesheet" type="text/css" href="index.css" />
    <style>
        body {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <?php if ($isBookingSuccessful): ?>
            <div class="status-icon">✔</div>
            <h1 class="status-title">Sikeres foglalás!</h1>
            <p class="status-details">
                A(z) <strong><?php echo $car['brand'] . ' ' . $car['model'] ?></strong> sikeresen lefoglalva
                <?php echo $startDate; ?>–<?php echo $endDate; ?> intervallumra.<br>
                Foglalásod státuszát a profiloldaladon követheted nyomon.
            </p>
        <?php else: ?>
            <div class="status-icon" style="background-color: #ff4d4d;">✘</div>
            <h1 class="status-title" style="color: #ff4d4d;">Sikertelen foglalás!</h1>
            <p class="status-details">
                Sajnos a foglalás nem sikerült. Kérjük, próbáld meg később, vagy próbálj meg másik autót foglalni.
            </p>
        <?php endif; ?>
        <a href="profil.php" class="profile-button">Profilom</a>
    </div>
</body>
</html>