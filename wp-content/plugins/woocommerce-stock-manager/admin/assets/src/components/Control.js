import React, { useState, useRef } from 'react';
import PropTypes from 'prop-types';

const Control = (props) => {
  const {
    className,
    value = '',
    type,
    onChange,
    options,
    required,
    ...rest
  } = props;

  const previousValue = useRef(value);

  const [currentValue, setCurrentValue] = useState(value);

  if (previousValue.current !== value) {
    previousValue.current = value;
    setCurrentValue(value);
  }

  const handleChange = (event) => {
    const nextValue = ['checkbox', 'radio'].includes(type)
      ? event.target.checked
      : event.target.value;

    setCurrentValue(nextValue);

    if (['select', 'checkbox', 'radio'].includes(type)) {
      onChange(nextValue);
    }
  };

  const handleBlur = () => {
    onChange(currentValue);
  };

  if (type === 'select') {
    return (
      <select
        required={required}
        className={className}
        value={currentValue}
        onChange={handleChange}
        {...rest}
      >
        {!required && (
          <option value="" />
        )}
        {Object.keys(options).map((key) => (
          <option value={key} key={key}>{options[key]}</option>
        ))}
      </select>
    );
  }

  if (['checkbox', 'radio'].includes(type)) {
    return (
      <input
        required={required}
        className={className}
        type={type}
        defaultChecked={currentValue}
        onChange={handleChange}
        {...rest}
      />
    );
  }

  return (
    <input
      required={required}
      className={className}
      type={type}
      value={currentValue}
      onChange={handleChange}
      onBlur={handleBlur}
      {...rest}
    />
  );
};

Control.propTypes = {
  className: PropTypes.string,
  value: PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number,
    PropTypes.bool,
  ]),
  type: PropTypes.oneOf(['text', 'number', 'checkbox', 'radio', 'select']),
  onChange: PropTypes.func.isRequired,
  options: PropTypes.object,
  required: PropTypes.bool,
};

Control.defaultProps = {
  className: undefined,
  type: 'text',
  value: '',
  options: {},
  required: false,
};

export default Control;
