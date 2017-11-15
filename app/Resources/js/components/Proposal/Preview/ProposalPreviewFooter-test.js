/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import ProposalPreviewFooter from './ProposalPreviewFooter';

describe('<ProposalPreviewFooter />', () => {
  const proposal = {
    commentsCount: 3,
    votesCountByStepId: {
      '1': 1,
      '42': 5,
    },
  };

  const props = {
    proposal,
    stepId: '1',
    showComments: true,
  };

  it('should render a footer with comment counter', () => {
    const wrapper = shallow(<ProposalPreviewFooter {...props} />);
    const footer = wrapper.find('div.proposal__footer');
    expect(footer).toHaveLength(1);

    const countersDiv = footer.find('div.proposal__counters');
    expect(countersDiv).toHaveLength(1);
    expect(countersDiv.find('div.proposal__counter')).toHaveLength(1);
    expect(countersDiv.find('div.proposal__counter--votes')).toHaveLength(0);

    const commentsCounter = countersDiv.find('div.proposal__counter--comments');
    expect(commentsCounter).toHaveLength(1);
    expect(commentsCounter.find('.proposal__counter__value').text()).toEqual(
      `${proposal.commentsCount}`,
    );
  });

  it('should render a footer with comment and votes counters', () => {
    const wrapper = shallow(<ProposalPreviewFooter {...props} showVotes />);

    const footer = wrapper.find('div.proposal__footer');
    expect(footer).toHaveLength(1);

    const countersDiv = footer.find('div.proposal__counters');
    expect(countersDiv).toHaveLength(1);
    expect(countersDiv.find('div.proposal__counter')).toHaveLength(2);

    const commentsCounter = countersDiv.find('div.proposal__counter--comments');
    expect(commentsCounter).toHaveLength(1);
    expect(commentsCounter.find('.proposal__counter__value').text()).toEqual(
      `${proposal.commentsCount}`,
    );

    const votesCounter = countersDiv.find('div.proposal__counter--votes');
    expect(votesCounter).toHaveLength(1);
    expect(votesCounter.find('.proposal__counter__value').text()).toEqual(
      `${proposal.votesCountByStepId['1']}`,
    );
  });

  it('should render a footer without comment and votes counters', () => {
    const wrapper = shallow(
      <ProposalPreviewFooter {...props} showVotes={false} showComments={false} />,
    );

    const footer = wrapper.find('div.proposal__footer');
    expect(footer).toHaveLength(0);
  });
});
