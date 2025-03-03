<?php
require_once 'storage.php';

$jsonIO = new JsonIO('cars.json'); 
$storage = new Storage($jsonIO);

$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : null;
$transmission = isset($_GET['transmission']) ? $_GET['transmission'] : null;
$price_min = isset($_GET['price_min']) ? (int)$_GET['price_min'] : null;
$price_max = isset($_GET['price_max']) ? (int)$_GET['price_max'] : null;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;

$cars = $storage->findMany(function ($car) use ($passengers, $transmission, $price_min, $price_max, $date_from, $date_to) {

    if ($passengers && $car['passengers'] < $passengers) {
        return false;
    }
    
    if ($transmission && $car['transmission'] !== $transmission) {
        return false;
    }
    
    if ($price_min && $car['daily_price_huf'] < $price_min) {
        return false;
    }
    if ($price_max && $car['daily_price_huf'] > $price_max) {
        return false;
    }
    
    if ($date_from && $date_to) {
        $available_dates = array_filter($car['available_dates'], function($date) {
            return !empty($date); // Csak nem üres dátumokat vizsgálunk
        });
    
        $date_from_obj = DateTime::createFromFormat('Y-m-d', $date_from);
        $date_to_obj = DateTime::createFromFormat('Y-m-d', $date_to);
    
        $is_available = false;
    
        foreach ($available_dates as $date) {
            $date_obj = DateTime::createFromFormat('Y.m.d', $date); // Az elérhető dátum formátuma "Y.m.d"
            if ($date_obj && $date_obj >= $date_from_obj && $date_obj <= $date_to_obj) {
                $is_available = true;
                break;
            }
        }
    
        if (!$is_available) {
            return false;
        }
    }
    

    return true;
});
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autóbérlés</title>
    <link rel="stylesheet" type="text/css" href="index.css" />
</head>
<body>
<header>
    <h1>iKarRental</h1>
    <div class="actions">
        <?php
        session_start(); 

        if (isset($_SESSION['user'])) { 
            echo '<span><a href="profil.php">' . $_SESSION['user']['name'] . '</a></span>'; 
            echo ' |<a href="logout.php">Kijelentkezés</a>';
        } else {
            echo '<a href="login.php">Bejelentkezés</a>'; 
            echo '<a href="register.php">Regisztráció</a>'; 
        }
        ?>
    </div>
    </header>
    <main>
    <form method="GET" action="index.php" class="filter">
            <div>
                <label for="passengers">Férőhely:</label>
                <input type="number" id="passengers" name="passengers" min="1" max="20" value="<?= htmlspecialchars($passengers) ?>">
            </div>
            <div>
                <label for="transmission">Váltó típusa:</label>
                <select id="transmission" name="transmission">
                    <option value="">Bármely</option>
                    <option value="Manual" <?= $transmission === "Manual" ? "selected" : "" ?>>Manuális</option>
                    <option value="Automatic" <?= $transmission === "Automatic" ? "selected" : "" ?>>Automata</option>
                </select>
            </div>
            <div>
                <label for="price_min">Ár (tól):</label>
                <input type="number" id="price_min" name="price_min" min="0" value="<?= htmlspecialchars($price_min) ?>">
            </div>
            <div>
                <label for="price_max">Ár (ig):</label>
                <input type="number" id="price_max" name="price_max" min="0" value="<?= htmlspecialchars($price_max) ?>">
            </div>
            <div>
                <label for="date_from">Dátum (tól):</label>
                <input type="date" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div>
                <label for="date_to">Dátum (ig):</label>
                <input type="date" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <button type="submit">Szűrés</button>
        </form>
        <div class="car-list">
            <?php if (empty($cars)): ?>
                <p>Nincs találat a megadott szűrési feltételek alapján.</p>
            <?php else: ?>
                <?php foreach ($cars as $car): ?>
                    <div class="car">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                        <h3>
                            <a href="auto.php?id=<?= htmlspecialchars($car['id']) ?>" style="text-decoration: none; color: #ffa500;">
                                <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>
                            </a>
                        </h3>
                        <p><?= htmlspecialchars($car['passengers']) ?> férőhely - <?= htmlspecialchars($car['transmission']) ?></p>
                        <div class="price"><?= htmlspecialchars(number_format($car['daily_price_huf'], 0, '', ' ')) ?> Ft</div>
                        <a href="auto.php?id=<?= htmlspecialchars($car['id']) ?>"><button>Foglalás</button></a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
