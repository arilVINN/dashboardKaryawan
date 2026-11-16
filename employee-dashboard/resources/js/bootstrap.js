import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 401) {
            if (typeof window.handleUnauthorizedSession === 'function') {
                window.handleUnauthorizedSession();
            } else if (window.location.pathname !== '/login') {
                sessionStorage.removeItem('staff_token');
                localStorage.removeItem('staff_token');
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);
