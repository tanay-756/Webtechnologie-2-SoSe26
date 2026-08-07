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

    <div class="error" id="dashboard-error" role="alert"></div>

    <section class="dashboard-stats" aria-label="Trainingsstatistiken">
      <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">
          <i class="ti ti-barbell" aria-hidden="true"></i>
          Workouts insgesamt
        </span>
        <strong class="dashboard-stat-value" id="total-workouts">—</strong>
      </article>

      <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">
          <i class="ti ti-clock" aria-hidden="true"></i>
          Trainingszeit insgesamt
        </span>
        <strong class="dashboard-stat-value" id="total-training-minutes">—</strong>
        <span class="dashboard-stat-unit">Minuten</span>
      </article>

      <article class="dashboard-stat-card">
        <span class="dashboard-stat-label">
          <i class="ti ti-target" aria-hidden="true"></i>
          Aktive Ziele
        </span>
        <strong class="dashboard-stat-value" id="active-goals-count">—</strong>
      </article>
    </section>

    <div class="dashboard-content-grid">
      <section class="dashboard-panel" aria-labelledby="recent-workouts-title">
        <h2 id="recent-workouts-title">Letzte Workouts</h2>
        <div class="dashboard-list" id="recent-workouts" aria-live="polite">
          <p class="dashboard-empty">Workouts werden geladen …</p>
        </div>
      </section>

      <section class="dashboard-panel" aria-labelledby="active-goals-title">
        <h2 id="active-goals-title">Aktive Ziele</h2>
        <div class="dashboard-list" id="active-goals" aria-live="polite">
          <p class="dashboard-empty">Ziele werden geladen …</p>
        </div>
      </section>
    </div>

  </div>
</div>
<script src="../js/api.js"></script>
<script src="../js/dashboard.js"></script>
</body>
</html>
