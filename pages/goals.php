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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ziele – Fitness Tracker</title>

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

        <h1>Ziele</h1>

        <p class="subtitle">
            Lege persönliche Fitnessziele an und verfolge deinen Fortschritt.
        </p>

        <div class="error" id="goal-error"></div>
        <div class="success" id="goal-success"></div>

        <div class="field">
            <label for="goal-description">
                Beschreibung
            </label>

            <input
                type="text"
                id="goal-description"
                placeholder="z. B. 100 kg beim Kniebeugen"
            >
        </div>

        <div class="grid-two">
            <div class="field">
                <label for="goal-target">
                    Zielwert
                </label>

                <input
                    type="number"
                    id="goal-target"
                    min="0"
                    step="0.1"
                    placeholder="z. B. 100"
                >
            </div>

            <div class="field">
                <label for="goal-current">
                    Aktueller Wert
                </label>

                <input
                    type="number"
                    id="goal-current"
                    min="0"
                    step="0.1"
                    value="0"
                >
            </div>
        </div>

        <div class="grid-two">
            <div class="field">
                <label for="goal-unit">Einheit</label>

                <input
                    type="text"
                    id="goal-unit"
                    placeholder="z. B. kg, Workouts oder km"
                >
            </div>

            <div class="field">
                <label for="goal-deadline">
                    Zieldatum
                </label>

                <input
                    type="date"
                    id="goal-deadline"
                >
            </div>
        </div>

        <button class="btn" id="save-goal">
            Ziel speichern
        </button>

        <p class="section-label">Meine Ziele</p>

        <div id="goal-list">
            Ziele werden geladen …
        </div>

    </div>
</div>

<script src="../js/api.js"></script>
<script src="../js/goals.js"></script>
</body>
</html>