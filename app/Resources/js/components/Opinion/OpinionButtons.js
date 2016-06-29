import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';

import ShareButtonDropdown from '../Utils/ShareButtonDropdown';
import OpinionVersionForm from './OpinionVersionForm';
import OpinionReportButton from './OpinionReportButton';
import { ButtonToolbar, Button } from 'react-bootstrap';
import OpinionDelete from './Delete/OpinionDelete';

const OpinionButtons = React.createClass({
  propTypes: {
    opinion: PropTypes.object.isRequired,
    user: PropTypes.object,
  },
  mixins: [IntlMixin],

  getDefaultProps() {
    return {
      user: null,
    };
  },

  isVersion() {
    return !!this.props.opinion.parent;
  },

  isContribuable() {
    return this.isVersion() ? this.props.opinion.parent.isContribuable : this.props.opinion.isContribuable;
  },

  isTheUserTheAuthor() {
    if (this.props.opinion.author === null || !this.props.user) {
      return false;
    }
    return this.props.user.uniqueId === this.props.opinion.author.uniqueId;
  },

  renderEditButton() {
    if (this.isContribuable() && this.isTheUserTheAuthor()) {
      if (this.isVersion()) {
        return (
          <OpinionVersionForm
            className="pull-right"
            style={{ marginLeft: '5px' }}
            mode="edit"
            opinionId={this.props.opinion.parent.id}
            version={this.props.opinion}
            isContribuable
          />
        );
      }
      return (
        <Button className="opinion__action--edit pull-right btn--outline btn-dark-gray" href={this.props.opinion._links.edit}>
          <i className="cap cap-pencil-1"></i> {this.getIntlMessage('global.edit')}
        </Button>
      );
    }
  },

  render() {
    const opinion = this.props.opinion;
    return (
      <ButtonToolbar>
        <OpinionDelete opinion={opinion} />
        {this.renderEditButton()}
        <OpinionReportButton opinion={opinion} />
        <ShareButtonDropdown
          id="opinion-share-button"
          className="pull-right"
          title={opinion.title}
          url={opinion._links.show}
        />
      </ButtonToolbar>
    );
  },

});

const mapStateToProps = (state) => {
  return {
    features: state.default.features,
    user: state.default.user,
  };
};

export default connect(mapStateToProps)(OpinionButtons);
