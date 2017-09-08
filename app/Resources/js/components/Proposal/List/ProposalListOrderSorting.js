// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import Input from '../../Form/Input';
import { changeOrder, loadProposals } from '../../../redux/modules/proposal';
import { PROPOSAL_AVAILABLE_ORDERS } from '../../../constants/ProposalConstants';
import type { Dispatch, State } from '../../../types';

type Props = { orderByVotes: boolean, dispatch: Dispatch, order?: string };

type ComponentState = {
  displayedOrders: Array<string>,
};

class ProposalListOrderSorting extends React.Component<Props, ComponentState> {
  static defaultProps = {
    orderByVotes: false,
  };

  constructor(props) {
    super(props);

    this.state = {
      // eslint-disable-next-line react/prop-types
      displayedOrders: PROPOSAL_AVAILABLE_ORDERS.concat(props.orderByVotes ? ['votes'] : []),
    };
  }

  render() {
    // eslint-disable-next-line react/prop-types
    const { order, dispatch } = this.props;
    const { displayedOrders } = this.state;

    return (
      <div>
        <Input
          id="proposal-sorting"
          type="select"
          onChange={e => {
            dispatch(changeOrder(e.target.value));
            dispatch(loadProposals());
          }}
          value={order}>
          {displayedOrders.map(choice => (
            <FormattedMessage key={choice} id={`global.filter_f_${choice}`}>
              {message => <option value={choice}>{message}</option>}
            </FormattedMessage>
          ))}) }
        </Input>
      </div>
    );
  }
}

const mapStateToProps = (state: State) => {
  return {
    order: state.proposal.order,
  };
};

export default connect(mapStateToProps)(ProposalListOrderSorting);
