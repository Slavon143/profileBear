import React from 'react';
import PropTypes from 'prop-types';
import Products from './Products';

const App = ({ className }) => (
  <Products className={className} />
);

App.propTypes = {
  className: PropTypes.string,
};

App.defaultProps = {
  className: undefined,
};

export default App;
