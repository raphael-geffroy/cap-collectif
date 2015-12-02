import SearchActions from '../../../actions/SearchActions';
import Input from '../../Form/Input';

const Button = ReactBootstrap.Button;

const ProposalListSearch = React.createClass({
  mixins: [
    ReactIntl.IntlMixin,
    React.addons.LinkedStateMixin,
  ],

  getInitialState() {
    return {
      value: '',
    };
  },

  handleSubmit(e) {
    e.preventDefault();
    const value = this._input.getValue();
    const length = value.length;

    if (length > 0) {
      SearchActions.getSearch(value, 'score', 'proposal', 1, 50);
    }
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
