/**
 * Smart IT Helpdesk - Client Application JS
 * Vanilla JS with Fetch API
 */

document.addEventListener('DOMContentLoaded', () => {
    // Auto fade out flash messages after 5 seconds
    const flash = document.getElementById('flash-alert');
    if (flash) {
        setTimeout(() => {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 300);
        }, 5000);
    }
});

/**
 * Universal Fetch Helper for AJAX endpoints
 */
async function fetchApi(url, options = {}) {
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                   || document.querySelector('input[name="_csrf"]')?.value;

    if (csrfToken) {
        defaultHeaders['X-CSRF-TOKEN'] = csrfToken;
    }

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers,
        },
    };

    try {
        const response = await fetch(url, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Server error occurred');
        }

        return data;
    } catch (err) {
        console.error('API Error:', err);
        throw err;
    }
}
