<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../classes/User.php';
$user = new User();
$profile = $user->getProfile($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil – Fitness Tracker</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
</head>
<body>
<div class="page">
  <div class="card">

    <?php include '../includes/nav.php'; ?>

    <div class="avatar">
      <?php echo strtoupper(substr($profile['username'], 0, 2)); ?>
    </div>
    <h1>Mein Profil</h1>
    <p class="subtitle">Körperdaten und Einstellungen</p>

    <div class="error" id="error"></div>
    <div class="success" id="success"></div>

    <p class="section-label">Accountdaten</p>
    <div class="field">
      <label>Benutzername</label>
      <input type="text" value="<?php echo htmlspecialchars($profile['username']); ?>" disabled />
    </div>
    <div class="field">
      <label>E-Mail</label>
      <input type="email" value="<?php echo htmlspecialchars($profile['email']); ?>" disabled />
    </div>

    <p class="section-label">Körperdaten</p>
    <div class="grid-two">
      <div class="field">
        <label>Gewicht (kg)</label>
        <input type="number" id="weight" min="20" max="500" step="0.1" placeholder="z.B. 65"
          value="<?php echo $profile['weight_kg'] ?? ''; ?>"
          oninput="calcBMI()" />
      </div>
      <div class="field">
        <label>Größe (cm)</label>
        <input type="number" id="height" min="50" max="300" step="0.1" placeholder="z.B. 170"
          value="<?php echo $profile['height_cm'] ?? ''; ?>"
          oninput="calcBMI()" />
      </div>
    </div>

    <div class="bmi-card">
      <span class="bmi-label"><i class="ti ti-activity"></i> BMI</span>
      <span class="bmi-value" id="bmi">
        <?php
          if ($profile['weight_kg'] && $profile['height_cm']) {
            $h = $profile['height_cm'] / 100;
            echo round($profile['weight_kg'] / ($h * $h), 1);
          } else {
            echo '—';
          }
        ?>
      </span>
    </div>

    <button class="btn" onclick="saveProfile()">Speichern</button>

  </div>
</div>
<script src="../js/main.js"></script>
</body>
</html>
