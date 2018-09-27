// @flow
import * as React from 'react';
import { connect, type MapStateToProps } from 'react-redux';
import { QueryRenderer, createFragmentContainer, graphql, type ReadyState } from 'react-relay';
import environment, { graphqlError } from '../../createRelayEnvironment';
import UnpublishedArgumentList from './UnpublishedArgumentList';
import type { UnpublishedArgumentListRendererQueryResponse } from './__generated__/UnpublishedArgumentListRendererQuery.graphql';
import type { ArgumentList_argumentable } from './__generated__/ArgumentList_argumentable.graphql';
import type { State } from '../../types';

type Props = {
  argumentable: ArgumentList_argumentable,
  isAuthenticated: boolean,
  type: 'FOR' | 'AGAINST' | 'SIMPLE',
};

export class UnpublishedArgumentListRenderer extends React.Component<Props> {
  render() {
    const { type, isAuthenticated } = this.props;
    return (
      <div id={`opinion__unpublished--arguments--${type}`} className="block--tablet">
        <QueryRenderer
          environment={environment}
          query={graphql`
            query UnpublishedArgumentListRendererQuery(
              $argumentableId: ID!
              $isAuthenticated: Boolean!
              $type: ArgumentValue!
            ) {
              argumentable: node(id: $argumentableId) {
                id
                ...UnpublishedArgumentList_argumentable
              }
            }
          `}
          variables={{
            isAuthenticated,
            argumentableId: this.props.argumentable.id,
            type: type === 'SIMPLE' ? 'FOR' : type,
          }}
          render={({
            props,
            error,
          }: { props: ?UnpublishedArgumentListRendererQueryResponse } & ReadyState) => {
            if (error) {
              return graphqlError;
            }
            if (props && props.argumentable) {
              // $FlowFixMe
              return <UnpublishedArgumentList type={type} argumentable={props.argumentable} />;
            }
            return null;
          }}
        />
      </div>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (state: State) => ({
  isAuthenticated: !!state.user.user,
});
const container = connect(mapStateToProps)(UnpublishedArgumentListRenderer);

export default createFragmentContainer(container, {
  argumentable: graphql`
    fragment UnpublishedArgumentListRenderer_argumentable on Argumentable {
      id
    }
  `,
});
