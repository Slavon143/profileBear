import {
  PRODUCT_VARIATIONS_FETCHING,
  PRODUCT_VARIATIONS_SUCCESS,
  PRODUCT_VARIATIONS_ERROR,
  PRODUCT_VARIATIONS_INVALIDATE,
} from './actionTypes';

export const initialState = () => ({});

export const PRODUCT_VARIATIONS_REDUCER_NAME = 'product-variations';

export const productVariationsReducer = (state = initialState(), action) => {
  const {
    type,
    data,
    error,
    links,
    productId,
  } = action;

  const productVariations = state[productId] || {};

  switch (type) {
    case PRODUCT_VARIATIONS_FETCHING:
      return {
        ...state,
        [productId]: {
          ...productVariations,
          isFetching: true,
          didInvalidate: false,
        },
      };

    case PRODUCT_VARIATIONS_SUCCESS:
      return {
        ...state,
        [productId]: {
          ...productVariations,
          items: (productVariations.items || []).concat(data),
          links,
          isFetching: false,
          didInvalidate: false,
          lastUpdated: new Date(),
        },
      };

    case PRODUCT_VARIATIONS_ERROR:
      return {
        ...state,
        [productId]: {
          ...productVariations,
          error,
          isFetching: false,
          didInvalidate: false,
          lastUpdated: new Date(),
        },
      };

    case PRODUCT_VARIATIONS_INVALIDATE:
      return {
        ...state,
        [productId]: {
          ...productVariations,
          items: [],
          didInvalidate: true,
        },
      };

    default:
      return state;
  }
};

export const getProductVariations = (state, { productId }) => ({
  ...(state[PRODUCT_VARIATIONS_REDUCER_NAME][productId] || null),
});

export const invalidateProductVariations = (productId) => ({
  type: PRODUCT_VARIATIONS_INVALIDATE,
  productId,
});

export const fetchProductVariations = ({ productId }, next = `wc/v3/products/${productId}/variations`) => {
  return {
    types: {
      requestTypes: [PRODUCT_VARIATIONS_FETCHING],
      successTypes: [PRODUCT_VARIATIONS_SUCCESS],
      failureTypes: [PRODUCT_VARIATIONS_ERROR],
    },
    endpoint: next,
    productId,
    shouldCallAPI: (state) => !getProductVariations(state, { productId }).isFetching,
  };
};
