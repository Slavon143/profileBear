import React, { useRef, useEffect } from 'react';
import PropTypes from 'prop-types';

const InfiniteScroll = ({ enabled, onVisible }) => {
  const infiniteScroller = useRef(null);
  const observer = useRef(null);

  useEffect(() => {
    const handleOnVisible = (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && enabled) {
          onVisible();
        }
      });
    };

    if (enabled && infiniteScroller.current !== null && observer.current === null) {
      observer.current = new IntersectionObserver(handleOnVisible);
      observer.current.observe(infiniteScroller.current);
    }

    return () => {
      if (observer.current !== null) {
        observer.current.disconnect();
        observer.current = null;
      }
    };
  });

  return enabled ? <div ref={infiniteScroller} /> : null;
};

InfiniteScroll.propTypes = {
  onVisible: PropTypes.func.isRequired,
  enabled: PropTypes.bool,
};

InfiniteScroll.defaultProps = {
  enabled: false,
};

export default InfiniteScroll;
