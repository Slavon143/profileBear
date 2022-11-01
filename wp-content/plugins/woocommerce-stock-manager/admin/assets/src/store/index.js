import { combineReducers, createStore, applyMiddleware } from 'redux';
import { composeWithDevTools } from 'redux-devtools-extension/logOnlyInProduction';

import thunkMiddleware from 'redux-thunk';
import wpApiMiddleware from '../middlewares/wpApiMiddleware';

import { PRODUCTS_REDUCER_NAME, productsReducer } from './products';
import { PRODUCT_REDUCER_NAME, productReducer } from './product';
import { PRODUCT_CATEGORIES_REDUCER_NAME, productCategoriesReducer } from './productCategories';
import { IMAGE_REDUCER_NAME, imageReducer } from './image';
import { APP_REDUCER_NAME, appReducer } from './app';
import { PRODUCT_TYPES_REDUCER_NAME, productTypesReducer } from './productTypes';
import { STOCK_STATUS_OPTIONS_REDUCER_NAME, stockStatusOptionsReducer } from './stockStatusOptions';
import { SHIPPING_CLASSES_REDUCER_NAME, shippingClassesReducer } from './shippingClasses';
import { TAX_CLASSES_REDUCER_NAME, taxClassesReducer } from './taxClasses';
import { TAX_STATUSES_REDUCER_NAME, taxStatusesReducer } from './taxStatuses';
import { BACKORDERS_OPTIONS_REDUCER_NAME, backordersOptionsReducer } from './backordersOptions';
import { PRODUCT_CHANGES_REDUCER_NAME, productChangesReducer } from './productChanges';
import { PRODUCT_VARIATIONS_REDUCER_NAME, productVariationsReducer } from './productVariations';
import { SCREEN_OPTIONS_REDUCER_NAME, screenOptionsReducer } from './screenOptions';

const configureStore = (preloadedState) => {
  const rootReducer = combineReducers({
    [PRODUCT_REDUCER_NAME]: productReducer,
    [PRODUCTS_REDUCER_NAME]: productsReducer,
    [PRODUCT_CATEGORIES_REDUCER_NAME]: productCategoriesReducer,
    [IMAGE_REDUCER_NAME]: imageReducer,
    [APP_REDUCER_NAME]: appReducer,
    [PRODUCT_TYPES_REDUCER_NAME]: productTypesReducer,
    [STOCK_STATUS_OPTIONS_REDUCER_NAME]: stockStatusOptionsReducer,
    [SHIPPING_CLASSES_REDUCER_NAME]: shippingClassesReducer,
    [TAX_CLASSES_REDUCER_NAME]: taxClassesReducer,
    [TAX_STATUSES_REDUCER_NAME]: taxStatusesReducer,
    [BACKORDERS_OPTIONS_REDUCER_NAME]: backordersOptionsReducer,
    [PRODUCT_CHANGES_REDUCER_NAME]: productChangesReducer,
    [PRODUCT_VARIATIONS_REDUCER_NAME]: productVariationsReducer,
    [SCREEN_OPTIONS_REDUCER_NAME]: screenOptionsReducer,
  });

  const middlewares = [thunkMiddleware, wpApiMiddleware];
  const middlewareEnhancer = applyMiddleware(...middlewares);

  const enhancers = [middlewareEnhancer];
  const composedEnhancers = composeWithDevTools(...enhancers);

  const store = createStore(rootReducer, preloadedState, composedEnhancers);

  return store;
};

export default configureStore;
