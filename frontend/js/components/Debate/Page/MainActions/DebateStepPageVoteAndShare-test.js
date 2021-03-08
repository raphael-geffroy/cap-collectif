// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { DebateStepPageVoteAndShare } from './DebateStepPageVoteAndShare';
import { $refType, $fragmentRefs } from '~/mocks';

const baseProps = {
  body: 'blabla le body',
  isAuthenticated: true,
  url: 'step/123',
  step: {
    url: 'step/123',
    $refType,
    $fragmentRefs,
    debate: {
      id: 'debate1',
      $fragmentRefs,
      votes: { totalCount: 5 },
      yesVotes: { totalCount: 4 },
      allArguments: {
        totalCount: 10,
      },
      argumentsFor: {
        totalCount: 5,
      },
      argumentsAgainst: {
        totalCount: 5,
      },
    },
    timeless: false,
    timeRange: {
      startAt: '2030-02-10 00:00:00',
      endAt: '2030-03-10 00:00:00',
    },
  },
  isMobile: false,
  viewerIsConfirmedByEmail: true,
};

const props = {
  basic: baseProps,
  onMobile: {
    ...baseProps,
    isMobile: true,
  },
  timelessStep: {
    ...baseProps,
    step: {
      ...baseProps.step,
      timeless: true,
      timeRange: {
        startAt: null,
        endAt: null,
      },
    },
  },
};

describe('<DebateStepPageVoteAndShare/>', () => {
  it('renders correctly', () => {
    const wrapper = shallow(<DebateStepPageVoteAndShare {...props.basic} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders correctly on mobile', () => {
    const wrapper = shallow(<DebateStepPageVoteAndShare {...props.onMobile} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('renders correctly when timeless step', () => {
    const wrapper = shallow(<DebateStepPageVoteAndShare {...props.timelessStep} />);
    expect(wrapper).toMatchSnapshot();
  });
});
