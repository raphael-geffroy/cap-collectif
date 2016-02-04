import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';

const OpinionSourceFormInfos = React.createClass({
  propTypes: {
    action: PropTypes.string.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    if (this.props.action === 'update') {
      return null;
    }

    return (
      <div className="modal-top bg-info">
        <p>
          { this.getIntlMessage('source.add_infos') }
        </p>
      </div>
    );
  },

});

export default OpinionSourceFormInfos;
