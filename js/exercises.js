const exercisesApiUrl =
    '/Webtechnologie-2-SoSe26/api/exercises.php';

document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('save-exercise');

    saveButton.addEventListener('click', saveExercise);

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
    const nameInput = document.getElementById('exercise-name');
    const categoryInput =
        document.getElementById('exercise-category');
    const descriptionInput =
        document.getElementById('exercise-description');

    const error = document.getElementById('exercise-error');
    const success = document.getElementById('exercise-success');

    const name = nameInput.value.trim();
    const category = categoryInput.value;
    const description = descriptionInput.value.trim();

    hideMessage(error);
    hideMessage(success);

    if (name === '' || category === '') {
        showMessage(
            error,
            'Bitte Name und Kategorie ausfüllen.'
        );
        return;
    }

    try {
        const data = await apiRequest(exercisesApiUrl, {
            method: 'POST',
            body: JSON.stringify({
                name,
                category,
                description
            })
        });

        showMessage(success, data.message);

        nameInput.value = '';
        categoryInput.value = '';
        descriptionInput.value = '';

        await loadExercises();
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

        item.append(title, category, description);
        list.appendChild(item);
    });
}

function showMessage(element, message) {
    element.textContent = message;
    element.style.display = 'block';
}

function hideMessage(element) {
    element.textContent = '';
    element.style.display = 'none';
}