import { getAppState } from '../store/app';

const wpApiMiddleware = ({ dispatch, getState }) => (next) => async (action) => {
  const {
    types,
    endpoint,
    shouldCallAPI = () => true,
    query = {},
    body = {},
    method = 'GET',
    ...rest
  } = action;

  if (!types || !endpoint) {
    return next(action);
  }

  const state = getState();

  if (!shouldCallAPI(state)) {
    return null;
  }

  const {
    requestTypes = ['REQUEST'],
    successTypes = ['SUCCESS'],
    failureTypes = ['FAILURE'],
  } = types;

  const meta = { ...query, ...body };

  requestTypes.forEach((requestType) => dispatch({
    headers: {},
    meta,
    type: requestType,
    query,
    body,
    ...rest,
  }));

  const { root, nonce, perPage } = getAppState(state);

  if (method === 'GET') {
    query.per_page = perPage;
  }

  const url = wp.url.addQueryArgs(
    root + endpoint,
    Object.keys(query)
      .filter((key) => !!query[key])
      .reduce((q, key) => ({ ...q, [key]: query[key] }), {}),
  );

  try {
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': nonce,
      },
      ...(method !== 'GET' ? { body: JSON.stringify(body) } : null),
    });

    if (!response.ok) {
      throw new Error(`${response.url}\n${response.status} ${response.statusText}`);
    }

    const headers = [...response.headers.entries()].reduce((acc, [key, value]) => ({
      ...acc,
      [key]: value,
    }), {});

    const links = response.headers.get('link')
      .split(',')
      .map((link) => {
        const parser = /<(?<url>[^>]+)>;\s?rel="(?<rel>[^"]+)"/g;
        const result = parser.exec(link);
        return result ? result.groups : false;
      })
      .filter(Boolean)
      .reduce((acc, curr) => ({ ...acc, [curr.rel]: curr.url.replace(root, '') }), {});

    const data = await response.json();

    successTypes.forEach((successType) => dispatch({
      headers,
      links,
      data,
      query,
      body,
      type: successType,
      ...rest,
    }));

    return true;
  } catch (error) {
    console.error(error.toString());

    failureTypes.forEach((failureType) => dispatch({
      type: failureType,
      query,
      body,
      ...rest,
      error: `${error.name}: ${error.message}`,
    }));

    return false;
  }
};

export default wpApiMiddleware;
