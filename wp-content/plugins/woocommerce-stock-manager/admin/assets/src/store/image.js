import {
  IMAGE_FETCHING,
  IMAGE_SUCCESS,
  IMAGE_ERROR,
  IMAGE_INVALIDATE,
  IMAGES_FETCHING,
  IMAGES_SUCCESS,
  IMAGES_ERROR,
} from './actionTypes';

const initialState = () => ({
  isFetching: false,
  didInvalidate: false,
});

export const IMAGE_REDUCER_NAME = 'image';

export const imageReducer = (state = initialState(), action) => {
  const {
    type,
    data,
    error,
    ids,
    id,
  } = action;

  switch (type) {
    case IMAGE_FETCHING:
      return {
        ...state,
        [id]: {
          ...(state[id] || null),
          isFetching: true,
          didInvalidate: false,
        },
      };

    case IMAGE_SUCCESS:
      return {
        ...state,
        [id]: {
          ...data,
          isFetching: false,
          didInvalidate: false,
          lastUpdated: new Date(),
        },
      };

    case IMAGE_ERROR:
      return {
        ...state,
        [id]: {
          ...(state[id] || null),
          error,
          isFetching: false,
          didInvalidate: false,
        },
      };

    case IMAGE_INVALIDATE:
      return {
        ...state,
        [id]: {
          ...(state[id] || null),
          didInvalidate: true,
        },
      };

    case IMAGES_FETCHING: {
      const nextState = {
        ...state,
        isFetching: true,
        didInvalidate: false,
      };

      ids.forEach((id) => {
        nextState[id] = {
          ...(nextState[id] || null),
          isFetching: true,
          didInvalidate: true,
        };
      });

      return nextState;
    }

    case IMAGES_SUCCESS: {
      const nextState = {
        ...state,
        isFetching: false,
        didInvalidate: false,
        lastUpdated: new Date(),
      };

      data.forEach((image) => {
        nextState[image.id] = {
          ...image,
          isFetching: false,
          didInvalidate: false,
          lastUpdated: new Date(),
        };
      });

      return nextState;
    }

    case IMAGES_ERROR: {
      const nextState = {
        ...state,
        isFetching: false,
        didInvalidate: false,
        error,
      };

      ids.forEach((id) => {
        nextState[id] = {
          ...(nextState[id] || null),
          isFetching: false,
          didInvalidate: false,
          error,
        };
      });

      return nextState;
    }

    default:
      return state;
  }
};

export const getImage = (state, { id }) => state[IMAGE_REDUCER_NAME][id];

export const getMissingImages = (state, { ids = [] }) => ids.filter((id) => (
  !state[IMAGE_REDUCER_NAME][id]
));

export const fetchImage = (id) => ({
  types: {
    requestTypes: [IMAGE_FETCHING],
    successTypes: [IMAGE_SUCCESS],
    failureTypes: [IMAGE_ERROR],
  },
  endpoint: `wp/v2/media/${id}`,
  id,
  shouldCallAPI: (state) => {
    const image = state[IMAGE_REDUCER_NAME][id];

    if (!image) {
      return true;
    }

    if (image.isFetching) {
      return false;
    }

    return image.didInvalidate || !image.lastUpdated;
  },
});

export const fetchImages = (ids = []) => ({
  types: {
    requestTypes: [IMAGES_FETCHING],
    successTypes: [IMAGES_SUCCESS],
    failureTypes: [IMAGES_ERROR],
  },
  endpoint: 'wp/v2/media',
  query: { include: ids.join() },
  ids,
  shouldCallAPI: (state) => {
    const images = state[IMAGE_REDUCER_NAME];

    if (images.isFetching) {
      return false;
    }

    return ids.map((id) => !!images[id]).includes(false);
  },
});
