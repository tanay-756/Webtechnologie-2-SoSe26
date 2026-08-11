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

    list.classList.add('entity-list');
    list.replaceChildren();

    if (goals.length === 0) {
        const message = document.createElement('p');
        message.className = 'entity-empty';
        message.textContent = 'Noch keine Ziele vorhanden.';

        list.appendChild(message);
        return;
    }

    goals.forEach((goal) => {
        const item = document.createElement('article');
        item.className = 'entity-card goal-item';

        const header = document.createElement('div');
        header.className = 'entity-card-header';

        const title = document.createElement('h3');
        title.className = 'entity-card-title';
        title.textContent = goal.description;

        const statusBadge = document.createElement('span');
        const statusClasses = {
            aktiv: 'entity-badge-active',
            erreicht: 'entity-badge-success',
            abgebrochen: 'entity-badge-cancelled'
        };
        const statusLabels = {
            aktiv: 'Aktiv',
            erreicht: 'Erreicht',
            abgebrochen: 'Abgebrochen'
        };

        statusBadge.className =
            'entity-badge ' +
            (statusClasses[goal.status] || 'entity-badge-neutral');
        statusBadge.textContent =
            statusLabels[goal.status] || goal.status;

        header.append(title, statusBadge);

        const valueSummary = document.createElement('div');
        valueSummary.className = 'goal-value-summary';

        const currentValueBlock = document.createElement('div');
        currentValueBlock.className = 'goal-value-block';

        const currentValueLabel = document.createElement('span');
        currentValueLabel.className = 'goal-value-label';
        currentValueLabel.textContent = 'Aktueller Wert';

        const currentValueText = document.createElement('strong');
        currentValueText.className = 'goal-value-number';
        currentValueText.textContent =
            `${goal.current_value} ${goal.unit}`;

        currentValueBlock.append(currentValueLabel, currentValueText);

        const targetValueBlock = document.createElement('div');
        targetValueBlock.className = 'goal-value-block';

        const targetValueLabel = document.createElement('span');
        targetValueLabel.className = 'goal-value-label';
        targetValueLabel.textContent = 'Zielwert';

        const targetValueText = document.createElement('strong');
        targetValueText.className = 'goal-value-number';
        targetValueText.textContent =
            `${goal.target_value} ${goal.unit}`;

        targetValueBlock.append(targetValueLabel, targetValueText);
        valueSummary.append(currentValueBlock, targetValueBlock);

        const meta = document.createElement('div');
        meta.className = 'entity-meta';

        const deadline = document.createElement('span');
        deadline.className = 'entity-meta-item';
        deadline.textContent = goal.deadline
            ? `Zieldatum: ${formatGoalDate(goal.deadline)}`
            : 'Kein Zieldatum';

        meta.appendChild(deadline);

        const currentNumber = Number(goal.current_value);
        const targetNumber = Number(goal.target_value);
        const rawPercentage =
            Number.isFinite(currentNumber) && targetNumber > 0
                ? (currentNumber / targetNumber) * 100
                : 0;
        const visualPercentage = Math.min(
            100,
            Math.max(0, rawPercentage)
        );
        const progressValue = Math.round(visualPercentage);

        const progressHeader = document.createElement('div');
        progressHeader.className = 'goal-progress-header';

        const progressLabel = document.createElement('span');
        progressLabel.textContent = 'Fortschritt';

        const percentage = document.createElement('span');
        percentage.className = 'goal-progress-percentage';
        percentage.textContent = `${progressValue}%`;

        progressHeader.append(progressLabel, percentage);

        const progressTrack = document.createElement('div');
        progressTrack.className = 'progress-track';
        progressTrack.setAttribute('role', 'progressbar');
        progressTrack.setAttribute('aria-label', 'Zielfortschritt');
        progressTrack.setAttribute('aria-valuemin', '0');
        progressTrack.setAttribute('aria-valuemax', '100');
        progressTrack.setAttribute(
            'aria-valuenow',
            String(progressValue)
        );

        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar';
        progressBar.style.width = `${visualPercentage}%`;

        progressTrack.appendChild(progressBar);

        const progressForm = document.createElement('div');
        progressForm.className = 'goal-progress-form';

        const currentField = document.createElement('label');
        currentField.className = 'goal-progress-field';

        const currentLabel = document.createElement('span');
        currentLabel.textContent = 'Aktueller Wert';

        const currentInput = document.createElement('input');
        currentInput.type = 'number';
        currentInput.min = '0';
        currentInput.step = '0.1';
        currentInput.value = goal.current_value;

        currentField.append(currentLabel, currentInput);

        const statusField = document.createElement('label');
        statusField.className = 'goal-progress-field';

        const statusLabel = document.createElement('span');
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

        statusField.append(statusLabel, statusSelect);

        const updateButton = document.createElement('button');
        updateButton.type = 'button';
        updateButton.className = 'btn goal-progress-button';
        updateButton.textContent = 'Fortschritt speichern';

        updateButton.addEventListener('click', () => {
            updateGoal(
                goal.id,
                currentInput.value,
                statusSelect.value
            );
        });

        progressForm.append(
            currentField,
            statusField,
            updateButton
        );

        const actions = document.createElement('div');
        actions.className = 'entity-card-actions goal-actions';

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
            header,
            valueSummary,
            meta,
            progressHeader,
            progressTrack,
            progressForm,
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
