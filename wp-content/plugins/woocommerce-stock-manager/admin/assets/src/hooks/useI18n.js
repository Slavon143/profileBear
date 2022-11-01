import { useContext } from 'react';
import { ReactReduxContext } from 'react-redux';
import { getTextDomain } from '../store/app';

const useI18n = () => {
  const redux = useContext(ReactReduxContext);
  const textDomain = getTextDomain(redux.store.getState());

  const { __ } = wp.i18n;
  return { __, textDomain };
};

export default useI18n;
