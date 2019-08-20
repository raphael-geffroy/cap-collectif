// @flow
import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import IntlProvider from './IntlProvider';
import EventAdmin, { type Props } from '../components/Event/Admin/EventAdmin';

export default (props: Props) => (
  <Provider store={ReactOnRails.getStore('appStore')}>
    <IntlProvider>
      <EventAdmin {...props} />
    </IntlProvider>
  </Provider>
);