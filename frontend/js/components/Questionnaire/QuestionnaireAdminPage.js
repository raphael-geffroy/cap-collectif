// @flow
import React, { Component } from 'react';
import { QueryRenderer, graphql } from 'react-relay';
import { connect } from 'react-redux';
import environment, { graphqlError } from '../../createRelayEnvironment';
import QuestionnaireAdminPageTabs from './QuestionnaireAdminPageTabs';
import Loader from '../Ui/FeedbacksIndicators/Loader';
import type { QuestionnaireAdminPageQueryResponse } from '~relay/QuestionnaireAdminPageQuery.graphql';
import type { Uuid, State } from '../../types';

export type Props = {| enableResultsTab: boolean, questionnaireId: Uuid |};

const component = ({
  error,
  props,
}: {
  ...ReactRelayReadyState,
  props: ?QuestionnaireAdminPageQueryResponse,
}) => {
  if (error) {
    console.log(error); // eslint-disable-line no-console
    return graphqlError;
  }
  if (props) {
    if (props.questionnaire !== null) {
      return <QuestionnaireAdminPageTabs questionnaire={props.questionnaire} />;
    }
    return graphqlError;
  }
  return <Loader />;
};

export class QuestionnaireAdminPage extends Component<Props> {
  render() {
    const { enableResultsTab, questionnaireId } = this.props;
    return (
      <div className="admin_questionnaire_form">
        <QueryRenderer
          environment={environment}
          query={graphql`
            query QuestionnaireAdminPageQuery($id: ID!, $enableResultsTab: Boolean!) {
              questionnaire: node(id: $id) {
                ...QuestionnaireAdminPageTabs_questionnaire
              }
            }
          `}
          variables={{
            id: questionnaireId,
            enableResultsTab,
          }}
          render={component}
        />
      </div>
    );
  }
}

const mapStateToProps = (state: State) => ({
  enableResultsTab: state.default.features.new_feature_questionnaire_result,
});

export default connect(mapStateToProps)(QuestionnaireAdminPage);