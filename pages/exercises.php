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

    <title>Übungen – Fitness Tracker</title>

    <link rel="stylesheet" href="../css/style.css">
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"
    >
</head>

<body>
<div class="page">
    <div class="card">

        <?php include '../includes/nav.php'; ?>

        <h1>Übungen</h1>
        <p class="subtitle">
            Lege Übungen an und verwende sie später in deinen Workouts.
        </p>

        <div class="error" id="exercise-error"></div>
        <div class="success" id="exercise-success"></div>

        <div class="field">
            <label for="exercise-name">Name</label>

            <input
                type="text"
                id="exercise-name"
                placeholder="z. B. Kniebeugen"
            >
        </div>

        <div class="field">
            <label for="exercise-category">Kategorie</label>

            <select id="exercise-category">
                <option value="">Kategorie auswählen</option>
                <option value="Kraft">Kraft</option>
                <option value="Cardio">Cardio</option>
                <option value="Stretching">Stretching</option>
            </select>
        </div>

        <div class="field">
            <label for="exercise-description">Beschreibung</label>

            <textarea
                id="exercise-description"
                placeholder="Kurze Beschreibung der Übung"
            ></textarea>
        </div>

        <button class="btn" id="save-exercise">
            Übung speichern
        </button>

        <p class="section-label">Vorhandene Übungen</p>

        <div id="exercise-list">
            Übungen werden geladen …
        </div>

    </div>
</div>

<script src="../js/api.js"></script>
<script src="../js/exercises.js"></script>
</body>
</html>