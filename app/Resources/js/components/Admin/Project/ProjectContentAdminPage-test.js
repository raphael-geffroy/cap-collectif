// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import ProjectContentAdminPage from './ProjectContentAdminPage';

describe('<ProjectContentAdminPage />', () => {
  const defaultProps = {
    projectId: '1',
  };

  it('renders correctly when editing project', () => {
    const wrapper = shallow(<ProjectContentAdminPage {...defaultProps} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders correctly when no project', () => {
    const wrapper = shallow(<ProjectContentAdminPage projectId={null} />);
    expect(wrapper).toMatchSnapshot();
  });
});
