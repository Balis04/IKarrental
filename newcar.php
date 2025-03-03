<?php
session_start();
require_once 'storage.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['admin'])) {
    header("Location: loginadmin.php");
    exit();
}

$jsonIO = new JsonIO('cars.json'); 
$storage = new Storage($jsonIO);

$errors = [];
$success = false;
$car = null;

$id = $_GET['id'] ?? null;

if ($id) {
    $car = $storage->findById($id);
    if (!$car) {
        die("Az autó azonosítója ($id) nem található.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $passengers = intval($_POST['passengers'] ?? 0);
    $transmission = trim($_POST['transmission'] ?? '');
    $fuel_type = trim($_POST['fuel_type'] ?? '');
    $daily_price_huf = intval($_POST['daily_price_huf'] ?? 0);
    $image = trim($_POST['image'] ?? '');
    $available_dates = isset($_POST['available_dates']) ? explode(',', trim($_POST['available_dates'])) : [];

    if (empty($brand)) $errors[] = 'A márka megadása kötelező.';
    if (empty($model)) $errors[] = 'A modell megadása kötelező.';
    if (empty($year)) $errors[] = 'Az évjárat megadása kötelező.';
    if ($passengers <= 0) $errors[] = 'A férőhelynek 1 vagy nagyobb számnak kell lennie.';
    if (!in_array($transmission, ['Manual', 'Automatic'])) $errors[] = 'A váltó típusa csak "Manual" vagy "Automatic" lehet.';
    if (empty($fuel_type)) $errors[] = 'Az üzemanyag megadása kötelező.';
    if ($daily_price_huf <= 0) $errors[] = 'Az árnak pozitív számnak kell lennie.';
    if (empty($image)) $errors[] = 'Az autó képének URL-je kötelező.';
    if (empty($available_dates)) $errors[] = 'Legalább egy elérhető dátumot meg kell adni.';

    if (empty($errors)) {
        $newCar = [
            'id' => uniqid(),
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'passengers' => $passengers,
            'transmission' => $transmission,
            'fuel_type' => $fuel_type,
            'daily_price_huf' => $daily_price_huf,
            'image' => $image,
            'available_dates' => $available_dates
        ];
        if ($id) {
            // Szerkesztés
            try {
                $updatedCar = [
                    'id' => $id, 
                    'brand' => $brand,
                    'model' => $model,
                    'year' => $year,
                    'passengers' => $passengers,
                    'transmission' => $transmission,
                    'fuel_type' => $fuel_type,
                    'daily_price_huf' => $daily_price_huf,
                    'image' => $image,
                    'available_dates' => $available_dates
                ];
                $storage->update($id, $updatedCar);
                $success = true;
            } catch (Exception $e) {
                $errors[] = "Hiba a mentés során: " . $e->getMessage();
            }
        } else{
            $storage->add($newCar); 
            $success = true;
        }

        
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin panel</title>
    <link rel="stylesheet" type="text/css" href="index.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1c1c1c;
            color: #fff;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <a href="admin.php"><h1>Admin panel</h1></a>
        <div class="actions">
            <a href="logout.php">Kijelentkezés</a>
        </div>
    </header>
    <h1><?= $id ? 'Autó szerkesztése' : 'Új autó hozzáadása' ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <p>Az autó sikeresen hozzáadva!</p>
        </div>
    <?php endif; ?>
    <?php if($id) : ?>
    <form class="newcar" method="POST" action="">
        <input type="hidden" name="id" value="<?= htmlspecialchars($car['id'] ?? '') ?>">

        <label for="brand">Márka:</label>
        <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($car['brand'] ?? '') ?>">

        <label for="model">Modell:</label>
        <input type="text" id="model" name="model" value="<?= htmlspecialchars($car['model'] ?? '') ?>">

        <label for="year">Évjárat:</label>
        <input type="text" id="year" name="year" value="<?= htmlspecialchars($car['year'] ?? '') ?>">

        <label for="passengers">Férőhely:</label>
        <input type="number" id="passengers" name="passengers" min="1" value="<?= htmlspecialchars($car['passengers'] ?? '') ?>">

        <label for="transmission">Váltó típusa:</label>
        <select id="transmission" name="transmission">
            <option value="Manual" <?= (isset($car['transmission']) && $car['transmission'] === 'Manual') ? 'selected' : '' ?>>Manuális</option>
            <option value="Automatic" <?= (isset($car['transmission']) && $car['transmission'] === 'Automatic') ? 'selected' : '' ?>>Automata</option>
        </select>

        <label for="fuel_type">Üzemanyag:</label>
        <input type="text" id="fuel_type" name="fuel_type" value="<?= htmlspecialchars($car['fuel_type'] ?? '') ?>">

        <label for="daily_price_huf">Napi ár (HUF):</label>
        <input type="number" id="daily_price_huf" name="daily_price_huf" min="1" value="<?= htmlspecialchars($car['daily_price_huf'] ?? '') ?>">

        <label for="image">Kép URL:</label>
        <input type="text" id="image" name="image" value="<?= htmlspecialchars($car['image'] ?? '') ?>">

        <label for="available_dates">Elérhető dátumok (vesszővel elválasztva):</label>
        <textarea id="available_dates" name="available_dates"><?= htmlspecialchars(implode(',', $car['available_dates'] ?? [])) ?></textarea>

        <button type="submit"><?= $id ? 'Frissítés' : 'Mentés' ?></button>
    </form>
    <?php endif; ?>

    <?php if(!$id) : ?>
    <form class="newcar" method="POST" action="">
        <label for="brand">Márka:</label>
        <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>">

        <label for="model">Modell:</label>
        <input type="text" id="model" name="model" value="<?= htmlspecialchars($_POST['model'] ?? '') ?>">

        <label for="year">Évjárat:</label>
        <input type="text" id="year" name="year" value="<?= htmlspecialchars($_POST['year'] ?? '') ?>">

        <label for="passengers">Férőhely:</label>
        <input type="number" id="passengers" name="passengers" min="1" value="<?= htmlspecialchars($_POST['passengers'] ?? '') ?>">

        <label for="transmission">Váltó típusa:</label>
        <select id="transmission" name="transmission">
            <option value="Manual" <?= (isset($_POST['transmission']) && $_POST['transmission'] === 'Manual') ? 'selected' : '' ?>>Manuális</option>
            <option value="Automatic" <?= (isset($_POST['transmission']) && $_POST['transmission'] === 'Automatic') ? 'selected' : '' ?>>Automata</option>
        </select>

        <label for="fuel_type">Üzemanyag:</label>
        <input type="text" id="fuel_type" name="fuel_type" value="<?= htmlspecialchars($_POST['fuel_type'] ?? '') ?>">

        <label for="daily_price_huf">Napi ár (HUF):</label>
        <input type="number" id="daily_price_huf" name="daily_price_huf" min="1" value="<?= htmlspecialchars($_POST['daily_price_huf'] ?? '') ?>">

        <label for="image">Kép URL:</label>
        <input type="text" id="image" name="image" value="<?= htmlspecialchars($_POST['image'] ?? '') ?>">

        <label for="available_dates">Elérhető dátumok (vesszővel elválasztva):</label>
        <textarea id="available_dates" name="available_dates"><?= htmlspecialchars($_POST['available_dates'] ?? '') ?></textarea>

        <button type="submit">Mentés</button>
    </form> 
    <?php endif; ?>
</body>
</html>
