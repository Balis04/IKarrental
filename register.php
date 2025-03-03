<?php
session_start();
require_once 'storage.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $errors = [];

    if (empty($name)) $errors[] = "Teljes név megadása kötelező!";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Érvényes e-mail cím megadása kötelező!";
    if (empty($password) || strlen($password) < 6) $errors[] = "A jelszónak legalább 6 karakter hosszúnak kell lennie!";

    if (empty($errors)) {
        $jsonIO = new JsonIO('users.json'); 
        $storage = new Storage($jsonIO);

        $existingUsers = $storage->findAll();
        $emailExists = array_filter($existingUsers, function ($user) use ($email) {
            return $user['email'] === $email;
        });

        if (!empty($emailExists)) {
            $errors[] = "Ez az e-mail cím már használatban van!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $storage->add([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
            ]);
            header("Location: login.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Regisztráció</title>
    <link rel="stylesheet" type="text/css" href="index.css" />
</head>
<body>
<header>
    <h1>iKarRental</h1>
    <div class="actions">
        <?php

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
    <h1 class="middle">Regisztráció</h1>
    <?php if (!empty($errors)): ?>
        <ul id="error-messages">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form id="registration-form" method="POST">
        <label for="name-input">Teljes név:</label>
        <input id="name-input" type="text" name="name" placeholder="Teljes név">
        
        <label for="email-input">E-mail cím:</label>
        <input id="email-input" type="email" name="email" placeholder="E-mail cím">
        
        <label for="password-input">Jelszó:</label>
        <input id="password-input" type="password" name="password" placeholder="Jelszó">
        
        <button id="submit-button" type="submit">Regisztráció</button>
    </form>
</body>
</html>
