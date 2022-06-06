// @flow
import * as React from 'react';
import { graphql, useFragment } from 'react-relay';
import styled, { type StyledComponent } from 'styled-components';
import ProposalVoteModal from '../Vote/ProposalVoteModal';
import ProposalVoteButtonWrapperFragment from '../Vote/ProposalVoteButtonWrapperFragment';
import type { ProposalPreviewVote_proposal$key } from '~relay/ProposalPreviewVote_proposal.graphql';
import type { ProposalPreviewVote_step$key } from '~relay/ProposalPreviewVote_step.graphql';
import type { ProposalPreviewVote_viewer$key } from '~relay/ProposalPreviewVote_viewer.graphql';

type Props = {
  proposal: ProposalPreviewVote_proposal$key,
  step: ProposalPreviewVote_step$key,
  viewer: ?ProposalPreviewVote_viewer$key,
};

const Container: StyledComponent<{}, {}, HTMLSpanElement> = styled.span`
  /** Boostrap for now until "Epurer" ticket */
  .proposal__button__vote.active:hover {
    background-color: #dc3545;
    border-color: #dc3545;
  }
`;

const VIEWER_FRAGMENT = graphql`
  fragment ProposalPreviewVote_viewer on User @argumentDefinitions(stepId: { type: "ID!" }) {
    ...ProposalVoteButtonWrapperFragment_viewer
      @arguments(isAuthenticated: $isAuthenticated, stepId: $stepId)
    ...ProposalVoteModal_viewer
  }
`;
const PROPOSAL_FRAGMENT = graphql`
  fragment ProposalPreviewVote_proposal on Proposal
  @argumentDefinitions(isAuthenticated: { type: "Boolean!" }, stepId: { type: "ID!" }) {
    id
    ...ProposalVoteModal_proposal @include(if: $isAuthenticated)
    ...ProposalVoteButtonWrapperFragment_proposal
      @arguments(stepId: $stepId, isAuthenticated: $isAuthenticated)
  }
`;
const STEP_FRAGMENT = graphql`
  fragment ProposalPreviewVote_step on Step
  @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
    ...ProposalVoteModal_step @arguments(isAuthenticated: $isAuthenticated)
    ...ProposalVoteButtonWrapperFragment_step @arguments(isAuthenticated: $isAuthenticated)
  }
`;

const ProposalPreviewVote: React.StatelessFunctionalComponent<Props> = ({
  viewer: viewerRef,
  step: stepRef,
  proposal: proposalRef,
}) => {
  const viewer = useFragment(VIEWER_FRAGMENT, viewerRef);
  const proposal = useFragment(PROPOSAL_FRAGMENT, proposalRef);
  const step = useFragment(STEP_FRAGMENT, stepRef);

  return (
    <Container>
      <ProposalVoteButtonWrapperFragment
        proposal={proposal}
        step={step}
        viewer={viewer}
        id={`proposal-vote-btn-${proposal.id}`}
        className="proposal__preview__vote mr-15"
      />
      {viewer && <ProposalVoteModal proposal={proposal} step={step} viewer={viewer} />}
    </Container>
  );
};
export default ProposalPreviewVote;
