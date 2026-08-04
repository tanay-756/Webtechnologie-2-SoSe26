<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Fitness Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
<div class="page">
  <div class="card">

    <?php include '../includes/nav.php'; ?>

    <h1>Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
    <p class="subtitle">Hier siehst du deine letzten Workouts und Ziele.</p>

  </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>
