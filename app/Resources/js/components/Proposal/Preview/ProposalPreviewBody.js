import React from 'react';
import { IntlMixin } from 'react-intl';
import ProposalDetailEstimation from '../Detail/ProposalDetailEstimation';
import ProposalDetailLikers from '../Detail/ProposalDetailLikers';

const ProposalPreviewBody = React.createClass({
  propTypes: {
    proposal: React.PropTypes.object.isRequired,
    showNullEstimation: React.PropTypes.bool.isRequired,
  },
  mixins: [IntlMixin],

  render() {
    const proposal = this.props.proposal;

    return (
      <div className="proposal__body" >
        <h2 className="h4 proposal__title smart-fade">
          <a href={proposal._links.show}>{proposal.title}</a>
        </h2>
        <div className="proposal__infos">
          {proposal.theme
            ? <div className="proposal__info">
                <i className="cap cap-tag-1-1"></i>{proposal.theme.title}
              </div>
            : null
          }
          {proposal.district
            ? <div className="proposal__info">
                <i className="cap cap-marker-1-1"></i>{proposal.district.name}
              </div>
            : null
          }
          <ProposalDetailEstimation
            proposal={proposal}
            showNullEstimation={this.props.showNullEstimation}
          />
          <ProposalDetailLikers
            proposal={proposal}
          />
        </div>
      </div>
    );
  },

});

export default ProposalPreviewBody;
