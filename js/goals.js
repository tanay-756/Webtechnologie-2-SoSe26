const goalsApiUrl =
    '/Webtechnologie-2-SoSe26/api/goals.php';

document.addEventListener('DOMContentLoaded', () => {
    const saveButton = document.getElementById('save-goal');

    saveButton.addEventListener('click', saveGoal);

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
    const descriptionInput =
        document.getElementById('goal-description');
    const targetInput =
        document.getElementById('goal-target');
    const currentInput =
        document.getElementById('goal-current');
    const unitInput =
        document.getElementById('goal-unit');
    const deadlineInput =
        document.getElementById('goal-deadline');

    const error = document.getElementById('goal-error');
    const success = document.getElementById('goal-success');

    hideGoalMessage(error);
    hideGoalMessage(success);

    const goalData = {
        description: descriptionInput.value.trim(),
        target_value: Number(targetInput.value) || 0,
        current_value: Number(currentInput.value) || 0,
        unit: unitInput.value.trim(),
        deadline: deadlineInput.value
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

    try {
        const data = await apiRequest(goalsApiUrl, {
            method: 'POST',
            body: JSON.stringify(goalData)
        });

        showGoalMessage(success, data.message);

        descriptionInput.value = '';
        targetInput.value = '';
        currentInput.value = '0';
        unitInput.value = '';
        deadlineInput.value = '';

        await loadGoals();
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
            updateButton
        );

        list.appendChild(item);
    });
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