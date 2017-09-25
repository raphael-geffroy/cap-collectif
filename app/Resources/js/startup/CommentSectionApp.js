import React from 'react';
import { Provider } from 'react-redux';
import ReactOnRails from 'react-on-rails';
import { IntlProvider } from 'react-intl-redux';
import CommentSection from '../components/Comment/CommentSection';

export default props => (
  <Provider store={ReactOnRails.getStore('appStore')}>
    <IntlProvider>
      <CommentSection {...props} />
    </IntlProvider>
  </Provider>
);
