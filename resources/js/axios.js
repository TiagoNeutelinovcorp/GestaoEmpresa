import axios from 'axios';

const axiosInstance = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Interceptor para tratar erros
axiosInstance.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login';
        }
        if (error.response?.status === 422) {
            console.error('Erro de validação:', error.response.data);
        }
        return Promise.reject(error);
    }
);

export default axiosInstance;
