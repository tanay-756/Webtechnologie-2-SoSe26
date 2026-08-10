const goalsApiUrl =
    '/Webtechnologie-2-SoSe26/api/goals.php';

let editingGoalId = null;

document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('save-goal');
    const cancelButton =
        document.getElementById('cancel-goal-edit');

    saveButton.addEventListener('click', saveGoal);
    cancelButton.addEventListener('click', cancelGoalEdit);

    loadGoals();
});

async function loadGoals() {
    const list = document.getElementById('goal-list');
    const error = document.getElementById('goal-error');

    list.textContent = 'Ziele werden geladen …';
    hideGoalMessage(error);

    try {
        const data = await apiRequest(goalsApiUrl);

        renderGoals(data.goals);
    } catch (requestError) {
        list.textContent = '';
        showGoalMessage(error, requestError.message);
    }
}

async function saveGoal() {
    const fields = getGoalFormFields();
    const error = document.getElementById('goal-error');
    const success = document.getElementById('goal-success');

    hideGoalMessage(error);
    hideGoalMessage(success);

    const goalData = {
        description: fields.description.value.trim(),
        target_value: Number(fields.target.value) || 0,
        current_value: Number(fields.current.value) || 0,
        unit: fields.unit.value.trim(),
        deadline: fields.deadline.value
    };

    if (
        goalData.description === '' ||
        goalData.target_value <= 0 ||
        goalData.unit === ''
    ) {
        showGoalMessage(
            error,
            'Bitte Beschreibung, Zielwert und Einheit ausfüllen.'
        );
        return;
    }

    const isEditing = editingGoalId !== null;

    if (isEditing) {
        goalData.id = editingGoalId;
        goalData.status = fields.status.value;
    }

    try {
        const data = await apiRequest(goalsApiUrl, {
            method: isEditing ? 'PATCH' : 'POST',
            body: JSON.stringify(goalData)
        });

        resetGoalForm();
        await loadGoals();

        showGoalMessage(success, data.message);
    } catch (requestError) {
        showGoalMessage(error, requestError.message);
    }
}

function renderGoals(goals) {
    const list = document.getElementById('goal-list');

    list.replaceChildren();

    if (goals.length === 0) {
        const message = document.createElement('p');
        message.textContent = 'Noch keine Ziele vorhanden.';

        list.appendChild(message);
        return;
    }

    goals.forEach((goal) => {
        const item = document.createElement('div');
        item.className = 'goal-item';

        const title = document.createElement('h3');
        title.textContent = goal.description;

        const progress = document.createElement('p');
        progress.textContent =
            `Fortschritt: ${goal.current_value} von ` +
            `${goal.target_value} ${goal.unit}`;

        const percentage = document.createElement('p');

        const progressValue = Math.min(
            100,
            Math.round(
                (Number(goal.current_value) /
                    Number(goal.target_value)) * 100
            )
        );

        percentage.textContent =
            `Erreicht: ${progressValue}%`;

        const deadline = document.createElement('p');

        deadline.textContent = goal.deadline
            ? `Zieldatum: ${formatGoalDate(goal.deadline)}`
            : 'Kein Zieldatum';

        const statusText = document.createElement('p');
        statusText.textContent = `Status: ${goal.status}`;

        const currentLabel = document.createElement('label');
        currentLabel.textContent = 'Aktueller Wert';

        const currentInput = document.createElement('input');
        currentInput.type = 'number';
        currentInput.min = '0';
        currentInput.step = '0.1';
        currentInput.value = goal.current_value;

        const statusLabel = document.createElement('label');
        statusLabel.textContent = 'Status';

        const statusSelect = document.createElement('select');

        [
            ['aktiv', 'Aktiv'],
            ['erreicht', 'Erreicht'],
            ['abgebrochen', 'Abgebrochen']
        ].forEach(([value, text]) => {
            const option = document.createElement('option');

            option.value = value;
            option.textContent = text;
            option.selected = goal.status === value;

            statusSelect.appendChild(option);
        });

        const updateButton = document.createElement('button');
        updateButton.type = 'button';
        updateButton.className = 'btn';
        updateButton.textContent = 'Fortschritt speichern';

        updateButton.addEventListener('click', () => {
            updateGoal(
                goal.id,
                currentInput.value,
                statusSelect.value
            );
        });

        const actions = document.createElement('div');
        actions.className = 'goal-actions';

        const editButton = document.createElement('button');
        editButton.type = 'button';
        editButton.className = 'goal-action-button';
        editButton.textContent = 'Bearbeiten';
        editButton.addEventListener('click', () => {
            startGoalEdit(goal);
        });

        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className =
            'goal-action-button goal-delete-button';
        deleteButton.textContent = 'Löschen';
        deleteButton.addEventListener('click', () => {
            deleteGoal(goal);
        });

        actions.append(editButton, deleteButton);

        item.append(
            title,
            progress,
            percentage,
            deadline,
            statusText,
            currentLabel,
            currentInput,
            statusLabel,
            statusSelect,
            updateButton,
            actions
        );

        list.appendChild(item);
    });
}

function startGoalEdit(goal) {
    const fields = getGoalFormFields();

    editingGoalId = Number(goal.id);

    fields.description.value = goal.description || '';
    fields.target.value = goal.target_value ?? '';
    fields.current.value = goal.current_value ?? '0';
    fields.unit.value = goal.unit || '';
    fields.deadline.value = goal.deadline || '';
    fields.status.value = goal.status || 'aktiv';

    document.getElementById('goal-status-field').hidden = false;
    document.getElementById('save-goal').textContent =
        'Änderungen speichern';
    document.getElementById('cancel-goal-edit').hidden = false;

    hideGoalMessage(document.getElementById('goal-error'));
    hideGoalMessage(document.getElementById('goal-success'));

    fields.description.focus();
}

function cancelGoalEdit() {
    resetGoalForm();
    hideGoalMessage(document.getElementById('goal-error'));
    hideGoalMessage(document.getElementById('goal-success'));
}

async function deleteGoal(goal) {
    const confirmed = confirm(
        `Soll das Ziel "${goal.description}" wirklich gelöscht werden?`
    );

    if (!confirmed) {
        return;
    }

    const error = document.getElementById('goal-error');
    const success = document.getElementById('goal-success');

    hideGoalMessage(error);
    hideGoalMessage(success);

    try {
        const data = await apiRequest(goalsApiUrl, {
            method: 'DELETE',
            body: JSON.stringify({ id: Number(goal.id) })
        });

        if (editingGoalId === Number(goal.id)) {
            resetGoalForm();
        }

        await loadGoals();

        showGoalMessage(success, data.message);
    } catch (requestError) {
        showGoalMessage(error, requestError.message);
    }
}

function resetGoalForm() {
    const fields = getGoalFormFields();

    editingGoalId = null;

    fields.description.value = '';
    fields.target.value = '';
    fields.current.value = '0';
    fields.unit.value = '';
    fields.deadline.value = '';
    fields.status.value = 'aktiv';

    document.getElementById('goal-status-field').hidden = true;
    document.getElementById('save-goal').textContent =
        'Ziel speichern';
    document.getElementById('cancel-goal-edit').hidden = true;
}

function getGoalFormFields() {
    return {
        description: document.getElementById('goal-description'),
        target: document.getElementById('goal-target'),
        current: document.getElementById('goal-current'),
        unit: document.getElementById('goal-unit'),
        deadline: document.getElementById('goal-deadline'),
        status: document.getElementById('goal-status')
    };
}

async function updateGoal(goalId, currentValue, status) {
    const error = document.getElementById('goal-error');
    const success = document.getElementById('goal-success');

    hideGoalMessage(error);
    hideGoalMessage(success);

    if (Number(currentValue) < 0) {
        showGoalMessage(
            error,
            'Der aktuelle Wert darf nicht negativ sein.'
        );
        return;
    }

    try {
        const data = await apiRequest(goalsApiUrl, {
            method: 'PATCH',
            body: JSON.stringify({
                id: Number(goalId),
                current_value: Number(currentValue),
                status
            })
        });

        showGoalMessage(success, data.message);

        await loadGoals();
    } catch (requestError) {
        showGoalMessage(error, requestError.message);
    }
}

function formatGoalDate(date) {
    const parts = date.split('-');

    return `${parts[2]}.${parts[1]}.${parts[0]}`;
}

function showGoalMessage(element, message) {
    element.textContent = message;
    element.style.display = 'block';
}

function hideGoalMessage(element) {
    element.textContent = '';
    element.style.display = 'none';
}
