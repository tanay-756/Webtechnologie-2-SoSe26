// Login-Formular absenden
async function handleLogin() {
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const error    = document.getElementById('error');

    // Pflichtfelder prüfen
    if (!email || !password) {
        showError(error, 'Bitte alle Felder ausfüllen.');
        return;
    }

    // API-Anfrage
    const res  = await fetch('/Webtechnologie-2-SoSe26/api/login.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ email, password })
    });

    const data = await res.json();

    if (data.success) {
        window.location.href = 'dashboard.php';
    } else {
        showError(error, data.message);
    }
}

// Registrierungs-Formular absenden
async function handleRegister() {
    const username = document.getElementById('username').value.trim();
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const error    = document.getElementById('error');
    const success  = document.getElementById('success');

    // Pflichtfelder prüfen
    if (!username || !email || !password) {
        showError(error, 'Bitte alle Felder ausfüllen.');
        return;
    }

    // API-Anfrage
    const res  = await fetch('/Webtechnologie-2-SoSe26/api/register.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ username, email, password })
    });

    const data = await res.json();

    if (data.success) {
        showSuccess(success, 'Registrierung erfolgreich! Weiterleitung...');
        setTimeout(() => window.location.href = 'login.php', 1500);
    } else {
        showError(error, data.message);
    }
}

// Fehlermeldung anzeigen
function showError(el, msg) {
    el.textContent = msg;
    el.style.display = 'block';
}

// Erfolgsmeldung anzeigen
function showSuccess(el, msg) {
    el.textContent = msg;
    el.style.display = 'block';
}
