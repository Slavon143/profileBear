import React, { useEffect, useState } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { getProducts, fetchProducts, resetProducts } from '../store/products';
import { fetchImages, getMissingImages } from '../store/image';
import { getScreenOptions } from '../store/screenOptions';
import useI18n from '../hooks/useI18n';
import ProductFilter from './ProductFilter';
import ScreenOptions from './ScreenOptions';
import Product from './Product';
import SaveChanges from './SaveChanges';
import InfiniteScroll from './InfiniteScroll';
import styles from './Products.scss';

const mapStateToProps = (state) => ({
  products: getProducts(state),
  missingImages: getMissingImages(state, {
    ids: getProducts(state).items.map((product) => (
      product.images.length > 0 ? product.images[0].id : ((product.hasOwnProperty('image')) ? product.image : false)
    )).filter(Boolean),
  }),
  settings: getScreenOptions(state).settings,
});

const mapDispatchToProps = {
  fetchProducts,
  fetchImages,
  resetProducts,
};

const Products = (props) => {
  const {
    products,
    missingImages,
    fetchProducts,
    fetchImages,
    resetProducts,
    className,
    settings,
  } = props;

  const { __ } = useI18n();
  const [filter, setFilter] = useState({});

  useEffect(() => {
    if (settings.thumbnail && missingImages) {
      fetchImages(missingImages);
    }
  }, [fetchImages, settings.thumbnail, missingImages]);

  if (products.error) {
    return <div>{__('Loading failed', 'woocommerce-stock-manager')}</div>;
  }

  const handleOnLoad = () => {
    if (products.links.next) {
      fetchProducts(filter, products.links.next);
    }
  };

  const handleOrder = (orderBy, order) => () => {
    setFilter((filter) => ({
      ...filter,
      order,
      orderBy,
    }));
    resetProducts();
  };

  const handleFilter = (filter) => {
    setFilter(filter);
    resetProducts();
  };

  return (
    <div className={className}>
      <ProductFilter onChange={handleFilter} />
      <ScreenOptions />
      <table className={classnames('wp-list-table widefat striped posts', styles.table)}>
        <thead>
          <tr>
            <th
              className={classnames('manage-column', 'column-id', {
                sorted: filter.orderBy === 'id',
                sortable: filter.orderBy !== 'id',
                asc: filter.orderBy === 'id' && filter.order === 'asc',
                desc: filter.orderBy === 'id' && filter.order === 'desc',
              })}
            >
              {/* eslint-disable-next-line */}
              <a onClick={handleOrder('id', (filter.orderBy === 'id' && filter.order === 'desc') ? 'asc' : 'desc')}>
                <span>{__('ID', 'woocommerce-stock-manager')}</span>
                <span className="sorting-indicator" />
              </a>
            </th>
            <th className="manage-column">{__('Product type', 'woocommerce-stock-manager')}</th>
            {settings.sku && (
              <th className="manage-column">{__('SKU', 'woocommerce-stock-manager')}</th>
            )}
            {settings.thumbnail && (
              <th className="manage-column">{__('Thumbnail', 'woocommerce-stock-manager')}</th>
            )}
            {settings.productName && (
              <th
                className={classnames('manage-column', 'column-name', {
                  sorted: filter.orderBy === 'title',
                  sortable: filter.orderBy !== 'title',
                  asc: filter.orderBy === 'title' && filter.order === 'asc',
                  desc: filter.orderBy === 'title' && filter.order === 'desc',
                })}
              >
                {/* eslint-disable-next-line */}
                <a onClick={handleOrder('title', (filter.orderBy === 'title' && filter.order === 'desc') ? 'asc' : 'desc')}>
                  <span>{__('Product name', 'woocommerce-stock-manager')}</span>
                  <span className="sorting-indicator" />
                </a>
              </th>
            )}
            {settings.taxStatus && (
              <th className="manage-column">{__('Tax status', 'woocommerce-stock-manager')}</th>
            )}
            {settings.taxClass && (
              <th className="manage-column">{__('Tax class', 'woocommerce-stock-manager')}</th>
            )}
            {settings.shippingClass && (
              <th className="manage-column">{__('Shipping class', 'woocommerce-stock-manager')}</th>
            )}
            {settings.price && (
              <th className="manage-column">{__('Price', 'woocommerce-stock-manager')}</th>
            )}
            {settings.salePrice && (
              <th className="manage-column">{__('Sale price', 'woocommerce-stock-manager')}</th>
            )}
            {settings.weight && (
              <th className="manage-column">{__('Weight', 'woocommerce-stock-manager')}</th>
            )}
            {settings.manageStock && (
              <th className="manage-column">{__('Manage stock', 'woocommerce-stock-manager')}</th>
            )}
            {settings.stockStatus && (
              <th className="manage-column">{__('Stock status', 'woocommerce-stock-manager')}</th>
            )}
            {settings.backorders && (
              <th className="manage-column">{__('Backorders', 'woocommerce-stock-manager')}</th>
            )}
            {settings.stock && (
              <th className="manage-column">{__('Stock', 'woocommerce-stock-manager')}</th>
            )}
          </tr>
        </thead>
        <tbody>
          {products.items.map((product) => (
            <Product product={product} settings={settings} key={product.id} />
          ))}
        </tbody>
      </table>
      {products.isFetching && (
        <p>
          <strong>{__('Loading more results...', 'woocommerce-stock-manager')}</strong>
        </p>
      )}
      <InfiniteScroll
        onVisible={handleOnLoad}
        enabled={!!products.links.next && !products.isFetching}
      />
      <SaveChanges />
    </div>
  );
};

Products.propTypes = {
  products: PropTypes.shape({
    isFetching: PropTypes.bool.isRequired,
    items: PropTypes.array.isRequired,
    meta: PropTypes.object,
  }),
  fetchProducts: PropTypes.func.isRequired,
  fetchImages: PropTypes.func.isRequired,
  resetProducts: PropTypes.func.isRequired,
  className: PropTypes.string,
  missingImages: PropTypes.array,
  settings: PropTypes.object.isRequired,
};

Products.defaultProps = {
  products: {
    isFetching: true,
    items: [],
    meta: {},
  },
  missingImages: [],
  className: undefined,
};

export default connect(mapStateToProps, mapDispatchToProps)(Products);
