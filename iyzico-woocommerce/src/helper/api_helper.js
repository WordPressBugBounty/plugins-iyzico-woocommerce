import apiFetch from '@wordpress/api-fetch';

const BASE_HEADERS = {
    'X-WP-Nonce': iyzicoRestApi.nonce
};

export const fetchData = async (url) => {
    try {
        const response = await apiFetch({
            url: url,
            method: 'GET',
            headers: BASE_HEADERS
        });
        return response;
    } catch (error) {
        console.error('Error: ', error);
        throw error;
    }
};

export const getOrders = async (page = 1, perPage = 50, search = '', status = '') => {
    try {
        const queryParams = new URLSearchParams({
            page: page.toString(),
            per_page: perPage.toString(),
        });

        if (search) {
            queryParams.append('search', search);
        }
        if (status && status !== 'all') {
            queryParams.append('status', status);
        }

        const response = await apiFetch({
            url: `${iyzicoRestApi.GetOrdersUrl}?${queryParams.toString()}`,
            method: 'GET',
            headers: BASE_HEADERS
        });
        return response;
    } catch (error) {
        console.error('Error fetching orders:', error);
        return {
            orders: [],
            total: 0,
            total_pages: 0,
            current_page: 1
        };
    }
};

export const getSettings = async () => {
    return fetchData(iyzicoRestApi.SettingsUrl);
};

export const saveSettings = async (formData = null) => {
    try {
        const response = await apiFetch({
            url: iyzicoRestApi.SaveSettingsUrl,
            method: 'POST',
            headers: BASE_HEADERS,
            body: formData
        });
        return response;
    } catch (error) {
        console.error('Error saving settings:', error);
        return null;
    }
};

export const getSettingsDashboardWidgets = async () => {
    return fetchData(iyzicoRestApi.SettingsDashboardWidgetsUrl);
};

export const getSettingsDashboardCharts = async () => {
    return fetchData(iyzicoRestApi.SettingsDashboardChartsUrl);
};

export const getLocalizations = () => fetchData(iyzicoRestApi.LocalizationsUrl);