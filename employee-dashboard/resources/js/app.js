import './bootstrap';

const originalFetch = window.fetch;
window.fetch = async function() {
    const response = await originalFetch.apply(this, arguments);
    if (response.status === 401) {
        // Jangan tampilkan alert jika berada di halaman login
        if (window.location.pathname !== '/login') {
            alert('Sesi Anda telah habis (lebih dari 4 jam). Silakan login kembali.');
            localStorage.removeItem('staff_token');
            window.location.href = '/login';
        }
    }
    return response;
};
