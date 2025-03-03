<?php
session_start();
require_once 'storage.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['admin'])) {
    header("Location: loginadmin.php");
    exit();
}

try {
    $storage = new Storage(new JsonIO('cars.json'));
} catch (Exception $e) {
    die("Hiba történt: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    $storage->delete($deleteId); 

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$carsData = $storage->findAll();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin oldal</title>
    <link rel="stylesheet" type="text/css" href="index.css" />
</head>
<body>
    <header>
        <a href='admin.php'><h1>Admin Panel</h1></a>
        <div class="actions">
            <a href="newcar.php">Új autó hozzáadása</a>
        </div>
        <div class="actions">
            <a href="admincars.php">Autók</a>
        </div>
        <div class="actions">
            <a href="logout.php">Kijelentkezés</a>
        </div>
    </header>
    <main>
        <h2>Autók listája</h2>
        <div class="car-list">
            <?php if (empty($carsData)): ?>
                <p>Nincs találat a megadott szűrési feltételek alapján.</p>
            <?php else: ?>
                <?php foreach ($carsData as $car): ?>
                    <div class="car">
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                        <h3>
                            <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>
                        </h3>
                        <p><?= htmlspecialchars($car['passengers']) ?> férőhely - <?= htmlspecialchars($car['transmission']) ?></p>
                        <div class="price"><?= htmlspecialchars(number_format($car['daily_price_huf'], 0, '', ' ')) ?> Ft</div>
                        <form method="POST" action="">
                            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($car['id']) ?>">
                            <button type="submit" id="delete">Törlés</button>
                        </form>

                        <a href="newcar.php?id=<?= urlencode($car['id']) ?>"><button id="edit">Szerkesztés</button></a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
