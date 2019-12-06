// @flow
import React from 'react';
import { connect } from 'react-redux';
import { QueryRenderer, graphql } from 'react-relay';
import environment, { graphqlError } from '../../../createRelayEnvironment';
import ProposalPageHeader from './ProposalPageHeader';
import ProposalPageAlert from './ProposalPageAlert';
import ProposalDraftAlert from './ProposalDraftAlert';
import ProposalPageTabs from './ProposalPageTabs';
import Loader from '../../Ui/FeedbacksIndicators/Loader';
import type { FeatureToggles, State } from '../../../types';
import { PROPOSAL_FOLLOWERS_TO_SHOW } from '../../../constants/ProposalConstants';
import type { ProposalPageQueryResponse } from '~relay/ProposalPageQuery.graphql';

export type Props = {|
  proposalId: string,
  currentVotableStepId: ?string,
  features: FeatureToggles,
  isAuthenticated: boolean,
|};

export class ProposalPage extends React.Component<Props> {
  render() {
    const { proposalId, currentVotableStepId, features, isAuthenticated } = this.props;
    return (
      <div>
        <QueryRenderer
          environment={environment}
          query={graphql`
            query ProposalPageQuery(
              $proposalId: ID!
              $hasVotableStep: Boolean!
              $stepId: ID!
              $count: Int!
              $cursor: String
              $isAuthenticated: Boolean!
            ) {
              viewer @include(if: $isAuthenticated) {
                ...ProposalPageTabs_viewer
                ...ProposalPageHeader_viewer @arguments(hasVotableStep: $hasVotableStep)
              }
              step: node(id: $stepId) @include(if: $hasVotableStep) {
                ...ProposalPageHeader_step @arguments(isAuthenticated: $isAuthenticated)
                ...ProposalPageTabs_step
              }
              proposal: node(id: $proposalId) {
                ...ProposalDraftAlert_proposal
                ...ProposalPageAlert_proposal
                ...ProposalPageTabs_proposal
                ...ProposalPageHeader_proposal @arguments(isAuthenticated: $isAuthenticated)
              }
            }
          `}
          variables={{
            proposalId,
            hasVotableStep: !!currentVotableStepId,
            stepId: currentVotableStepId || '',
            count: PROPOSAL_FOLLOWERS_TO_SHOW,
            cursor: null,
            isAuthenticated,
          }}
          render={({
            error,
            props,
          }: {
            ...ReactRelayReadyState,
            props: ?ProposalPageQueryResponse,
          }) => {
            if (error) {
              console.log(error); // eslint-disable-line no-console
              return graphqlError;
            }
            if (props) {
              if (props.proposal) {
                return (
                  <div>
                    <ProposalDraftAlert proposal={props.proposal} />
                    <ProposalPageAlert proposal={props.proposal} />
                    <section className="section--custom">
                      <ProposalPageHeader
                        proposal={props.proposal}
                        step={props.step || null}
                        viewer={props.viewer || null}
                        isAuthenticated={!!props.viewer}
                        className="container"
                      />
                    </section>
                    <section className="section--custom">
                      <ProposalPageTabs
                        proposal={props.proposal}
                        step={props.step || null}
                        viewer={props.viewer || null}
                        features={features}
                      />
                    </section>
                  </div>
                );
              }

              return graphqlError;
            }
            return <Loader />;
          }}
        />
      </div>
    );
  }
}

const mapStateToProps = (state: State) => ({
  isAuthenticated: state.user.user !== null,
  features: state.default.features,
});

export default connect(mapStateToProps)(ProposalPage);