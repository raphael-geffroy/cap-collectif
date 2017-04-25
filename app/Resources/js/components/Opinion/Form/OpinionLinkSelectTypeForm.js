import React, { PropTypes } from 'react';
import { IntlMixin } from 'react-intl';
import { connect } from 'react-redux';
import { reduxForm, Field as ReduxFormField, formValueSelector } from 'redux-form';
import Field from '../../Form/Field';

export const OpinionLinkSelectTypeForm = React.createClass({
  displayName: 'OpinionLinkSelectTypeForm',
  propTypes: {
    options: PropTypes.array.isRequired,
    onChange: PropTypes.func.isRequired,
    opinionType: PropTypes.any,
  },
  mixins: [IntlMixin],

  componentDidUpdate(prevProps) {
    const {
      onChange,
      opinionType,
    } = this.props;
    if (prevProps && prevProps.opinionType && prevProps.opinionType !== opinionType) {
      onChange(opinionType);
    }
  },

  render() {
    const { options } = this.props;
    return (
      <form>
        <ReduxFormField
          autoFocus
          label={this.getIntlMessage('opinion.link.select_type')}
          name={'opinionType'}
          type={'select'}
          component={Field}
          disableValidation
        >
          <option disabled>{this.getIntlMessage('global.select')}</option>
          {
            options.map((opt, i) => <option key={i} value={opt.id}>{opt.title}</option>)
          }
        </ReduxFormField>
      </form>
    );
  },

});

function mapStateToProps(state) {
  return {
    opinionType: formValueSelector('OpinionLinkSelectTypeForm')(state, 'opinionType'),
  };
}
export default reduxForm({ form: 'OpinionLinkSelectTypeForm' })(connect(mapStateToProps)(OpinionLinkSelectTypeForm));
