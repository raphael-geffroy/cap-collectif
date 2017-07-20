/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';

import IdeasIndexPage from './IdeasIndexPage';

const props = {
  count: 5,
  countTrashed: 0,
  ideas: [],
  themes: [],
};

describe('<IdeasIndexPage />', () => {
  it('it should render ideas index page', () => {
    const wrapper = shallow(<IdeasIndexPage {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
