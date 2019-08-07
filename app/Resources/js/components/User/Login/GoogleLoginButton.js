// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
import type { FeatureToggles } from '../../../types';
import type { LabelPrefix } from './LoginSocialButtons';

type Props = {|
  features: FeatureToggles,
  prefix?: LabelPrefix,
|};

export class GoogleLoginButton extends React.Component<Props> {
  static displayName = 'GoogleLoginButton';

  static defaultProps = {
    prefix: 'login.',
  };

  render() {
    const { features, prefix } = this.props;
    if (!features.login_gplus) {
      return null;
    }

    const title = <FormattedMessage id={`${prefix || 'login.'}google`} />;
    return (
      <a
        href={`/login/google?_destination=${window && window.location.href}`}
        className="btn login__social-btn login__social-btn--googleplus"
        title={title}>
        {title}
      </a>
    );
  }
}

export default GoogleLoginButton;
