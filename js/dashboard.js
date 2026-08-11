const dashboardStatsApiUrl =
    '/Webtechnologie-2-SoSe26/api/stats.php';

const dashboardIntegerFormatter = new Intl.NumberFormat(
    'de-DE',
    { maximumFractionDigits: 0 }
);

const dashboardValueFormatter = new Intl.NumberFormat(
    'de-DE',
    { maximumFractionDigits: 2 }
);

document.addEventListener('DOMContentLoaded', loadDashboard);

async function loadDashboard() {
    const error = document.getElementById('dashboard-error');

    hideDashboardError(error);

    try {
        const response = await apiRequest(dashboardStatsApiUrl);
        const data = response.data || {};

        renderDashboardStats(data);
        renderRecentWorkouts(
            Array.isArray(data.recent_workouts)
                ? data.recent_workouts
                : []
        );
        renderActiveGoals(
            Array.isArray(data.active_goals)
                ? data.active_goals
                : []
        );
    } catch (requestError) {
        showDashboardError(
            error,
            requestError.message ||
                'Die Dashboard-Daten konnten nicht geladen werden.'
        );

        renderDashboardMessage(
            document.getElementById('recent-workouts'),
            'Workouts konnten nicht geladen werden.'
        );
        renderDashboardMessage(
            document.getElementById('active-goals'),
            'Ziele konnten nicht geladen werden.'
        );
    }
}

function renderDashboardStats(data) {
    document.getElementById('total-workouts').textContent =
        formatDashboardInteger(data.total_workouts);

    document.getElementById('total-training-minutes').textContent =
        formatDashboardInteger(data.total_training_minutes);

    document.getElementById('active-goals-count').textContent =
        formatDashboardInteger(data.active_goals_count);
}

function renderRecentWorkouts(workouts) {
    const list = document.getElementById('recent-workouts');

    list.replaceChildren();

    if (workouts.length === 0) {
        renderDashboardMessage(
            list,
            'Noch keine Workouts vorhanden.'
        );
        return;
    }

    workouts.slice(0, 5).forEach((workout) => {
        const item = document.createElement('article');
        item.className = 'dashboard-list-item';

        const title = document.createElement('h3');
        title.textContent = workout.title || 'Workout';

        const meta = document.createElement('div');
        meta.className = 'dashboard-item-meta';

        const date = document.createElement('span');
        date.textContent = `Datum: ${formatDashboardDate(workout.date)}`;

        const duration = document.createElement('span');
        duration.textContent =
            `Dauer: ${formatDashboardInteger(
                workout.duration_minutes
            )} Minuten`;

        meta.append(date, duration);
        item.append(title, meta);
        list.appendChild(item);
    });
}

function renderActiveGoals(goals) {
    const list = document.getElementById('active-goals');

    list.replaceChildren();

    if (goals.length === 0) {
        renderDashboardMessage(
            list,
            'Keine aktiven Ziele vorhanden.'
        );
        return;
    }

    goals.forEach((goal) => {
        const currentValue = toDashboardNumber(goal.current_value);
        const targetValue = toDashboardNumber(goal.target_value);
        const visualPercentage = calculateVisualPercentage(
            currentValue,
            targetValue
        );
        const roundedPercentage = Math.round(visualPercentage);

        const item = document.createElement('article');
        item.className = 'dashboard-list-item';

        const title = document.createElement('h3');
        title.textContent = goal.description || 'Ziel';

        const values = document.createElement('p');
        values.className = 'dashboard-goal-values';

        const unit = goal.unit ? ` ${goal.unit}` : '';
        values.textContent =
            `${dashboardValueFormatter.format(currentValue)} / ` +
            `${dashboardValueFormatter.format(targetValue)}${unit}`;

        item.append(title, values);

        if (goal.deadline) {
            const deadline = document.createElement('p');
            deadline.className = 'dashboard-goal-deadline';
            deadline.textContent =
                `Deadline: ${formatDashboardDate(goal.deadline)}`;
            item.appendChild(deadline);
        }

        const progressHeader = document.createElement('div');
        progressHeader.className = 'dashboard-progress-header';

        const progressLabel = document.createElement('span');
        progressLabel.className = 'dashboard-progress-label';
        progressLabel.textContent = 'Fortschritt';

        const progressPercentage = document.createElement('span');
        progressPercentage.className = 'dashboard-progress-percentage';
        progressPercentage.textContent = `${roundedPercentage}%`;

        progressHeader.append(progressLabel, progressPercentage);

        const progressTrack = document.createElement('div');
        progressTrack.className = 'dashboard-progress-track';
        progressTrack.setAttribute('role', 'progressbar');
        progressTrack.setAttribute('aria-valuemin', '0');
        progressTrack.setAttribute('aria-valuemax', '100');
        progressTrack.setAttribute(
            'aria-valuenow',
            String(roundedPercentage)
        );

        const progressBar = document.createElement('div');
        progressBar.className = 'dashboard-progress-bar';
        progressBar.style.width = `${visualPercentage}%`;

        progressTrack.appendChild(progressBar);
        item.append(progressHeader, progressTrack);
        list.appendChild(item);
    });
}

function calculateVisualPercentage(currentValue, targetValue) {
    if (targetValue <= 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.max(0, (currentValue / targetValue) * 100)
    );
}

function formatDashboardInteger(value) {
    const number = toDashboardNumber(value);

    return dashboardIntegerFormatter.format(
        Math.max(0, Math.trunc(number))
    );
}

function toDashboardNumber(value) {
    const number = Number(value);

    return Number.isFinite(number) ? number : 0;
}

function formatDashboardDate(value) {
    const parts = String(value || '').split('-');

    if (parts.length !== 3) {
        return 'Kein Datum';
    }

    return `${parts[2]}.${parts[1]}.${parts[0]}`;
}

function renderDashboardMessage(container, message) {
    container.replaceChildren();

    const text = document.createElement('p');
    text.className = 'dashboard-empty';
    text.textContent = message;

    container.appendChild(text);
}

function showDashboardError(element, message) {
    element.textContent = message;
    element.style.display = 'block';
}

function hideDashboardError(element) {
    element.textContent = '';
    element.style.display = 'none';
}
