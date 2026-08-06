async function apiRequest(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {})
        }
    });

    let data;

    try {
        data = await response.json();
    } catch {
        throw new Error('Der Server hat keine gültige Antwort gesendet.');
    }

    if (!response.ok || data.success === false) {
        throw new Error(data.message || 'Die Anfrage ist fehlgeschlagen.');
    }

    return data;
}