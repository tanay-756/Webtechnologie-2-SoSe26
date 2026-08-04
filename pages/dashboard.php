<?php
session_start();

// Nicht eingeloggt → zurück zum Login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Fitness Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="page">
  <div class="card">
    <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
    <p class="subtitle">Du bist eingeloggt.</p>
    <a href="login.php"><button class="btn">Ausloggen</button></a>
  </div>
</div>
</body>
</html>
