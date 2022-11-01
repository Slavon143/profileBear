import {
  PRODUCT_FETCHING,
  PRODUCT_SUCCESS,
  PRODUCTS_SUCCESS,
  PRODUCT_ERROR,
  PRODUCT_INVALIDATE,
  SAVE_PRODUCT_CHANGES_SUCCESS,
} from './actionTypes';

const initialState = () => ({});

export const PRODUCT_REDUCER_NAME = 'product';

export const productReducer = (state = initialState(), action) => {
  const {
    type,
    meta,
    data,
    error,
  } = action;

  switch (type) {
    case PRODUCT_FETCHING: {
      return {
        ...state,
        [data.id]: {
          ...(state[data.id] ? state[data.id] : null),
          isFetching: true,
          didInvalidate: false,
        },
      };
    }

    case PRODUCT_SUCCESS: {
      return {
        ...state,
        [data.id]: {
          ...data,
          isFetching: false,
          didInvalidate: false,
          lastUpdated: new Date(),
        },
      };
    }

    case PRODUCTS_SUCCESS: {
      const nextState = { ...state };

      data.forEach((product) => {
        nextState[product.id] = {
          ...product,
          isFetching: false,
          didInvalidate: false,
          lastUpdated: new Date(),
        };
      });

      return nextState;
    }

    case SAVE_PRODUCT_CHANGES_SUCCESS: {
      const nextState = { ...state };

      if (data.update) {
        data.update.forEach((product) => {
          nextState[product.id] = {
            ...product,
            isFetching: false,
            didInvalidate: false,
            lastUpdated: new Date(),
          };
        });
      }

      return { ...nextState };
    }

    case PRODUCT_ERROR: {
      return {
        ...state,
        [meta.id]: {
          ...(state[meta.id] ? state[meta.id] : null),
          error,
          isFetching: false,
          didInvalidate: false,
        },
      };
    }

    case PRODUCT_INVALIDATE: {
      return {
        ...state,
        [data.id]: {
          ...(state[data.id] ? state[data.id] : null),
          didInvalidate: true,
        },
      };
    }

    default: {
      return state;
    }
  }
};

export const getProduct = (state, { id }) => state[PRODUCT_REDUCER_NAME][id];
