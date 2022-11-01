import {
  PRODUCT_CHANGE_DELETE,
  PRODUCT_CHANGE_UPDATE,
  SAVE_PRODUCT_CHANGES_FETCHING,
  SAVE_PRODUCT_CHANGES_SUCCESS,
  SAVE_PRODUCT_CHANGES_ERROR,
  CLEAN_PRODUCT_CHANGES,
  CLEAN_PRODUCT_VARIATIONS_CHANGES,
  PRODUCT_VARIATIONS_INVALIDATE,
} from './actionTypes';

import { getProducts } from './products';

export const PRODUCT_CHANGES_REDUCER_NAME = 'product-changes';

export const productChangesReducer = (state = {}, action = {}) => {
  const {
    type,
    productId,
    field,
    value,
  } = action;

  const currentProductChange = state[productId] || {};

  switch (type) {
    case PRODUCT_CHANGE_UPDATE: {
      return {
        ...state,
        [productId]: {
          ...currentProductChange,
          [field]: value,
        },
      };
    }

    case PRODUCT_CHANGE_DELETE: {
      if (typeof currentProductChange[field] !== 'undefined') {
        delete currentProductChange[field];
      }

      if (Object.keys(currentProductChange).length === 0) {
        const nextState = { ...state };

        if (typeof nextState[productId] !== 'undefined') {
          delete nextState[productId];
        }

        return nextState;
      }

      return {
        ...state,
        [productId]: {
          ...currentProductChange,
        },
      };
    }

    case CLEAN_PRODUCT_CHANGES: {
      return {};
    }

    case CLEAN_PRODUCT_VARIATIONS_CHANGES: {
      const nextState = { ...state };
      delete nextState[productId];
      return nextState;
    }

    default: {
      return state;
    }
  }
};

export const getProductChanges = (state) => {
  // Filter just products
  return getProducts(state).items
    .reduce((changes, product) => {
      if (state[PRODUCT_CHANGES_REDUCER_NAME][product.id]) {
        return {
          ...changes,
          [product.id]: state[PRODUCT_CHANGES_REDUCER_NAME][product.id],
        };
      }

      return changes;
    }, {});
};

export const getProductVariationChanges = (state) => {
  return getProducts(state).items
    .map((product) => ({
      id: product.id,
      update: product.variations
        .map((id) => {
          if (state[PRODUCT_CHANGES_REDUCER_NAME][id]) {
            return { id, ...state[PRODUCT_CHANGES_REDUCER_NAME][id] };
          }
          return false;
        })
        .filter(Boolean),
    }))
    .filter((product) => product.update.length > 0)
    .reduce((changes, product) => ({
      ...changes,
      [product.id]: product.update,
    }), {});
};

export const setProductChange = (productId, field, originalValue = '', value = '') => {
  if ((originalValue || '').toString() === (value || '').toString()) {
    return {
      type: PRODUCT_CHANGE_DELETE,
      productId,
      field,
    };
  }

  return {
    type: PRODUCT_CHANGE_UPDATE,
    productId,
    field,
    value,
  };
};

export const getProductChange = (state, { productId }) => (
  state[PRODUCT_CHANGES_REDUCER_NAME][productId]
);

export const saveProductChanges = (changes = {}) => {
  return {
    types: {
      requestTypes: [SAVE_PRODUCT_CHANGES_FETCHING],
      successTypes: [SAVE_PRODUCT_CHANGES_SUCCESS, CLEAN_PRODUCT_CHANGES],
      failureTypes: [SAVE_PRODUCT_CHANGES_ERROR],
    },
    endpoint: 'wc/v3/products/batch',
    method: 'POST',
    body: {
      update: Object.keys(changes).map((id) => ({
        id,
        ...changes[id],
      })),
    },
  };
};

export const saveProductVariationsChanges = (productId, changes = {}) => {
  return {
    types: {
      requestTypes: [],
      successTypes: [PRODUCT_VARIATIONS_INVALIDATE, CLEAN_PRODUCT_VARIATIONS_CHANGES],
      failureTypes: [],
    },
    endpoint: `wc/v3/products/${productId}/variations/batch`,
    method: 'POST',
    body: {
      update: Object.keys(changes).map((id) => ({
        id,
        ...changes[id],
      })),
    },
    productId,
  };
};

export const cleanProductChanges = () => ({
  type: CLEAN_PRODUCT_CHANGES,
});
