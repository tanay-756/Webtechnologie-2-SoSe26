const exercisesApiUrl =
    '/Webtechnologie-2-SoSe26/api/exercises.php';

let editingExerciseId = null;

document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('save-exercise');
    const cancelButton =
        document.getElementById('cancel-exercise-edit');

    saveButton.addEventListener('click', saveExercise);
    cancelButton.addEventListener('click', cancelExerciseEdit);

    loadExercises();
});

async function loadExercises() {
    const list = document.getElementById('exercise-list');
    const error = document.getElementById('exercise-error');

    list.textContent = 'Übungen werden geladen …';
    hideMessage(error);

    try {
        const data = await apiRequest(exercisesApiUrl);

        renderExercises(data.exercises);
    } catch (requestError) {
        list.textContent = '';
        showMessage(error, requestError.message);
    }
}

async function saveExercise() {
    const fields = getExerciseFormFields();
    const error = document.getElementById('exercise-error');
    const success = document.getElementById('exercise-success');

    const exerciseData = {
        name: fields.name.value.trim(),
        category: fields.category.value,
        description: fields.description.value.trim()
    };

    hideMessage(error);
    hideMessage(success);

    if (exerciseData.name === '' || exerciseData.category === '') {
        showMessage(
            error,
            'Bitte Name und Kategorie ausfüllen.'
        );
        return;
    }

    const isEditing = editingExerciseId !== null;

    if (isEditing) {
        exerciseData.id = editingExerciseId;
    }

    try {
        const data = await apiRequest(exercisesApiUrl, {
            method: isEditing ? 'PATCH' : 'POST',
            body: JSON.stringify(exerciseData)
        });

        resetExerciseForm();
        await loadExercises();

        showMessage(success, data.message);
    } catch (requestError) {
        showMessage(error, requestError.message);
    }
}

function renderExercises(exercises) {
    const list = document.getElementById('exercise-list');

    list.replaceChildren();

    if (exercises.length === 0) {
        const message = document.createElement('p');
        message.textContent = 'Noch keine Übungen vorhanden.';

        list.appendChild(message);
        return;
    }

    exercises.forEach((exercise) => {
        const item = document.createElement('div');
        item.className = 'exercise-item';

        const title = document.createElement('h3');
        title.textContent = exercise.name;

        const category = document.createElement('p');
        category.textContent =
            `Kategorie: ${exercise.category}`;

        const description = document.createElement('p');
        description.textContent =
            exercise.description || 'Keine Beschreibung';

        const actions = document.createElement('div');
        actions.className = 'exercise-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'exercise-action-button';
        editButton.textContent = 'Bearbeiten';
        editButton.addEventListener('click', () => {
            startExerciseEdit(exercise);
        });

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className =
            'exercise-action-button exercise-delete-button';
        deleteButton.textContent = 'Löschen';
        deleteButton.addEventListener('click', () => {
            deleteExercise(exercise);
        });

        actions.append(editButton, deleteButton);
        item.append(title, category, description, actions);
        list.appendChild(item);
    });
}

function startExerciseEdit(exercise) {
    const fields = getExerciseFormFields();

    editingExerciseId = Number(exercise.id);

    fields.name.value = exercise.name || '';
    fields.category.value = exercise.category || '';
    fields.description.value = exercise.description || '';

    document.getElementById('save-exercise').textContent =
        'Änderungen speichern';
    document.getElementById('cancel-exercise-edit').hidden = false;

    hideMessage(document.getElementById('exercise-error'));
    hideMessage(document.getElementById('exercise-success'));

    fields.name.focus();
}

function cancelExerciseEdit() {
    resetExerciseForm();
    hideMessage(document.getElementById('exercise-error'));
    hideMessage(document.getElementById('exercise-success'));
}

async function deleteExercise(exercise) {
    const confirmed = confirm(
        `Soll die Übung "${exercise.name}" wirklich gelöscht werden?`
    );

    if (!confirmed) {
        return;
    }

    const error = document.getElementById('exercise-error');
    const success = document.getElementById('exercise-success');

    hideMessage(error);
    hideMessage(success);

    try {
        const data = await apiRequest(exercisesApiUrl, {
            method: 'DELETE',
            body: JSON.stringify({ id: Number(exercise.id) })
        });

        if (editingExerciseId === Number(exercise.id)) {
            resetExerciseForm();
        }

        await loadExercises();

        showMessage(success, data.message);
    } catch (requestError) {
        showMessage(error, requestError.message);
    }
}

function resetExerciseForm() {
    const fields = getExerciseFormFields();

    editingExerciseId = null;

    fields.name.value = '';
    fields.category.value = '';
    fields.description.value = '';

    document.getElementById('save-exercise').textContent =
        'Übung speichern';
    document.getElementById('cancel-exercise-edit').hidden = true;
}

function getExerciseFormFields() {
    return {
        name: document.getElementById('exercise-name'),
        category: document.getElementById('exercise-category'),
        description: document.getElementById('exercise-description')
    };
}

function showMessage(element, message) {
    element.textContent = message;
    element.style.display = 'block';
}

function hideMessage(element) {
    element.textContent = '';
    element.style.display = 'none';
}
