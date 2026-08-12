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
        window.location.href = 'profile.php'; // nach Login → Profil
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

// Profil speichern
async function saveProfile() {
    const username = document.getElementById('username').value.trim();
    const emailEl  = document.getElementById('email');
    const email    = emailEl.value.trim();
    const weight  = document.getElementById('weight').value;
    const height  = document.getElementById('height').value;
    const error   = document.getElementById('error');
    const success = document.getElementById('success');

    // Pflichtfelder prüfen
    if (!username || !email || !weight || !height) {
        showError(error, 'Bitte alle Felder ausfüllen.');
        return;
    }

    if (username.length > 50) {
        showError(error, 'Der Benutzername darf höchstens 50 Zeichen lang sein.');
        return;
    }

    if (!emailEl.checkValidity()) {
        showError(error, 'Bitte eine gültige E-Mail-Adresse eingeben.');
        return;
    }

    // API-Anfrage
    const res  = await fetch('/Webtechnologie-2-SoSe26/api/profile.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ username, email, weight_kg: weight, height_cm: height })
    });

    const data = await res.json();

    if (data.success) {
        showSuccess(success, 'Profil gespeichert!');
        error.style.display = 'none';
        document.getElementById('avatar').textContent = username.substring(0, 2).toUpperCase();
    } else {
        showError(error, data.message);
        success.style.display = 'none';
    }
}

// BMI berechnen
function calcBMI() {
    const w  = parseFloat(document.getElementById('weight').value);
    const h  = parseFloat(document.getElementById('height').value) / 100;
    const el = document.getElementById('bmi');
    if (w > 0 && h > 0) {
        el.textContent = (w / (h * h)).toFixed(1);
    } else {
        el.textContent = '—';
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
