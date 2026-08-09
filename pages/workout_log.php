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

    <title>Workouts – Fitness Tracker</title>

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

        <h1>Workouts</h1>

        <p class="subtitle">
            Erfasse dein Training und deine Leistung.
        </p>

        <div class="error" id="workout-error"></div>
        <div class="success" id="workout-success"></div>

        <div class="field">
            <label for="workout-title">Titel</label>

            <input
                type="text"
                id="workout-title"
                placeholder="z. B. Beintraining"
            >
        </div>

        <div class="grid-two">
            <div class="field">
                <label for="workout-date">Datum</label>

                <input
                    type="date"
                    id="workout-date"
                    value="<?php echo date('Y-m-d'); ?>"
                >
            </div>

            <div class="field">
                <label for="workout-duration">
                    Dauer in Minuten
                </label>

                <input
                    type="number"
                    id="workout-duration"
                    min="0"
                    placeholder="z. B. 60"
                >
            </div>
        </div>

        <p class="section-label">Übung</p>

        <div class="field">
            <label for="workout-exercise">
                Übung auswählen
            </label>

            <select id="workout-exercise">
                <option value="">Übung auswählen</option>
            </select>
        </div>

        <div class="grid-two">
            <div class="field">
                <label for="workout-sets">Sätze</label>

                <input
                    type="number"
                    id="workout-sets"
                    min="0"
                    placeholder="z. B. 3"
                >
            </div>

            <div class="field">
                <label for="workout-reps">
                    Wiederholungen
                </label>

                <input
                    type="number"
                    id="workout-reps"
                    min="0"
                    placeholder="z. B. 10"
                >
            </div>
        </div>

        <div class="field">
            <label for="workout-weight">
                Gewicht in kg
            </label>

            <input
                type="number"
                id="workout-weight"
                min="0"
                step="0.1"
                placeholder="z. B. 40"
            >
        </div>

        <div class="field">
            <label for="workout-notes">Notizen</label>

            <textarea
                id="workout-notes"
                placeholder="Optionale Notizen zum Training"
            ></textarea>
        </div>

        <div class="workout-form-actions">
            <button type="button" class="btn" id="save-workout">
                Workout speichern
            </button>

            <button
                type="button"
                class="workout-cancel-button"
                id="cancel-workout-edit"
                hidden
            >
                Abbrechen
            </button>
        </div>

        <p class="section-label">Meine Workouts</p>

        <div id="workout-list">
            Workouts werden geladen …
        </div>

    </div>
</div>

<script src="../js/api.js"></script>
<script src="../js/workouts.js"></script>
</body>
</html>
