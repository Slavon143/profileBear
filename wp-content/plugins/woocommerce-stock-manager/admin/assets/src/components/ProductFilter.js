import React, { useState, useRef } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import classnames from 'classnames';
import { getProductCategories } from '../store/productCategories';
import { getProductTypes } from '../store/productTypes';
import { getStockStatusOptions } from '../store/stockStatusOptions';
import styles from './ProductFilter.scss';
import useI18n from '../hooks/useI18n';

const mapStateToProps = (state) => ({
  productCategories: getProductCategories(state),
  productTypes: getProductTypes(state),
  stockStatusOptions: getStockStatusOptions(state),
});

const ProductFilter = (props) => {
  const {
    onChange,
    productCategories,
    productTypes,
    stockStatusOptions,
    className,
  } = props;

  const [filter, setFilter] = useState({
    sku: '',
    search: '',
    type: '',
    category: '',
    stock_status: '',
    order: 'desc',
    orderBy: 'date',
  });

  const timer = useRef(null);

  const { __ } = useI18n();

  const forwardChange = (nextFilter, timeout = 500) => {
    if (timer.current) {
      window.clearTimeout(timer.current);
    }

    timer.current = window.setTimeout(() => {
      onChange(nextFilter);
    }, timeout);
  };

  const handleChange = (item, postponeChange = false) => (event) => {
    event.persist();

    setFilter((state) => {
      const nextState = { ...state, [item]: event.target.value };
      forwardChange(nextState, (postponeChange ? 500 : 0));
      return nextState;
    });
  };

  return (
    <div className={classnames(className, styles.wrapper)}>
      <div className={styles.row}>
        <p className={styles.field}>
          <label htmlFor="woocommerce-product-manager-filter-sku" className={styles.label}>{__('SKU', 'woocommerce-stock-manager')}:</label>
          <input
            type="search"
            id="woocommerce-product-manager-filter-sku"
            value={filter.sku}
            onChange={handleChange('sku', true)}
            size={10}
          />
        </p>
        <p className={styles.field}>
          <label htmlFor="woocommerce-product-manager-filter-search" className={styles.label}>{__('Search', 'woocommerce-stock-manager')}:</label>
          <input
            type="search"
            id="woocommerce-product-manager-filter-search"
            value={filter.search}
            onChange={handleChange('search', true)}
            size={50}
          />
        </p>
      </div>
      <div className={styles.row}>
        <p className={styles.field}>
          <label htmlFor="woocommerce-product-manager-filter-category" className={styles.label}>{__('Category', 'woocommerce-stock-manager')}:</label>
          <select
            id="woocommerce-product-manager-filter-category"
            value={filter.category}
            onChange={handleChange('category')}
          >
            <option value="">{__('All', 'woocommerce-stock-manager')}</option>
            {Object.keys(productCategories).map((key) => (
              <option value={key} key={key}>{productCategories[key]}</option>
            ))}
          </select>
        </p>
        <p className={styles.field}>
          <label htmlFor="woocommerce-product-manager-filter-type" className={styles.label}>{__('Type', 'woocommerce-stock-manager')}:</label>
          <select
            id="woocommerce-product-manager-filter-type"
            value={filter.type}
            onChange={handleChange('type')}
          >
            <option value="">{__('All', 'woocommerce-stock-manager')}</option>
            {Object.keys(productTypes).map((key) => (
              <option value={key} key={key}>{productTypes[key]}</option>
            ))}
          </select>
        </p>
        <p className={styles.field}>
          <label htmlFor="woocommerce-product-manager-filter-stock-status" className={styles.label}>{__('Stock status', 'woocommerce-stock-manager')}:</label>
          <select
            id="woocommerce-product-manager-filter-stock-status"
            value={filter.stock_status}
            onChange={handleChange('stock_status')}
          >
            <option value="">{__('All', 'woocommerce-stock-manager')}</option>
            {Object.keys(stockStatusOptions).map((key) => (
              <option value={key} key={key}>{stockStatusOptions[key]}</option>
            ))}
          </select>
        </p>
      </div>
    </div>
  );
};

ProductFilter.propTypes = {
  onChange: PropTypes.func.isRequired,
  productCategories: PropTypes.object,
  productTypes: PropTypes.object.isRequired,
  stockStatusOptions: PropTypes.object.isRequired,
  className: PropTypes.string,
};

ProductFilter.defaultProps = {
  productCategories: {},
  className: undefined,
};

export default connect(mapStateToProps)(ProductFilter);
