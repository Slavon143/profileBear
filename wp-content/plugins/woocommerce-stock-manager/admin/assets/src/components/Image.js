import React, { useEffect } from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { getImage, fetchImage } from '../store/image';

const mapStateToProps = (state, ownProps) => ({
  image: getImage(state, ownProps),
});

const mapDispatchToProps = {
  fetchImage,
};

const Image = (props) => {
  const {
    id,
    fetchImage,
    image,
    size,
    className,
  } = props;

  useEffect(() => {
    window.setTimeout(() => {
      fetchImage(id);
    }, 0);
  }, [fetchImage, id]);

  if (!image.lastUpdated) {
    return <span>&nbsp;</span>;
  }

  let imageDetails = image.media_details || {}
  let imageSizes = imageDetails.sizes || {}
  let sizedImage = imageSizes[size] || ''

  if (!sizedImage) {
    sizedImage = imageSizes.thumbnail || '';
  }

  return (
    <img
      src={sizedImage.source_url}
      width={sizedImage.width}
      height={sizedImage.height}
      alt={image.alt_text}
      className={className}
    />
  );
};

Image.propTypes = {
  id: PropTypes.number.isRequired,
  fetchImage: PropTypes.func.isRequired,
  image: PropTypes.object,
  size: PropTypes.string,
  className: PropTypes.string,
};

Image.defaultProps = {
  image: {
    isFetching: false,
  },
  size: 'thumbnail',
  className: undefined,
};

export default connect(mapStateToProps, mapDispatchToProps)(Image);
