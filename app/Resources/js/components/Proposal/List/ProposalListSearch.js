import React from 'react';
import ProposalActions from '../../../actions/ProposalActions';
import Input from '../../Form/Input';
import { Button } from 'react-bootstrap';
import DeepLinkStateMixin from '../../../utils/DeepLinkStateMixin';
import { IntlMixin } from 'react-intl';

const ProposalListSearch = React.createClass({
  propTypes: {
    id: React.PropTypes.number.isRequired,
    fetchFrom: React.PropTypes.string,
  },
  mixins: [
    IntlMixin,
    DeepLinkStateMixin,
  ],

  getDefaultProps() {
    return {
      fetchFrom: 'form',
    };
  },

  getInitialState() {
    return {
      value: '',
    };
  },

  handleSubmit(e) {
    e.preventDefault();
    let value = this._input.getValue();
    value = value.length > 0 ? value : null;
    ProposalActions.changeSearchTerms(value);
    this.reload();
  },

  reload() {
    const {
      fetchFrom,
      id,
    } = this.props;
    ProposalActions.load(fetchFrom, id);
  },

  renderSearchButton() {
    return (
      <Button id="proposal-search-button" type="submit">
        <i className="cap cap-magnifier"></i>
      </Button>
    );
  },

  render() {
    return (
      <form onSubmit={this.handleSubmit}>
        <Input
          id="proposal-search-input"
          type="text"
          ref={(c) => this._input = c}
          placeholder={this.getIntlMessage('proposal.search')}
          buttonAfter={this.renderSearchButton(this.handleSubmit)}
          valueLink={this.linkState('value')}
          groupClassName="proposal-search-group pull-right"
        />
      </form>
    );
  },
});

export default ProposalListSearch;
