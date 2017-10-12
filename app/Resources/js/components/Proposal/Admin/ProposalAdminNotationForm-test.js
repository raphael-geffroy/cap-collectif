// @flow
/* eslint-env jest */
import * as React from 'react';
import { shallow } from 'enzyme';
import { ProposalAdminNotationForm } from './ProposalAdminNotationForm';

describe('<ProposalAdminNotationForm />', () => {
  const props = {
    handleSubmit: jest.fn(),
    invalid: false,
    disabled: false,
    pristine: false,
    submitting: false,
    formValidationRules: Object,
    initialValues: Object,
    proposal: {
      id: '1',
      estimation: 1000,
      likers: [{ id: '1', displayName: 'liker-1' }],
      form: {
        evaluationForm: null,
      },
      proposalEvaluation: {
        responses: [],
      },
    },
  };

  it('render correctly', () => {
    const wrapper = shallow(<ProposalAdminNotationForm {...props} />);
    expect(wrapper).toMatchSnapshot();
  });
});
