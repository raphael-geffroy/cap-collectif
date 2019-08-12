// @flow
import React from 'react';
import styled from 'styled-components';
import { QueryRenderer, graphql, type ReadyState } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import environment, { graphqlError } from '../../createRelayEnvironment';
import EventPreview from '../Event/EventPreview';
import type {
  PresentationStepEventsQueryResponse,
  PresentationStepEventsQueryVariables,
} from '~relay/PresentationStepEventsQuery.graphql';

export type Props = {|
  +showAllUrl: string,
  +limit: number,
  +projectId: string,
|};

const EventContainer = styled.div`
  padding-top: 20px;

  >div{
    width: 45%;
    display: inline-block;
    margin-right: 5%;
  }

  @media (max-width: 1200px) {
    >div{
      width: 70%;
      display: block;
      margin-right: 0;
    }
    
  @media (max-width: 380px) {
    >div{
      width: 100%;
    }
  }
`;

class PresentationStepEvents extends React.Component<Props> {
  renderEventList = ({
    error,
    props,
  }: {|
    ...ReadyState,
    props: ?PresentationStepEventsQueryResponse,
  |}) => {
    if (error) {
      return graphqlError;
    }
    if (props && props.events.edges && props.events.edges.length > 0) {
      const { showAllUrl } = this.props;
      return (
        <div id="PresentationStepEvents" className="block">
          <h2 className="h2">
            <FormattedMessage id="step.presentation.events.title" />{' '}
            <span className="small excerpt">{props.events.totalCount}</span>
          </h2>
          <EventContainer>
            {props.events.edges &&
              props.events.edges
                .filter(Boolean)
                .map(edge => edge.node)
                .filter(Boolean)
                .map((node, key) => (
                  <div key={key}>
                    {/* $FlowFixMe */}
                    <EventPreview isHighlighted={false} event={node} />
                  </div>
                ))}
          </EventContainer>
          {props.events.totalCount > 2 && (
            <a id="project-events" href={showAllUrl} className="btn btn-primary btn--outline">
              <FormattedMessage id="event.see_all" />
            </a>
          )}
        </div>
      );
    }
  };

  render() {
    const { limit, projectId } = this.props;

    return (
      <QueryRenderer
        environment={environment}
        query={graphql`
          query PresentationStepEventsQuery($count: Int, $orderBy: EventOrder!, $project: ID!) {
            events(orderBy: $orderBy, first: $count, project: $project, isFuture: null) {
              totalCount
              edges {
                node {
                  id
                  ...EventPreview_event
                }
              }
            }
          }
        `}
        variables={
          ({
            count: limit,
            project: projectId,
            orderBy: {
              field: 'END_AT',
              direction: 'DESC',
            },
          }: PresentationStepEventsQueryVariables)
        }
        render={this.renderEventList}
      />
    );
  }
}

export default PresentationStepEvents;