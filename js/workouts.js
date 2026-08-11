const workoutsApiUrl =
    '/Webtechnologie-2-SoSe26/api/workouts.php';

const exercisesApiUrl =
    '/Webtechnologie-2-SoSe26/api/exercises.php';

let editingWorkoutId = null;
let defaultWorkoutDate = '';
let exerciseCatalog = [];

document.addEventListener('DOMContentLoaded', async () => {
    const saveButton = document.getElementById('save-workout');
    const cancelButton =
        document.getElementById('cancel-workout-edit');
    const addExerciseButton =
        document.getElementById('add-workout-exercise');

    defaultWorkoutDate =
        document.getElementById('workout-date').value;

    saveButton.addEventListener('click', saveWorkout);
    cancelButton.addEventListener('click', cancelWorkoutEdit);
    addExerciseButton.addEventListener('click', () => {
        addWorkoutExerciseBlock();
    });

    addWorkoutExerciseBlock();
    await loadExercises();
    await loadWorkouts();
});

async function loadExercises() {
    const error = document.getElementById('workout-error');

    try {
        const data = await apiRequest(exercisesApiUrl);

        exerciseCatalog = Array.isArray(data.exercises)
            ? data.exercises
            : [];

        updateWorkoutExerciseSelects();

        if (exerciseCatalog.length === 0) {
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
        exercises: getWorkoutExercisesFromForm(),
        notes: fields.notes.value.trim()
    };

    if (
        workoutData.title === '' ||
        workoutData.date === '' ||
        workoutData.exercises.length === 0 ||
        workoutData.exercises.some(
            (exercise) => exercise.exercise_id === 0
        )
    ) {
        showWorkoutMessage(
            error,
            'Bitte Titel, Datum und jede Übung ausfüllen.'
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

    list.classList.add('entity-list');
    list.replaceChildren();

    if (workouts.length === 0) {
        const message = document.createElement('p');
        message.className = 'entity-empty';
        message.textContent = 'Noch keine Workouts vorhanden.';

        list.appendChild(message);
        return;
    }

    workouts.forEach((workout) => {
        const item = document.createElement('article');
        item.className = 'entity-card workout-item';

        const header = document.createElement('div');
        header.className = 'entity-card-header';

        const title = document.createElement('h3');
        title.className = 'entity-card-title';
        title.textContent = workout.title;

        const date = document.createElement('span');
        date.className = 'entity-meta-item';
        date.textContent = `Datum: ${formatWorkoutDate(workout.date)}`;

        header.append(title, date);

        const meta = document.createElement('div');
        meta.className = 'entity-meta';

        const duration = document.createElement('p');
        duration.className = 'entity-meta-item';
        duration.textContent =
            `Dauer: ${workout.duration_minutes || 0} Minuten`;

        meta.appendChild(duration);
        item.append(header, meta);

        const exercises = Array.isArray(workout.exercises)
            ? workout.exercises
            : [];

        if (exercises.length > 0) {
            const exerciseSection = document.createElement('div');
            exerciseSection.className = 'workout-card-exercises';

            const exerciseHeading = document.createElement('h4');
            exerciseHeading.className = 'workout-card-section-title';
            exerciseHeading.textContent = 'Übungen';

            exerciseSection.appendChild(exerciseHeading);

            exercises.forEach((exercise) => {
                const exerciseItem = document.createElement('div');
                exerciseItem.className = 'workout-card-exercise';

                const exerciseName = document.createElement('p');
                exerciseName.className = 'workout-card-exercise-name';
                exerciseName.textContent = exercise.name;

                const metrics = document.createElement('div');
                metrics.className = 'workout-exercise-metrics';

                [
                    `${exercise.sets || 0} Sätze`,
                    `${exercise.reps || 0} Wiederholungen`,
                    `${exercise.weight_kg || 0} kg`
                ].forEach((value) => {
                    const metric = document.createElement('span');
                    metric.className = 'workout-metric-chip';
                    metric.textContent = value;

                    metrics.appendChild(metric);
                });

                exerciseItem.append(exerciseName, metrics);
                exerciseSection.appendChild(exerciseItem);
            });

            item.appendChild(exerciseSection);
        }

        if (workout.notes) {
            const notes = document.createElement('div');
            notes.className = 'workout-notes';

            const notesLabel = document.createElement('strong');
            notesLabel.textContent = 'Notizen';

            const notesText = document.createElement('p');
            notesText.textContent = workout.notes;

            notes.append(notesLabel, notesText);
            item.appendChild(notes);
        }

        const actions = document.createElement('div');
        actions.className =
            'entity-card-actions workout-actions';

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

    editingWorkoutId = Number(workout.id);

    fields.title.value = workout.title || '';
    fields.date.value = workout.date || defaultWorkoutDate;
    fields.duration.value = workout.duration_minutes ?? '';
    fields.notes.value = workout.notes || '';

    setWorkoutExerciseBlocks(workout.exercises);

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
    fields.notes.value = '';

    setWorkoutExerciseBlocks([]);

    document.getElementById('save-workout').textContent =
        'Workout speichern';
    document.getElementById('cancel-workout-edit').hidden = true;
}

function getWorkoutFormFields() {
    return {
        title: document.getElementById('workout-title'),
        date: document.getElementById('workout-date'),
        duration: document.getElementById('workout-duration'),
        notes: document.getElementById('workout-notes')
    };
}

function addWorkoutExerciseBlock(exercise = null) {
    const container = document.getElementById('workout-exercises');
    const template = document.getElementById(
        'workout-exercise-template'
    );
    const block = template.content.firstElementChild.cloneNode(true);
    const select = block.querySelector('.workout-exercise-select');
    const exerciseId = exercise?.id ?? exercise?.exercise_id ?? '';

    populateWorkoutExerciseSelect(select, exerciseId);

    block.querySelector('.workout-exercise-sets').value =
        exercise?.sets ?? '';
    block.querySelector('.workout-exercise-reps').value =
        exercise?.reps ?? '';
    block.querySelector('.workout-exercise-weight').value =
        exercise?.weight_kg ?? '';

    block
        .querySelector('.workout-remove-exercise-button')
        .addEventListener('click', () => {
            if (container.children.length <= 1) {
                return;
            }

            block.remove();
            updateWorkoutExerciseBlocks();
        });

    container.appendChild(block);
    updateWorkoutExerciseBlocks();
}

function setWorkoutExerciseBlocks(exercises) {
    const container = document.getElementById('workout-exercises');
    const workoutExercises =
        Array.isArray(exercises) && exercises.length > 0
            ? exercises
            : [null];

    container.replaceChildren();

    workoutExercises.forEach((exercise) => {
        addWorkoutExerciseBlock(exercise);
    });
}

function updateWorkoutExerciseBlocks() {
    const blocks = document.querySelectorAll(
        '.workout-exercise-block'
    );
    const hasSingleBlock = blocks.length === 1;

    blocks.forEach((block, index) => {
        block.querySelector('.workout-exercise-title').textContent =
            `Übung ${index + 1}`;
        block.querySelector(
            '.workout-remove-exercise-button'
        ).disabled = hasSingleBlock;
    });
}

function updateWorkoutExerciseSelects() {
    document
        .querySelectorAll('.workout-exercise-select')
        .forEach((select) => {
            populateWorkoutExerciseSelect(select, select.value);
        });
}

function populateWorkoutExerciseSelect(select, selectedExerciseId) {
    select.replaceChildren();

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Übung auswählen';

    select.appendChild(defaultOption);

    exerciseCatalog.forEach((exercise) => {
        const option = document.createElement('option');

        option.value = exercise.id;
        option.textContent =
            `${exercise.name} (${exercise.category})`;

        select.appendChild(option);
    });

    select.value = String(selectedExerciseId);
}

function getWorkoutExercisesFromForm() {
    return Array.from(
        document.querySelectorAll('.workout-exercise-block')
    ).map((block) => ({
        exercise_id:
            Number(
                block.querySelector('.workout-exercise-select').value
            ) || 0,
        sets:
            Number(
                block.querySelector('.workout-exercise-sets').value
            ) || 0,
        reps:
            Number(
                block.querySelector('.workout-exercise-reps').value
            ) || 0,
        weight_kg:
            Number(
                block.querySelector('.workout-exercise-weight').value
            ) || 0
    }));
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
