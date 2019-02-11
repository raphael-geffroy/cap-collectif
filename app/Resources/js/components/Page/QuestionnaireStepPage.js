// @flow
import * as React from 'react';
import {QueryRenderer, graphql, type ReadyState} from 'react-relay';
import {connect} from 'react-redux';
import environment, {graphqlError} from '../../createRelayEnvironment';
import {type GlobalState} from '../../types';
import {type QuestionnaireStepPageQueryResponse} from './__generated__/QuestionnaireStepPageQuery.graphql';
import {Loader} from '../Ui/FeedbacksIndicators/Loader';
import QuestionnaireStepTabs from '../Questionnaire/QuestionnaireStepTabs'

type Props = {
  questionnaireId: ?string,
  isAuthenticated: boolean,
};

const component = ({
  error,
  props,
}: {
  props: ?QuestionnaireStepPageQueryResponse,
} & ReadyState) => {
  if (error) {
    return graphqlError;
  }

  if (props) {
    if (props.questionnaire) {
      return (
        <div>
          <QuestionnaireStepTabs questionnaire={props.questionnaire}/>
        </div>
      );
    }
    return graphqlError;
  }
  return <Loader/>;
};

export class QuestionnaireStepPage extends React.Component<Props> {
  render() {
    const {questionnaireId, isAuthenticated} = this.props;

    return (
      <div>
        {questionnaireId ? (
          <QueryRenderer
            environment={environment}
            query={graphql`
              query QuestionnaireStepPageQuery($id: ID!, $isAuthenticated: Boolean!) {
                questionnaire: node(id: $id) {
                  ...QuestionnaireStepTabs_questionnaire @arguments(isAuthenticated: $isAuthenticated)
                }
              }
            `}
            variables={{
              id: questionnaireId,
              isAuthenticated,
            }}
            render={component}
          />
        ) : null}
      </div>
    );
  }
}

const mapStateToProps = (state: GlobalState) => ({
  isAuthenticated: state.user.user !== null,
});

export default connect(mapStateToProps)(QuestionnaireStepPage);
