const workoutsApiUrl =
    '/Webtechnologie-2-SoSe26/api/workouts.php';

const exercisesApiUrl =
    '/Webtechnologie-2-SoSe26/api/exercises.php';

document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('save-workout');

    saveButton.addEventListener('click', saveWorkout);

    loadExercises();
    loadWorkouts();
});

async function loadExercises() {
    const select = document.getElementById('workout-exercise');
    const error = document.getElementById('workout-error');

    try {
        const data = await apiRequest(exercisesApiUrl);

        select.replaceChildren();

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Übung auswählen';

        select.appendChild(defaultOption);

        data.exercises.forEach((exercise) => {
            const option = document.createElement('option');

            option.value = exercise.id;
            option.textContent =
                `${exercise.name} (${exercise.category})`;

            select.appendChild(option);
        });

        if (data.exercises.length === 0) {
            showWorkoutMessage(
                error,
                'Lege zuerst mindestens eine Übung an.'
            );
        }
    } catch (requestError) {
        showWorkoutMessage(error, requestError.message);
    }
}

async function loadWorkouts() {
    const list = document.getElementById('workout-list');
    const error = document.getElementById('workout-error');

    list.textContent = 'Workouts werden geladen …';

    try {
        const data = await apiRequest(workoutsApiUrl);

        renderWorkouts(data.workouts);
    } catch (requestError) {
        list.textContent = '';
        showWorkoutMessage(error, requestError.message);
    }
}

async function saveWorkout() {
    const titleInput =
        document.getElementById('workout-title');
    const dateInput =
        document.getElementById('workout-date');
    const durationInput =
        document.getElementById('workout-duration');
    const exerciseInput =
        document.getElementById('workout-exercise');
    const setsInput =
        document.getElementById('workout-sets');
    const repsInput =
        document.getElementById('workout-reps');
    const weightInput =
        document.getElementById('workout-weight');
    const notesInput =
        document.getElementById('workout-notes');

    const error = document.getElementById('workout-error');
    const success = document.getElementById('workout-success');

    hideWorkoutMessage(error);
    hideWorkoutMessage(success);

    const workoutData = {
        title: titleInput.value.trim(),
        date: dateInput.value,
        duration_minutes:
            Number(durationInput.value) || 0,
        exercise_id:
            Number(exerciseInput.value) || 0,
        sets: Number(setsInput.value) || 0,
        reps: Number(repsInput.value) || 0,
        weight_kg: Number(weightInput.value) || 0,
        notes: notesInput.value.trim()
    };

    if (
        workoutData.title === '' ||
        workoutData.date === '' ||
        workoutData.exercise_id === 0
    ) {
        showWorkoutMessage(
            error,
            'Bitte Titel, Datum und Übung ausfüllen.'
        );
        return;
    }

    try {
        const data = await apiRequest(workoutsApiUrl, {
            method: 'POST',
            body: JSON.stringify(workoutData)
        });

        showWorkoutMessage(success, data.message);

        titleInput.value = '';
        durationInput.value = '';
        exerciseInput.value = '';
        setsInput.value = '';
        repsInput.value = '';
        weightInput.value = '';
        notesInput.value = '';

        await loadWorkouts();
    } catch (requestError) {
        showWorkoutMessage(error, requestError.message);
    }
}

function renderWorkouts(workouts) {
    const list = document.getElementById('workout-list');

    list.replaceChildren();

    if (workouts.length === 0) {
        const message = document.createElement('p');
        message.textContent = 'Noch keine Workouts vorhanden.';

        list.appendChild(message);
        return;
    }

    workouts.forEach((workout) => {
        const item = document.createElement('div');
        item.className = 'workout-item';

        const title = document.createElement('h3');
        title.textContent =
            `${workout.title} – ${formatWorkoutDate(workout.date)}`;

        const duration = document.createElement('p');
        duration.textContent =
            `Dauer: ${workout.duration_minutes || 0} Minuten`;

        item.append(title, duration);

        workout.exercises.forEach((exercise) => {
            const exerciseText = document.createElement('p');

            exerciseText.textContent =
                `${exercise.name}: ` +
                `${exercise.sets || 0} Sätze, ` +
                `${exercise.reps || 0} Wiederholungen, ` +
                `${exercise.weight_kg || 0} kg`;

            item.appendChild(exerciseText);
        });

        if (workout.notes) {
            const notes = document.createElement('p');
            notes.textContent = `Notizen: ${workout.notes}`;

            item.appendChild(notes);
        }

        list.appendChild(item);
    });
}

function formatWorkoutDate(date) {
    const parts = date.split('-');

    return `${parts[2]}.${parts[1]}.${parts[0]}`;
}

function showWorkoutMessage(element, message) {
    element.textContent = message;
    element.style.display = 'block';
}

function hideWorkoutMessage(element) {
    element.textContent = '';
    element.style.display = 'none';
}