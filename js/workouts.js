const workoutsApiUrl =
    '/Webtechnologie-2-SoSe26/api/workouts.php';

const exercisesApiUrl =
    '/Webtechnologie-2-SoSe26/api/exercises.php';

let editingWorkoutId = null;
let defaultWorkoutDate = '';

document.addEventListener('DOMContentLoaded', async () => {
    const saveButton = document.getElementById('save-workout');
    const cancelButton =
        document.getElementById('cancel-workout-edit');

    defaultWorkoutDate =
        document.getElementById('workout-date').value;

    saveButton.addEventListener('click', saveWorkout);
    cancelButton.addEventListener('click', cancelWorkoutEdit);

    await loadExercises();
    await loadWorkouts();
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
    const fields = getWorkoutFormFields();
    const error = document.getElementById('workout-error');
    const success = document.getElementById('workout-success');

    hideWorkoutMessage(error);
    hideWorkoutMessage(success);

    const workoutData = {
        title: fields.title.value.trim(),
        date: fields.date.value,
        duration_minutes:
            Number(fields.duration.value) || 0,
        exercise_id:
            Number(fields.exercise.value) || 0,
        sets: Number(fields.sets.value) || 0,
        reps: Number(fields.reps.value) || 0,
        weight_kg: Number(fields.weight.value) || 0,
        notes: fields.notes.value.trim()
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

    const isEditing = editingWorkoutId !== null;

    if (isEditing) {
        workoutData.id = editingWorkoutId;
    }

    try {
        const data = await apiRequest(workoutsApiUrl, {
            method: isEditing ? 'PATCH' : 'POST',
            body: JSON.stringify(workoutData)
        });

        resetWorkoutForm();
        await loadWorkouts();

        showWorkoutMessage(success, data.message);
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

        const exercises = Array.isArray(workout.exercises)
            ? workout.exercises
            : [];

        exercises.forEach((exercise) => {
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

        const actions = document.createElement('div');
        actions.className = 'workout-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'workout-action-button';
        editButton.textContent = 'Bearbeiten';
        editButton.addEventListener('click', () => {
            startWorkoutEdit(workout);
        });

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className =
            'workout-action-button workout-delete-button';
        deleteButton.textContent = 'Löschen';
        deleteButton.addEventListener('click', () => {
            deleteWorkout(workout);
        });

        actions.append(editButton, deleteButton);
        item.appendChild(actions);

        list.appendChild(item);
    });
}

function startWorkoutEdit(workout) {
    const fields = getWorkoutFormFields();
    const exercise = Array.isArray(workout.exercises)
        ? workout.exercises[0]
        : null;

    editingWorkoutId = Number(workout.id);

    fields.title.value = workout.title || '';
    fields.date.value = workout.date || defaultWorkoutDate;
    fields.duration.value = workout.duration_minutes ?? '';
    fields.notes.value = workout.notes || '';
    fields.exercise.value = exercise ? String(exercise.id) : '';
    fields.sets.value = exercise?.sets ?? '';
    fields.reps.value = exercise?.reps ?? '';
    fields.weight.value = exercise?.weight_kg ?? '';

    document.getElementById('save-workout').textContent =
        'Änderungen speichern';
    document.getElementById('cancel-workout-edit').hidden = false;

    hideWorkoutMessage(document.getElementById('workout-error'));
    hideWorkoutMessage(document.getElementById('workout-success'));

    fields.title.focus();
}

function cancelWorkoutEdit() {
    resetWorkoutForm();
    hideWorkoutMessage(document.getElementById('workout-error'));
    hideWorkoutMessage(document.getElementById('workout-success'));
}

async function deleteWorkout(workout) {
    const confirmed = confirm(
        `Soll das Workout "${workout.title}" wirklich gelöscht werden?`
    );

    if (!confirmed) {
        return;
    }

    const error = document.getElementById('workout-error');
    const success = document.getElementById('workout-success');

    hideWorkoutMessage(error);
    hideWorkoutMessage(success);

    try {
        const data = await apiRequest(workoutsApiUrl, {
            method: 'DELETE',
            body: JSON.stringify({ id: Number(workout.id) })
        });

        if (editingWorkoutId === Number(workout.id)) {
            resetWorkoutForm();
        }

        await loadWorkouts();

        showWorkoutMessage(success, data.message);
    } catch (requestError) {
        showWorkoutMessage(error, requestError.message);
    }
}

function resetWorkoutForm() {
    const fields = getWorkoutFormFields();

    editingWorkoutId = null;

    fields.title.value = '';
    fields.date.value = defaultWorkoutDate;
    fields.duration.value = '';
    fields.exercise.value = '';
    fields.sets.value = '';
    fields.reps.value = '';
    fields.weight.value = '';
    fields.notes.value = '';

    document.getElementById('save-workout').textContent =
        'Workout speichern';
    document.getElementById('cancel-workout-edit').hidden = true;
}

function getWorkoutFormFields() {
    return {
        title: document.getElementById('workout-title'),
        date: document.getElementById('workout-date'),
        duration: document.getElementById('workout-duration'),
        exercise: document.getElementById('workout-exercise'),
        sets: document.getElementById('workout-sets'),
        reps: document.getElementById('workout-reps'),
        weight: document.getElementById('workout-weight'),
        notes: document.getElementById('workout-notes')
    };
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
