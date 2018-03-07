import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { createFragmentContainer, graphql } from 'react-relay';
import { Alert, Button } from 'react-bootstrap';
import { type ReplyCreateFormWrapper_questionnaire } from './__generated__/ReplyCreateFormWrapper_questionnaire.graphql';
import LoginButton from '../../User/Login/LoginButton';
import RegistrationButton from '../../User/Registration/RegistrationButton';
import PhoneModal from '../../User/Phone/PhoneModal';
import ReplyForm from './ReplyForm';

type Props = {
  questionnaire: ReplyCreateFormWrapper_questionnaire,
  user: Object,
};

export class ReplyCreateFormWrapper extends React.Component<Props> {
  state = {
    showPhoneModal: false,
  };

  openPhoneModal() {
    this.setState({ showPhoneModal: true });
  }

  closePhoneModal() {
    this.setState({ showPhoneModal: false });
  }

  formIsDisabled() {
    const { questionnaire, user } = this.props;
    return (
      !questionnaire.open ||
      !user ||
      (questionnaire.phoneConfirmationRequired && !user.isPhoneConfirmed) ||
      (questionnaire.viewerReplies.length > 0 && !questionnaire.multipleRepliesAllowed)
    );
  }

  render() {
    const { questionnaire, user } = this.props;

    return (
      <div>
        {questionnaire.contribuable && !user ? (
          <Alert bsStyle="warning" className="text-center">
            <strong>
              <FormattedMessage id="reply.not_logged_in.error" />
            </strong>
            <RegistrationButton bsStyle="primary" style={{ marginLeft: '10px' }} />
            <LoginButton style={{ marginLeft: 5 }} />
          </Alert>
        ) : (
          questionnaire.contribuable &&
          questionnaire.viewerReplies.length > 0 &&
          !questionnaire.multipleRepliesAllowed && (
            <Alert bsStyle="warning">
              <strong>
                <FormattedMessage id="reply.user_has_reply.reason" />
              </strong>
              <p>
                <FormattedMessage id="reply.user_has_reply.error" />
              </p>
            </Alert>
          )
        )}
        {questionnaire.contribuable &&
          questionnaire.phoneConfirmationRequired &&
          user &&
          !user.isPhoneConfirmed && (
            <Alert bsStyle="warning">
              <strong>
                <FormattedMessage id="phone.please_verify" />
              </strong>
              <span style={{ marginLeft: '10px' }}>
                <Button onClick={this.openPhoneModal}>
                  <FormattedMessage id="phone.check" />
                </Button>
              </span>
            </Alert>
          )}
        {/* <ReplyCreateForm form={questionnaire} disabled={this.formIsDisabled()} /> */}
        <ReplyForm questionnaire={questionnaire} />
        {/* <PhoneModal show={this.state.showPhoneModal} onClose={this.closePhoneModal} /> */}
      </div>
    );
  }
}

const mapStateToProps = state => {
  return {
    user: state.user.user,
  };
};

const container = connect(mapStateToProps)(ReplyCreateFormWrapper);

export default createFragmentContainer(container, {
  questionnaire: graphql`
    fragment ReplyCreateFormWrapper_questionnaire on Questionnaire {
      anonymousAllowed
      description
      multipleRepliesAllowed
      phoneConfirmationRequired
      open
      viewerReplies {
        id
      }
      id
      ...ReplyForm_questionnaire
    }
  `,
});
