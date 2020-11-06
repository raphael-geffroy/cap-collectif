// @flow
import { DEFAULT_FILTERS } from './DashboardMailingList.context';
import { getFieldsFromUrl } from '~/shared/utils/analysis-filters';

export type DashboardStatus = 'ready' | 'loading';

export type Filters = {|
  +term: ?string,
|};

export type DashboardState = {|
  +status: DashboardStatus,
  +filters: Filters,
|};

export type DashboardParameters = {|
  +filters: $PropertyType<DashboardState, 'filters'>,
|};

export type Action =
  | { type: 'START_LOADING' }
  | { type: 'STOP_LOADING' }
  | { type: 'SEARCH_TERM', payload: ?string }
  | { type: 'CLEAR_TERM' }
  | { type: 'INIT_FILTERS_FROM_URL' };

const url = new URL(window.location.href);

export const createReducer = (state: DashboardState, action: Action) => {
  switch (action.type) {
    case 'START_LOADING':
      return {
        ...state,
        status: 'loading',
      };
    case 'STOP_LOADING':
      return {
        ...state,
        status: 'ready',
      };
    case 'SEARCH_TERM':
      return {
        ...state,
        filters: {
          ...state.filters,
          term: action.payload,
        },
      };
    case 'CLEAR_TERM':
      return {
        ...state,
        filters: {
          ...state.filters,
          term: null,
        },
      };
    case 'INIT_FILTERS_FROM_URL': {
      const filters = getFieldsFromUrl<Filters>(url, {
        default: DEFAULT_FILTERS,
      });

      return {
        ...state,
        filters,
      };
    }
    default:
      throw new Error(`Unknown action : ${action.type}`);
  }
};