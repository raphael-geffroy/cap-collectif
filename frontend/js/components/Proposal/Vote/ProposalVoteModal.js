// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { graphql, createFragmentContainer, commitLocalUpdate } from 'react-relay';
import { ConnectionHandler, fetchQuery_DEPRECATED } from 'relay-runtime';
import { Modal, Panel, Label } from 'react-bootstrap';
import { submit, isPristine, isInvalid, getFormSyncErrors } from 'redux-form';
import { connect } from 'react-redux';
import styled, { type StyledComponent } from 'styled-components';
import CloseButton from '../../Form/CloseButton';
import SubmitButton from '../../Form/SubmitButton';
import { closeVoteModal, vote } from '~/redux/modules/proposal';
import ProposalsUserVotesTable, { getFormName } from '../../Project/Votes/ProposalsUserVotesTable';
import environment from '~/createRelayEnvironment';
import type { GlobalState, Dispatch } from '~/types';
import RequirementsForm, { formName, refetchViewer } from '../../Requirements/RequirementsForm';
import UpdateProposalVotesMutation from '~/mutations/UpdateProposalVotesMutation';
import type { ProposalVoteModal_proposal } from '~relay/ProposalVoteModal_proposal.graphql';
import type { ProposalVoteModal_step } from '~relay/ProposalVoteModal_step.graphql';
import WYSIWYGRender from '../../Form/WYSIWYGRender';
import invariant from '~/utils/invariant';
import { isInterpellationContextFromStep } from '~/utils/interpellationLabelHelper';
import VoteMinAlert from '~/components/Project/Votes/VoteMinAlert';

type ParentProps = {
  proposal: ProposalVoteModal_proposal,
  step: ProposalVoteModal_step,
};

type Props = {
  ...ParentProps,
  dispatch: Dispatch,
  showModal: boolean,
  isSubmitting: boolean,
  invalid: boolean,
  pristine: boolean,
  viewerIsConfirmedByEmail: boolean,
  isAuthenticated: boolean,
};

const ProposalVoteModalContainer: StyledComponent<{}, {}, typeof Modal> = styled(Modal).attrs({
  className: 'proposalVote__modal',
})`
  && .custom-modal-dialog {
    transform: none;
  }

  #confirm-proposal-vote {
    background: #0488cc !important;
    border-color: #0488cc !important;
  }
`;

type State = {
  keyboard: boolean,
};

export class ProposalVoteModal extends React.Component<Props, State> {
  state = {
    keyboard: true,
  };

  componentDidUpdate(prevProps: Props) {
    const { showModal } = this.props;
    if (!prevProps.showModal && showModal) {
      this.createTmpVote();
    } else if (!showModal && prevProps.showModal) {
      this.deleteTmpVote();
    }
  }

  onSubmit = (values: { votes: Array<{ public: boolean, id: string }> }) => {
    const { pristine, dispatch, step, proposal, isAuthenticated } = this.props;

    const tmpVote = values.votes.filter(v => v.id === null)[0];

    // First we add the vote
    return vote(dispatch, step.id, proposal.id, !tmpVote.public).then(data => {
      if (
        !data ||
        !data.addProposalVote ||
        !data.addProposalVote.voteEdge ||
        !data.addProposalVote.voteEdge.node ||
        typeof data.addProposalVote.voteEdge === 'undefined' ||
        data.addProposalVote.voteEdge === null
      ) {
        invariant(false, 'The vote id is missing.');
      }
      tmpVote.id = data.addProposalVote.voteEdge.node.id;

      // If the user didn't reorder
      // or update any vote privacy
      // we are clean
      if (!step.votesRanking && pristine) {
        return true;
      }

      // Otherwise we update/reorder votes
      return UpdateProposalVotesMutation.commit(
        {
          input: {
            step: step.id,
            votes: values.votes
              .filter(voteFilter => voteFilter.id !== null)
              .map(v => ({ id: v.id, anonymous: !v.public })),
          },
          stepId: step.id,
          isAuthenticated,
        },
        { id: null, position: -1, isVoteRanking: step.votesRanking },
      );
    });
  };

  onHide = () => {
    const { dispatch } = this.props;
    dispatch(closeVoteModal());
  };

  createTmpVote = () => {
    commitLocalUpdate(environment, store => {
      const { proposal, viewerIsConfirmedByEmail, step } = this.props;
      const dataID = `client:newTmpVote:${proposal.id}`;

      let newNode = store.get(dataID);
      if (!newNode) {
        newNode = store.create(dataID, 'ProposalVote');
      }
      newNode.setValue(viewerIsConfirmedByEmail, 'published');
      if (!viewerIsConfirmedByEmail) {
        newNode.setValue('WAITING_AUTHOR_CONFIRMATION', 'notPublishedReason');
      }
      newNode.setValue(false, 'anonymous');
      newNode.setValue(null, 'id'); // This will be used to know that this is the tmp vote

      // $FlowFixMe Cannot call newNode.setLinkedRecord with store.get(...) bound to record
      newNode.setLinkedRecord(store.get(proposal.id), 'proposal');

      // Create a new edge
      const edgeID = `client:newTmpEdge:${proposal.id}`;
      let newEdge = store.get(edgeID);
      if (!newEdge) {
        newEdge = store.create(edgeID, 'ProposalVoteEdge');
      }
      newEdge.setLinkedRecord(newNode, 'node');

      const stepProxy = store.get(step.id);
      if (!stepProxy) return;
      const connection = stepProxy.getLinkedRecord('viewerVotes', {
        orderBy: { field: 'POSITION', direction: 'ASC' },
      });
      if (!connection) {
        return;
      }
      ConnectionHandler.insertEdgeAfter(connection, newEdge);
      const totalCount = parseInt(connection.getValue('totalCount'), 10);
      connection.setValue(totalCount + 1, 'totalCount');
    });
  };

  deleteTmpVote = () => {
    commitLocalUpdate(environment, store => {
      const { proposal, step } = this.props;
      const dataID = `client:newTmpVote:${proposal.id}`;
      const stepProxy = store.get(step.id);
      if (!stepProxy) return;
      const connection = stepProxy.getLinkedRecord('viewerVotes', {
        orderBy: { field: 'POSITION', direction: 'ASC' },
      });
      if (connection) {
        ConnectionHandler.deleteNode(connection, dataID);
        const totalCount = parseInt(connection.getValue('totalCount'), 10);
        connection.setValue(totalCount - 1, 'totalCount');
      }
      store.delete(dataID);
    });
  };

  disabledKeyboard = () => {
    this.setState({
      keyboard: false,
    });
  };

  activeKeyboard = () => {
    this.setState({
      keyboard: true,
    });
  };

  getModalVoteTranslation = (step: ProposalVoteModal_step) => {
    if (step.form && step.form.objectType === 'PROPOSAL') {
      if (isInterpellationContextFromStep(step)) {
        return 'interpellation.support.count';
      }
      return 'votes-count';
    }
    return 'count-questions';
  };

  getModalVoteTitleTranslation = (step: ProposalVoteModal_step) => {
    const isInterpellation = isInterpellationContextFromStep(step);
    if (step.votesRanking) {
      if (isInterpellation) {
        return 'project.supports.title';
      }

      return 'project.votes.title';
    }
    if (isInterpellation) {
      return 'global.support.for';
    }

    return 'global.vote.for';
  };

  render() {
    const {
      dispatch,
      showModal,
      proposal,
      step,
      invalid,
      isSubmitting,
      isAuthenticated,
    } = this.props;
    const { keyboard } = this.state;
    const keyTradForModalVote = this.getModalVoteTranslation(step);
    const keyTradForModalVoteTitle = this.getModalVoteTitleTranslation(step);

    return step.requirements ? (
      <ProposalVoteModalContainer
        animation={false}
        enforceFocus={false}
        keyboard={keyboard}
        show={showModal}
        onHide={this.onHide}
        bsSize="large"
        role="dialog"
        dialogClassName="custom-modal-dialog"
        aria-labelledby="contained-modal-title-lg">
        <Modal.Header closeButton>
          <Modal.Title id="contained-modal-title-lg">
            <FormattedMessage id={keyTradForModalVoteTitle} />
          </Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {step.requirements.totalCount > 0 && (
            <Panel id="required-conditions" bsStyle="primary">
              <Panel.Heading>
                <FormattedMessage id="requirements" />{' '}
                {step.requirements.viewerMeetsTheRequirements && (
                  <Label bsStyle="primary">
                    <FormattedMessage id="filled" />
                  </Label>
                )}
              </Panel.Heading>
              {!step.requirements.viewerMeetsTheRequirements && (
                <Panel.Body>
                  <WYSIWYGRender value={step.requirements.reason} />
                  <RequirementsForm step={step} />
                </Panel.Body>
              )}
            </Panel>
          )}
          <VoteMinAlert step={step} translationKey={keyTradForModalVote} />
          <ProposalsUserVotesTable
            onSubmit={this.onSubmit}
            step={step}
            votes={step.viewerVotes}
            disabledKeyboard={this.disabledKeyboard}
            activeKeyboard={this.activeKeyboard}
          />
          {step.votesHelpText && (
            <div className="well mb-0 mt-15">
              <p>
                <b>
                  <FormattedMessage
                    id={
                      isInterpellationContextFromStep(step)
                        ? 'admin.fields.step.supportsHelpText'
                        : 'admin.fields.step.votesHelpText'
                    }
                  />
                </b>
              </p>
              <WYSIWYGRender value={step.votesHelpText} />
            </div>
          )}
        </Modal.Body>
        <Modal.Footer>
          <CloseButton className="pull-right" onClose={this.onHide} />
          <SubmitButton
            id="confirm-proposal-vote"
            disabled={step.requirements.totalCount > 0 ? invalid : false}
            onSubmit={() => {
              dispatch(submit(`proposal-user-vote-form-step-${step.id}`));
              fetchQuery_DEPRECATED(environment, refetchViewer, {
                stepId: step.id,
                isAuthenticated,
              });
            }}
            label="global.save"
            isSubmitting={isSubmitting}
            bsStyle={!proposal.viewerHasVote || isSubmitting ? 'success' : 'danger'}
            style={{ marginLeft: '10px' }}
          />
        </Modal.Footer>
      </ProposalVoteModalContainer>
    ) : null;
  }
}

const mapStateToProps = (state: GlobalState, props: ParentProps) => ({
  showModal: !!(
    state.proposal.currentVoteModal && state.proposal.currentVoteModal === props.proposal.id
  ),
  isSubmitting: !!state.proposal.isVoting,
  pristine: isPristine(getFormName(props.step))(state),
  invalid: isInvalid(formName)(state) || Object.keys(getFormSyncErrors(formName)(state)).length > 0,
  viewerIsConfirmedByEmail: state.user.user && state.user.user.isEmailConfirmed,
  isAuthenticated: !!state.user.user,
});

const container = connect<any, any, _, _, _, _>(mapStateToProps)(ProposalVoteModal);

export default createFragmentContainer(container, {
  proposal: graphql`
    fragment ProposalVoteModal_proposal on Proposal @argumentDefinitions(stepId: { type: "ID!" }) {
      id
      viewerHasVote(step: $stepId)
    }
  `,
  step: graphql`
    fragment ProposalVoteModal_step on ProposalStep
      @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      votesRanking
      votesHelpText
      ...VoteMinAlert_step
      ... on RequirementStep {
        requirements {
          viewerMeetsTheRequirements @include(if: $isAuthenticated)
          reason
          totalCount
        }
      }
      ...interpellationLabelHelper_step @relay(mask: false)
      ...RequirementsForm_step @arguments(isAuthenticated: $isAuthenticated)
      ...ProposalsUserVotesTable_step
      viewerVotes(orderBy: { field: POSITION, direction: ASC }) @include(if: $isAuthenticated) {
        ...ProposalsUserVotesTable_votes
        totalCount
        edges {
          node {
            id
            anonymous
          }
        }
      }
    }
  `,
});
