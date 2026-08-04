<?php session_start(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren – Fitness Tracker</title>
    <!-- Stylesheet -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>

<div class="page">
  <div class="card">

    <!-- Logo -->
    <div class="logo">
      <?php include '../assets/logo.svg'; ?>
      <span class="logo-text">Fitness Tracker</span>
    </div>

    <h1>Konto erstellen</h1>
    <p class="subtitle">Starte jetzt mit deinem Training.</p>

    <!-- Fehlermeldung -->
    <div class="error" id="error"></div>

    <!-- Erfolgsmeldung -->
    <div class="success" id="success"></div>

    <!-- Formular -->
    <div class="field">
      <label for="username">Benutzername</label>
      <input type="text" id="username" placeholder="max_mustermann" />
    </div>

    <div class="field">
      <label for="email">E-Mail</label>
      <input type="email" id="email" placeholder="name@beispiel.de" />
    </div>

    <div class="field">
      <label for="password">Passwort</label>
      <input type="password" id="password" placeholder="••••••••" />
    </div>

    <!-- Registrieren-Button -->
    <button class="btn" onclick="handleRegister()">Registrieren</button>

    <div class="divider"><span>oder</span></div>

    <!-- Weiter zum Login -->
    <p class="register">
      Bereits ein Konto? <a href="login.php">Anmelden</a>
    </p>

  </div>
</div>

<!-- JS -->
<script src="../js/main.js"></script>

</body>
</html>
