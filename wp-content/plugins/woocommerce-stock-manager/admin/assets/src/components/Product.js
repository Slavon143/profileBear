import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import Image from './Image';
import Control from './Control';
import { getAdminUrl, getLowStockThreshold } from '../store/app';
import { getShippingClasses } from '../store/shippingClasses';
import { getStockStatusOptions } from '../store/stockStatusOptions';
import { getProductTypes } from '../store/productTypes';
import { getTaxClasses } from '../store/taxClasses';
import { getTaxStatuses } from '../store/taxStatuses';
import { getBackordersOptions } from '../store/backordersOptions';
import { setProductChange, getProductChange } from '../store/productChanges';
import { fetchProductVariations, getProductVariations } from '../store/productVariations';
import styles from './Product.scss';
import useI18n from '../hooks/useI18n';

const mapStateToProps = (state, ownProps) => ({
  shippingClasses: getShippingClasses(state),
  stockStatusOptions: getStockStatusOptions(state),
  productTypes: getProductTypes(state),
  taxClasses: getTaxClasses(state),
  taxStatuses: getTaxStatuses(state),
  backordersOptions: getBackordersOptions(state),
  adminUrl: getAdminUrl(state),
  lowStockThreshold: getLowStockThreshold(state),
  productChange: getProductChange(state, { productId: ownProps.product.id }),
  productVariations: getProductVariations(state, { productId: ownProps.product.id }),
});

const mapDispatchToProps = {
  setProductChange,
  fetchProductVariations,
};

const Product = (props) => {
  const {
    product,
    settings,
    className,
    adminUrl,
    shippingClasses,
    stockStatusOptions,
    productTypes,
    taxClasses,
    taxStatuses,
    backordersOptions,
    setProductChange,
    productChange = {},
    fetchProductVariations,
    productVariations,
    isVariation,
    lowStockThreshold,
  } = props;

  const [showVariations, setShowVariations] = useState(false);

  useEffect(() => {
    if (showVariations && (
      (productVariations.links && productVariations.links.next)
      || !productVariations.lastUpdated
      || productVariations.didInvalidate
    )) {
      fetchProductVariations(
        { productId: product.id },
        productVariations.links && productVariations.links.next,
      );
    }
  }, [
    fetchProductVariations,
    product.id,
    productVariations.links,
    productVariations.lastUpdated,
    productVariations.didInvalidate,
    showVariations,
  ]);

  const { __ } = useI18n();

  const handleChange = (field, originalValue) => (value) => {
    setProductChange(product.id, field, originalValue, value);
  };

  const getChangedStyle = (field) => {
    if (typeof productChange[field] === 'undefined') {
      return styles.normal;
    }

    return styles.changed;
  };

  const getChangedValue = (field) => (
    typeof productChange[field] === 'undefined' ? product[field] : productChange[field]
  );

  const handleShowVariations = () => {
    setShowVariations((state) => !state);
  };

  const stockCssLow = getChangedValue('manage_stock') && (
    parseInt(getChangedValue('stock_quantity'), 10) > lowStockThreshold ? 'ok' : 'low'
  );

  const stockCssZero = getChangedValue('manage_stock') && (
    parseInt(getChangedValue('stock_quantity'), 10) > 0 ? false : 'zero'
  );

  const isVariable = product.type && product.type.startsWith('variable');

  product.sale_price = (product.sale_price == '' && product.price == '0') ? '0' : product.sale_price;

  return (
    <>
      <tr
        className={className}
        style={isVariation ? ({
          background: 'rgba(0, 0, 0, 0.05)',
        }) : null}
      >
        <td
          className={classnames(isVariation && styles.variationId, 'stock-manager-field-id', {
            'stock-manager-field-id--is-variation': isVariation,
          })}
        >
          {!isVariation ? (
            <a href={`${adminUrl}post.php?post=${product.id}&action=edit`} target="_blank" rel="noopener noreferrer">
              <strong>#{product.id}</strong>
            </a>
          ) : `↳ #${product.id}`}
        </td>
        <td className={classnames(styles.productType, 'stock-manager-field-product-type', `stock-manager-field-product-type--${product.type}`)}>
          {/* eslint-disable-next-line no-nested-ternary */}
          {isVariable ? (
            <button type="button" onClick={handleShowVariations} className="button">
              {productTypes[product.type]} ({product.variations.length})
            </button>
          ) : isVariation ? (
            __('Variation', 'woocommerce-stock-manager')
          ) : (
            productTypes[product.type]
          )}
        </td>
        {settings.sku && (
          <td className={classnames(getChangedStyle('sku'), styles.inputCell, 'stock-manager-field-sku')}>
            <Control
              type="text"
              value={getChangedValue('sku')}
              onChange={handleChange('sku', product.sku)}
              size={10}
            />
          </td>
        )}
        {settings.thumbnail && (
          <td className="stock-manager-field-thumbnail">
            {((product.images && product.images.length > 0) || (product.hasOwnProperty('image'))) && (
              <Image id={(typeof product.image === 'object' && product.image !== null) ? product.image.id : ((product.images && product.images.length > 0) ? product.images[0].id : 0)} className={styles.thumbnail} />
            )}
          </td>
        )}
        {settings.productName && (
          isVariation ? (
            <td className="stock-manager-field-product-name stock-manager-field-product-name--disabled">
              {product.attributes.map((attribute) => attribute.option).join(', ')}
            </td>
          ) : (
            <td className={classnames(getChangedStyle('name'), styles.inputCell, 'stock-manager-field-product-name')}>
              <Control
                type="text"
                value={getChangedValue('name')}
                onChange={handleChange('name', product.name)}
                size={60}
              />
            </td>
          )
        )}
        {settings.taxStatus && (
          <td className={classnames(getChangedStyle('tax_status'), 'stock-manager-field-tax-status', `stock-manager-field-tax-status--${getChangedValue('tax_status')}`)}>
            {!isVariation && (
              <Control
                type="select"
                value={getChangedValue('tax_status')}
                onChange={handleChange('tax_status', product.tax_status)}
                options={taxStatuses}
                required
              />
            )}
          </td>
        )}
        {settings.taxClass && (
          <td className={classnames(getChangedStyle('tax_class'), 'stock-manager-field-tax-class', `stock-manager-field-tax-class--${getChangedValue('tax_class')}`)}>
            <Control
              type="select"
              value={getChangedValue('tax_class') || ''}
              onChange={handleChange('tax_class', product.tax_class)}
              options={taxClasses}
              required
            />
          </td>
        )}
        {settings.shippingClass && (
          <td className={classnames(getChangedStyle('shipping_class'), 'stock-manager-field-shipping-class', `stock-manager-field-shipping-class--${getChangedValue('shipping_class')}`)}>
            <Control
              type="select"
              value={getChangedValue('shipping_class') || ''}
              onChange={handleChange('shipping_class', product.shipping_class)}
              options={shippingClasses}
              required
            />
          </td>
        )}
        {settings.price && (
          <td className={classnames(getChangedStyle('regular_price'), styles.inputCell, 'stock-manager-field-regular-price')}>
            <Control
              type="number"
              value={getChangedValue('regular_price') ? parseFloat(getChangedValue('regular_price')) : ''}
              onChange={handleChange('regular_price', parseFloat(product.price))}
              size={8}
              className={styles.numberControl}
            />
          </td>
        )}
        {settings.salePrice && (
          <td className={classnames(getChangedStyle('sale_price'), styles.inputCell, 'stock-manager-field-sale-price')}>
            <Control
              type="number"
              value={getChangedValue('sale_price') ? parseFloat(getChangedValue('sale_price')) : ''}
              onChange={handleChange('sale_price', parseFloat(product.sale_price))}
              size={8}
              className={styles.numberControl}
            />
          </td>
        )}
        {settings.weight && (
          <td className={classnames(getChangedStyle('weight'), styles.inputCell, 'stock-manager-field-weight')}>
            <Control
              type="number"
              value={getChangedValue('weight') ? parseFloat(getChangedValue('weight')) : ''}
              onChange={handleChange('weight', parseFloat(product.weight))}
              size={8}
              className={styles.numberControl}
            />
          </td>
        )}
        {settings.manageStock && (
          <td className={classnames(getChangedStyle('manage_stock'), 'stock-manager-field-manage-stock', `stock-manager-field-manage-stock--${getChangedValue('manage_stock').toString()}`)}>
            {product.type !== 'grouped' && product.type !== 'external' && (
              <Control
                type="checkbox"
                value={getChangedValue('manage_stock')}
                onChange={handleChange('manage_stock', product.manage_stock)}
              />
            )}
          </td>
        )}
        {settings.stockStatus && (
          <td className={classnames(getChangedStyle('stock_status'), 'stock-manager-field-stock-status', `stock-manager-field-stock-status--${getChangedValue('stock_status')}`)}>
            {product.type !== 'grouped' && product.type !== 'external' && !isVariable && (
              getChangedValue('manage_stock') ? (
                stockStatusOptions[getChangedValue('stock_status')]
              ) : (
                <Control
                  type="select"
                  value={getChangedValue('stock_status')}
                  onChange={handleChange('stock_status', product.stock_status)}
                  options={stockStatusOptions}
                />
              )
            )}
          </td>
        )}
        {settings.backorders && (
          <td className={classnames(getChangedStyle('backorders'), 'stock-manager-field-backorders', `stock-manager-field-backorders--${getChangedValue('backorders')}`)}>
            {product.type !== 'grouped' && product.type !== 'external' && !isVariable && (
              <Control
                type="select"
                value={getChangedValue('backorders')}
                onChange={handleChange('backorders', product.backorders)}
                options={backordersOptions}
                required
              />
            )}
          </td>
        )}
        {settings.stock && (
          <td
            className={classnames(getChangedStyle('stock_quantity'), styles.inputCell, 'stock-manager-field-stock-quantity', {
              [`stock-manager-field-stock-quantity--${parseInt(getChangedValue('stock_quantity'), 10)}`]: false,
              [`stock-manager-field-stock-quantity--${stockCssLow}`]: stockCssLow,
              [`stock-manager-field-stock-quantity--${stockCssZero}`]: stockCssZero,
            })}
          >
            {getChangedValue('manage_stock') && product.type !== 'grouped' && product.type !== 'external' && !isVariable && (
              <Control
                type="number"
                value={getChangedValue('stock_quantity') ? parseInt(getChangedValue('stock_quantity'), 10) : ''}
                onChange={handleChange('stock_quantity', parseInt(product.stock_quantity, 10))}
                size={5}
                className={styles.numberControl}
              />
            )}
          </td>
        )}
      </tr>
      {showVariations
      && productVariations.items
      && productVariations.items.map((productVariation) => (
        <ConnectedProduct
          product={productVariation}
          settings={settings}
          key={productVariation.id}
          isVariation
        />
      ))}
    </>
  );
};

Product.propTypes = {
  product: PropTypes.object.isRequired,
  settings: PropTypes.object.isRequired,
  className: PropTypes.string,
  adminUrl: PropTypes.string.isRequired,
  shippingClasses: PropTypes.object.isRequired,
  stockStatusOptions: PropTypes.object.isRequired,
  productTypes: PropTypes.object.isRequired,
  taxClasses: PropTypes.object.isRequired,
  taxStatuses: PropTypes.object.isRequired,
  backordersOptions: PropTypes.object.isRequired,
  setProductChange: PropTypes.func.isRequired,
  productChange: PropTypes.object,
  fetchProductVariations: PropTypes.func.isRequired,
  productVariations: PropTypes.object,
  isVariation: PropTypes.bool,
  lowStockThreshold: PropTypes.number,
};

Product.defaultProps = {
  className: undefined,
  productChange: {},
  productVariations: {},
  isVariation: false,
};

const ConnectedProduct = connect(mapStateToProps, mapDispatchToProps)(Product);

export default ConnectedProduct;
