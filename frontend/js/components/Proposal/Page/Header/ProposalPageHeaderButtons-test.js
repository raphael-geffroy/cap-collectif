/* eslint-env jest */
// @flow
import React from 'react';
import { shallow } from 'enzyme';
import { ProposalPageHeaderButtons } from './ProposalPageHeaderButtons';
import { $refType, $fragmentRefs } from '~/mocks';

describe('<ProposalPageHeaderButtons />', () => {
  const proposal = {
    $refType,
    $fragmentRefs,
    id: 'proposalId',
    url: '/slash',
    title: 'OEEEEE',
    author: {
      id: 'authorid',
      slug: 'metal',
      displayName: 'flex',
    },
    form: {
      contribuable: true,
      canContact: true,
    },
    publicationStatus: 'PUBLISHED',
  };

  const props = {
    opinionCanBeFollowed: true,
    hasVotableStep: true,
    dispatch: jest.fn(),
  };

  it('should render correctly', () => {
    const wrapper = shallow(
      <ProposalPageHeaderButtons step={null} proposal={proposal} viewer={null} {...props} />,
    );
    expect(wrapper).toMatchSnapshot();
  });
});