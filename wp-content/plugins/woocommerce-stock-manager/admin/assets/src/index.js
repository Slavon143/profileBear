import React from 'react';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import throttle from 'lodash/throttle';
import App from './components/App';
import configureStore from './store';
import { loadState, saveState } from './localStorage';

document.addEventListener('DOMContentLoaded', () => {
  const domContainer = document.querySelector('#woocommerce-stock-manager-app');
  const store = configureStore({
    ...WooCommerceStockManagerPreloadedState,
    ...loadState(),
  });

  store.subscribe(throttle(() => {
    saveState({
      'screen-options': store.getState()['screen-options'],
    });
  }, 1000));

  if (domContainer) {
    ReactDOM.render(
      <Provider store={store}>
        <App />
      </Provider>,
      domContainer,
    );
  }
});
