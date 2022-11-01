export const APP_REDUCER_NAME = 'app';

export const appReducer = (state = {}) => state;

export const getAppState = (state) => state[APP_REDUCER_NAME];

export const getTextDomain = (state) => getAppState(state).textDomain;

export const getAdminUrl = (state) => getAppState(state).adminUrl;

export const getPerPage = (state) => getAppState(state).perPage;

export const getLowStockThreshold = (state) => getAppState(state).lowStockThreshold;