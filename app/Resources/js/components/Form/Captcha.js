// @flow
import * as React from 'react';
import ReCAPTCHA from 'react-google-recaptcha';

type Props = {
  value: ?any,
  onChange: (response: string) => void,
};

export class Captcha extends React.PureComponent<Props> {
  captcha: ?Object;

  render() {
    const { onChange } = this.props;
    return (
      <ReCAPTCHA
        ref={c => (this.captcha = c)}
        style={{ transform: 'scale(0.85)', transformOrigin: '0 0' }}
        sitekey="6LfKLxsTAAAAANGSsNIlspDarsFFK53b4bKiBYKC"
        onChange={onChange}
      />
    );
  }
}

export default Captcha;
