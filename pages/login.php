<?php session_start(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Fitness Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
<div class="page-centered">
  <div class="card-small">
    <div class="logo">
      <?php include '../assets/logo.svg'; ?>
      <span class="logo-text">Fitness Tracker</span>
    </div>
    <h1>Willkommen zurück</h1>
    <p class="subtitle">Meld dich an, um weiterzumachen.</p>
    <div class="error" id="error">
      <i class="ti ti-alert-circle"></i> E-Mail oder Passwort falsch.
    </div>
    <div class="field">
      <label for="email">E-Mail</label>
      <input type="email" id="email" placeholder="name@beispiel.de" />
    </div>
    <div class="field">
      <label for="password">Passwort</label>
      <input type="password" id="password" placeholder="••••••••" />
    </div>
    <div class="forgot">
      <span>Passwort vergessen?</span>
    </div>
    <button class="btn" onclick="handleLogin()">Anmelden</button>
    <div class="divider"><span>oder</span></div>
    <p class="register">
      Noch kein Konto? <a href="register.php">Registrieren</a>
    </p>
  </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>
