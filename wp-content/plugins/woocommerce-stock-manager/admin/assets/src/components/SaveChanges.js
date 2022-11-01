import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import classnames from 'classnames';
import {
  getProductChanges,
  getProductVariationChanges,
  saveProductChanges,
  cleanProductChanges,
  saveProductVariationsChanges,
} from '../store/productChanges';
import styles from './SaveChanges.scss';
import useI18n from '../hooks/useI18n';

const mapStateToProps = (state) => ({
  productChanges: getProductChanges(state),
  productVariationChanges: getProductVariationChanges(state),
});

const mapDispatchToProps = {
  saveProductChanges,
  cleanProductChanges,
  saveProductVariationsChanges,
};

const SaveChanges = (props) => {
  const {
    className,
    productChanges,
    productVariationChanges,
    saveProductChanges,
    saveProductVariationsChanges,
    cleanProductChanges,
  } = props;

  const { __ } = useI18n();

  if (Object.keys(productChanges).length + Object.keys(productVariationChanges).length === 0) {
    return null;
  }

  const handleSave = () => {
    saveProductChanges(productChanges);

    Object.keys(productVariationChanges).forEach((productId) => {
      saveProductVariationsChanges(productId, productVariationChanges[productId]);
    });
  };

  const handleDiscart = () => {
    cleanProductChanges();
  };

  const wpcontent = document.getElementById('wpcontent').getBoundingClientRect();

  return (
    <div
      className={classnames(className, styles.wrapper)}
      style={{
        left: wpcontent.x,
      }}
    >
      <button onClick={handleDiscart} className="button button-large" type="button">{__('Discard changes', 'woocommerce-stock-manager')}</button>
      <button onClick={handleSave} className="button button-primary button-large" type="button">{__('Save all changes', 'woocommerce-stock-manager')}</button>
    </div>
  );
};

SaveChanges.propTypes = {
  className: PropTypes.string,
  productChanges: PropTypes.object,
  productVariationChanges: PropTypes.object,
  saveProductChanges: PropTypes.func.isRequired,
  cleanProductChanges: PropTypes.func.isRequired,
  saveProductVariationsChanges: PropTypes.func.isRequired,
};

SaveChanges.defaultProps = {
  className: undefined,
  productChanges: {},
  productVariationChanges: {},
};

export default connect(mapStateToProps, mapDispatchToProps)(SaveChanges);
