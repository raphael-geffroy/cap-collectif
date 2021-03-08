// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { QueryRenderer, graphql } from 'react-relay';
import environment, { graphqlError } from '~/createRelayEnvironment';
import type { State } from '~/types';
import type { DebateStepPageQueryResponse } from '~relay/DebateStepPageQuery.graphql';
import DebateStepPageLogic from './DebateStepPageLogic';
import { DebateStepPageContext } from './DebateStepPage.context';

export type Props = {|
  +stepId: string,
  +title: string,
  +isAuthenticated: boolean,
  +fromWidget: boolean,
  +authEnabled: boolean,
|};

export const DebateStepPage = ({
  stepId,
  title,
  isAuthenticated,
  fromWidget,
  authEnabled,
}: Props) => {
  const contextValue = React.useMemo(
    () => ({
      widget: {
        isSource: fromWidget,
        authEnabled,
      },
      title,
    }),
    [title, fromWidget, authEnabled],
  );

  return (
    <QueryRenderer
      environment={environment}
      query={graphql`
        query DebateStepPageQuery($stepId: ID!, $isAuthenticated: Boolean!) {
          ...DebateStepPageLogic_query
            @arguments(stepId: $stepId, isAuthenticated: $isAuthenticated)
        }
      `}
      variables={{
        stepId,
        isAuthenticated,
      }}
      render={({
        error,
        props,
      }: {
        ...ReactRelayReadyState,
        props: ?DebateStepPageQueryResponse,
      }) => {
        if (error) {
          console.error(error); // eslint-disable-line no-console
          return graphqlError;
        }

        return (
          <DebateStepPageContext.Provider value={contextValue}>
            <DebateStepPageLogic query={props} isAuthenticated={isAuthenticated} />
          </DebateStepPageContext.Provider>
        );
      }}
    />
  );
};

const mapStateToProps = (state: State) => ({
  isAuthenticated: state.user.user !== null,
});

export default connect<any, any, _, _, _, _>(mapStateToProps)(DebateStepPage);
