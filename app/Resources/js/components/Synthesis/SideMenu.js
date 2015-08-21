import CreateModal from './CreateModal';
import ElementsFinder from './ElementsFinder';

const Nav = ReactBootstrap.Nav;
const NavItem = ReactBootstrap.NavItem;
const NavItemLink = ReactRouterBootstrap.NavItemLink;

const SideMenu = React.createClass({
  propTypes: {
    synthesis: React.PropTypes.object,
  },
  mixins: [ReactIntl.IntlMixin],

  getInitialState() {
    return {
      showCreateModal: false,
    };
  },

  componentWillUnmount() {
    this.toggleCreateModal(false);
  },

  renderContributionsButton() {
    return (
        <NavItemLink to="tree" className="menu__link" bsStyle="link">
          <i className="cap cap-baloon"></i> {this.getIntlMessage('edition.sideMenu.contributions')}
        </NavItemLink>
    );
  },

  renderTree() {
    return (
      <ElementsFinder synthesis={this.props.synthesis} itemClass="menu__link" />
    );
  },

  renderCreateButton() {
    return (
      <NavItem className="menu__link menu__action" onClick={this.showCreateModal.bind(null, this)}>
          <i className="cap cap-folder-add"></i> {this.getIntlMessage('edition.action.create.label')}
      </NavItem>
    );
  },

  renderManageButton() {
    return (
      <NavItemLink className="menu__link menu__action" to="tree">
        <i className="cap cap-folder-edit"></i> {this.getIntlMessage('edition.action.manage.label')}
      </NavItemLink>
    );
  },

  render() {
    return (
      <div className="synthesis__side-menu">
        <Nav stacked className="menu--fixed">
          {this.renderContributionsButton()}
        </Nav>
        <div className="menu__tree">
          {this.renderTree()}
        </div>
        <Nav stacked className="menu__actions menu--fixed">
          {this.renderCreateButton()}
          {this.renderManageButton()}
        </Nav>
        <CreateModal synthesis={this.props.synthesis} show={this.state.showCreateModal} toggle={this.toggleCreateModal} />
      </div>
    );
  },

  showCreateModal() {
    this.toggleCreateModal(true);
  },

  hideCreateModal() {
    this.toggleCreateModal(false);
  },

  toggleCreateModal(value) {
    this.setState({
      showCreateModal: value,
    });
  },

});

export default SideMenu;

