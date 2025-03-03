<?php
session_start();
require_once 'storage.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $errors = [];

    if (empty($email) || empty($password)) {
        $errors[] = "Minden mező kitöltése kötelező!";
    } else {
        $jsonIO = new JsonIO('adminuser.json');
        $storage = new Storage($jsonIO);
        $user = $storage->findOne(['email' => $email]);

        if ($user && $password == $user['password']) {
            $_SESSION['user'] = $user;
            header("Location: admin.php");
            exit;
        } else {
            $errors[] = "Hibás e-mail cím vagy jelszó!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
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
    <main>
        <h1 id="page-title">Bejelentkezés</h1>
            <?php if (!empty($errors)): ?>
                <ul id="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <form id="login-form" method="POST">
            <label for="email-input">E-mail cím:</label>
            <input id="email-input" type="email" name="email" placeholder="E-mail cím">
            
            <label for="password-input">Jelszó:</label>
            <input id="password-input" type="password" name="password" placeholder="Jelszó">
            
            <button id="login-button" type="submit">Bejelentkezés</button>
        </form>
    </main>
</body>
</html>
