// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import {Field, formValueSelector, reduxForm} from 'redux-form';
import { Alert } from 'react-bootstrap';
import styled from 'styled-components';
import renderInput from '../../Form/Field';
import { login as onSubmit } from '../../../redux/modules/user';
import { isEmail } from '../../../services/Validator';
import type {GlobalState} from '../../../types';

const StyledContainer = styled.div`
  .hide-captcha{
    display: none;
  }
`;

type LoginValues = {|
  username: string,
  password: string,
|};

type ReduxProps = {|
  +displayCaptcha: boolean,
  +error?: string,
  +submitting: ?boolean
|};

type State = {|
  error?: ?string
|};

type Props = {|
  ...ReduxProps,
  error?: ?string,
|};

export const formName = 'login';

export const validate = (values: LoginValues) => {
  const errors = {};

  if (!values.username || !isEmail(values.username)) {
    errors.username = 'global.constraints.email.invalid';
  }

  return errors;
};

export class LoginForm extends React.Component<Props, State> {
  static defaultProps = {
    displayCaptcha: false,
    submitting: undefined
  };

  state = {
    error:  null
  };

  componentDidUpdate(prevProps: Props){
    const {submitting, error} = this.props;
    if (prevProps.submitting && submitting === false){
      // https://reactjs.org/docs/react-component.html#componentdidupdate
      // eslint-disable-next-line react/no-did-update-set-state
      this.setState({error});
    }
  }

  render() {
    const { error } = this.state;
    const { displayCaptcha } = this.props;
    return (
      <div className="form_no-bold-label">
        {error && (
          <Alert bsStyle="danger">
              <div className="font-weight-bold"><FormattedMessage id={error} /></div>
              <FormattedMessage id="try-again-or-click-on-forgotten-password-to-reset-it" />
          </Alert>
        )}
        <Field
          name="username"
          type="email"
          autoFocus
          disableValidation
          ariaRequired
          id="username"
          label={<FormattedMessage id="global.email" />}
          autoComplete="email"
          labelClassName="font-weight-normal"
          component={renderInput}
        />
        <Field
          name="password"
          type="password"
          disableValidation
          ariaRequired
          id="password"
          label={<FormattedMessage id="global.password" />}
          labelClassName="w-100 font-weight-normal"
          autoComplete={error ? undefined : "current-password"}
          component={renderInput}
        />
        <a href="/resetting/request">{<FormattedMessage id="global.forgot_password" />}</a>


        <StyledContainer>
          <div className={displayCaptcha ? '' : 'hide-captcha'}>
            <Field id="captcha" component={renderInput} name="captcha" type="captcha"  />
          </div>
        </StyledContainer>
      </div>
    );
  }
}

const mapStateToProps = (state: GlobalState) => ({
  displayCaptcha: formValueSelector(formName)(state, 'displayCaptcha'),
});


const container = connect(mapStateToProps)(LoginForm);

export default reduxForm({
  initialValues: {
    username: '',
    password: '',
  },
  validate,
  onSubmit,
  form: formName,
  destroyOnUnmount: true,
  persistentSubmitErrors: true
})(container);
