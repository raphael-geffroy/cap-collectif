import React, { PropTypes } from 'react';
import { Button } from 'react-bootstrap';
import classNames from 'classnames';
import { IntlMixin } from 'react-intl';

const RankingArrow = React.createClass({
  displayName: 'RankingArrow',
  propTypes: {
    type: PropTypes.string.isRequired,
    onClick: PropTypes.func.isRequired,
    disabled: PropTypes.bool,
  },
  mixins: [ IntlMixin ],

  getDefaultProps() {
    return {
      disabled: false,
    };
  },

  render() {
    const { type, onClick, disabled } = this.props;
    const classes = classNames({
      'cap': true,
      'cap-delete-1': type === 'left',
      'cap-arrow-2-1': type === 'right',
      'cap-arrow-67': type === 'down',
      'cap-arrow-68': type === 'up',
    });
    return (
      <Button
        disabled={disabled}
        onClick={disabled ? null : onClick}
        className={'ranking__item__arrow ranking__item__arrow--' + type}
      >
        {
          type === 'right' &&
          <span className="hidden-xs" style={{ marginRight: '10px' }}>
              {this.getIntlMessage('global.form.ranking.select')}
            </span>
        }
        <i className={classes}></i>
      </Button>
    );
  },

});

export default RankingArrow;
