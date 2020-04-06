// @flow
import React from 'react';
import { graphql, QueryRenderer } from 'react-relay';
import environment, { graphqlError } from '~/createRelayEnvironment';
import type {
  ProjectAdminAnalysisTabQueryResponse,
  ProjectAdminAnalysisTabQueryVariables,
} from '~relay/ProjectAdminAnalysisTabQuery.graphql';
import type { ParametersState } from '~/components/Admin/Project/ProjectAdminPage.reducer';
import ProjectAdminAnalysis, {
  PROJECT_ADMIN_PROPOSAL_PAGINATION,
} from '~/components/Admin/Project/ProjectAdminAnalysis';
import { useProjectAdminProposalsContext } from '~/components/Admin/Project/ProjectAdminPage.context';
import Loader from '~ui/FeedbacksIndicators/Loader';

type Props = {|
  +projectId: string,
|};

const createQueryVariables = (
  projectId: string,
  parameters: ParametersState,
): ProjectAdminAnalysisTabQueryVariables => ({
  projectId,
  count: PROJECT_ADMIN_PROPOSAL_PAGINATION,
  cursor: null,
  orderBy: {
    field: 'PUBLISHED_AT',
    direction: parameters.sort === 'newest' ? 'DESC' : 'ASC',
  },
  category: parameters.filters.category === 'ALL' ? null : parameters.filters.category,
  district: parameters.filters.district === 'ALL' ? null : parameters.filters.district,
  term: parameters.filters.term,
});

const ProjectAdminAnalysisTab = ({ projectId }: Props) => {
  const { parameters } = useProjectAdminProposalsContext();
  return (
    <QueryRenderer
      environment={environment}
      query={graphql`
        query ProjectAdminAnalysisTabQuery(
          $projectId: ID!
          $count: Int!
          $cursor: String
          $orderBy: ProposalOrder!
          $category: ID
          $district: ID
          $status: ID
          $term: String
        ) {
          project: node(id: $projectId) {
            ...ProjectAdminAnalysis_project
              @arguments(
                projectId: $projectId
                count: $count
                cursor: $cursor
                orderBy: $orderBy
                category: $category
                district: $district
                status: $status
                term: $term
              )
          }
        }
      `}
      variables={createQueryVariables(projectId, parameters)}
      render={({
        props,
        error,
      }: {
        ...ReactRelayReadyState,
        props: ?ProjectAdminAnalysisTabQueryResponse,
      }) => {
        if (error) {
          return graphqlError;
        }
        if (props && props.project) {
          return <ProjectAdminAnalysis project={props.project} />;
        }
        return <Loader show />;
      }}
    />
  );
};

export default ProjectAdminAnalysisTab;