import { SET_SCREEN_OPTIONS } from './actionTypes';

const getInitialState = () => ({
  settings: {
    sku: true,
    thumbnail: false,
    price: true,
    salePrice: true,
    weight: false,
    manageStock: true,
    stockStatus: true,
    backorders: false,
    stock: true,
    taxStatus: false,
    taxClass: false,
    shippingClass: false,
    productName: true,
  },
});

export const SCREEN_OPTIONS_REDUCER_NAME = 'screen-options';

export const screenOptionsReducer = (state = getInitialState(), action) => {
  const {
    type,
    settings,
  } = action;

  switch (type) {
    case SET_SCREEN_OPTIONS: {
      return {
        ...state,
        settings: {
          ...state.settings,
          ...settings,
        },
      };
    }

    default: {
      return state;
    }
  }
};

export const getScreenOptions = (state) => state[SCREEN_OPTIONS_REDUCER_NAME];

export const getIsScreenOptionSet = (state, field) => (
  state[SCREEN_OPTIONS_REDUCER_NAME].settings[field] !== undefined
    ? state[SCREEN_OPTIONS_REDUCER_NAME].settings[field]
    : false
);

export const setScreenOptions = (settings) => ({
  type: SET_SCREEN_OPTIONS,
  settings,
});
