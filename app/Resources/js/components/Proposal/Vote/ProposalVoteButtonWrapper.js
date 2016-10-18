import React, { PropTypes } from 'react';
import ProposalVoteButton from './ProposalVoteButton';
import VoteButtonOverlay from './VoteButtonOverlay';
import { VOTE_TYPE_SIMPLE } from '../../../constants/ProposalConstants';
import LoginOverlay from '../../Utils/LoginOverlay';
import { connect } from 'react-redux';

export const ProposalVoteButtonWrapper = React.createClass({
  displayName: 'ProposalVoteButtonWrapper',
  propTypes: {
    proposal: PropTypes.object.isRequired,
    userHasVote: PropTypes.bool.isRequired,
    step: PropTypes.object,
    creditsLeft: PropTypes.number,
    user: PropTypes.object,
    style: PropTypes.object,
    className: PropTypes.string,
  },

  getDefaultProps() {
    return {
      style: {},
      className: '',
    };
  },

  userHasEnoughCredits() {
    const {
      creditsLeft,
      proposal,
    } = this.props;
    if (creditsLeft !== null && !!proposal.estimation) {
      return creditsLeft >= proposal.estimation;
    }
    return true;
  },

  render() {
    const { user, step, proposal, style, className, userHasVote } = this.props;
    if (step && step.voteType === VOTE_TYPE_SIMPLE) {
      return (
        <ProposalVoteButton
          proposal={proposal}
          step={step}
          user={user}
          disabled={!step.open}
          style={style}
          className={className}
        />
      );
    }

    if (user) {
      return (
        <VoteButtonOverlay
            popoverId={`vote-tooltip-proposal-${proposal.id}`}
            show={!userHasVote && !this.userHasEnoughCredits()}
        >
          <ProposalVoteButton
            proposal={proposal}
            step={step}
            user={user}
            disabled={!(step && step.open) || !this.userHasEnoughCredits()}
            style={style}
            className={className}
          />
        </VoteButtonOverlay>
      );
    }

    return (
      <LoginOverlay>
        <ProposalVoteButton
          proposal={proposal}
          step={step}
          user={user}
          disabled={!(step && step.open)}
          style={style}
          className={className}
        />
      </LoginOverlay>
    );
  },

});

const mapStateToProps = (state, props) => {
  const step = (state.project.currentProjectById && props.proposal.votableStepId)
    ? state.project.projects[state.project.currentProjectById].steps.filter(s => s.id === props.proposal.votableStepId)[0]
    : null;
  const user = state.default.user;
  return {
    user,
    userHasVote: !!(user && step && state.proposal.userVotesByStepId[step.id].includes(props.proposal.id)),
    creditsLeft: step ? state.proposal.creditsLeftByStepId[step.id] : null,
    step,
  };
};

export default connect(mapStateToProps)(ProposalVoteButtonWrapper);
