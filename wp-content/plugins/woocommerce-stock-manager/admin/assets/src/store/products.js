import {
  PRODUCTS_FETCHING,
  PRODUCTS_SUCCESS,
  PRODUCTS_ERROR,
  PRODUCTS_INVALIDATE,
  PRODUCTS_RESET,
} from './actionTypes';

import { getProduct } from './product';

export const initialState = () => ({
  isFetching: false,
  didInvalidate: false,
  items: [],
  meta: {},
  links: {
    next: 'wc/v3/products',
  },
});

export const PRODUCTS_REDUCER_NAME = 'products';

export const productsReducer = (state = initialState(), action) => {
  const {
    type,
    meta,
    links,
    data,
    error,
  } = action;

  switch (type) {
    case PRODUCTS_FETCHING:
      return {
        ...state,
        isFetching: true,
        didInvalidate: false,
      };

    case PRODUCTS_SUCCESS:
      return {
        items: state.items.concat(data.map((product) => product.id)),
        meta,
        links,
        isFetching: false,
        didInvalidate: false,
        lastUpdated: new Date(),
      };

    case PRODUCTS_ERROR:
      return {
        ...state,
        error,
        isFetching: false,
        didInvalidate: false,
      };

    case PRODUCTS_INVALIDATE:
      return {
        ...state,
        didInvalidate: true,
      };

    case PRODUCTS_RESET:
      return initialState();

    default:
      return state;
  }
};

export const getProducts = (state) => ({
  ...state[PRODUCTS_REDUCER_NAME],
  items: state[PRODUCTS_REDUCER_NAME].items.map((id) => getProduct(state, { id })),
});

export const invalidateProducts = () => ({
  type: PRODUCTS_INVALIDATE,
});

export const fetchProducts = (query, next) => {
  return {
    types: {
      requestTypes: [PRODUCTS_FETCHING],
      successTypes: [PRODUCTS_SUCCESS],
      failureTypes: [PRODUCTS_ERROR],
    },
    endpoint: next,
    query: { ...query },
  };
};

export const resetProducts = () => ({
  type: PRODUCTS_RESET,
});
