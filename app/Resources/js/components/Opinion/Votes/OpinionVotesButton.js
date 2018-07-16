// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import { FormattedMessage } from 'react-intl';
import { connect, type MapStateToProps } from 'react-redux';
import { Button } from 'react-bootstrap';
import LoginOverlay from '../../Utils/LoginOverlay';
import { VOTE_WIDGET_SIMPLE, VOTE_WIDGET_BOTH } from '../../../constants/VoteConstants';
import {
  deleteVoteVersion,
  deleteVoteOpinion,
  voteOpinion,
  voteVersion,
} from '../../../redux/modules/opinion';
import type { VoteValue, OpinionAndVersion, State, Dispatch } from '../../../types';

const valueToObject = (value: VoteValue): Object => {
  if (value === -1) {
    return {
      style: 'danger',
      str: 'nok',
      icon: 'cap cap-hand-unlike-2-1',
    };
  }
  if (value === 0) {
    return {
      style: 'warning',
      str: 'mitige',
      icon: 'cap cap-hand-like-2 icon-rotate',
    };
  }
  return {
    style: 'success',
    str: 'ok',
    icon: 'cap cap-hand-like-2-1',
  };
};

type Props = {
  style?: Object,
  opinion: Object,
  value: $FlowFixMe,
  active: boolean,
  disabled?: boolean,
  dispatch: Dispatch,
  user?: Object,
  features: Object,
};

export class OpinionVotesButton extends React.Component<Props> {
  static defaultProps = {
    style: {},
    disabled: false,
  };

  isVersion = () => {
    const { opinion } = this.props;
    return !!opinion.parent;
  };

  vote = () => {
    const { opinion, value, dispatch } = this.props;
    if (this.isVersion()) {
      voteVersion(value, opinion.id, opinion.parent.id, dispatch);
    } else {
      voteOpinion(value, opinion.id, dispatch);
    }
  };

  deleteVote = () => {
    const { opinion, dispatch } = this.props;
    if (this.isVersion()) {
      deleteVoteVersion(opinion.id, opinion.parent.id, dispatch);
    } else {
      deleteVoteOpinion(opinion.id, dispatch);
    }
  };

  voteAction = () => {
    const { disabled, user, active } = this.props;
    if (!user || disabled) {
      return null;
    }
    return active ? this.deleteVote() : this.vote();
  };

  voteIsEnabled = () => {
    const { opinion, value } = this.props;
    const voteType = opinion.section.voteWidgetType;
    if (voteType === VOTE_WIDGET_BOTH) {
      return true;
    }
    if (voteType === VOTE_WIDGET_SIMPLE) {
      return value === 1;
    }
    return false;
  };

  render() {
    if (!this.voteIsEnabled()) {
      return null;
    }
    const { disabled, style, value, active } = this.props;
    const data = valueToObject(value);
    return (
      <LoginOverlay>
        <Button
          style={style}
          bsStyle={data.style}
          className="btn--outline"
          onClick={this.voteAction}
          active={active}
          aria-label={
            <FormattedMessage
              id={active ? `vote.aria_label_active.${data.str}` : `vote.aria_label.${data.str}`}
            />
          }
          disabled={disabled}>
          <i className={data.icon} /> <FormattedMessage id={`vote.${data.str}`} />
        </Button>
      </LoginOverlay>
    );
  }
}

const mapStateToProps: MapStateToProps<*, *, *> = (
  state: State,
  { opinion, value }: { value: VoteValue, opinion: OpinionAndVersion },
) => {
  const vote = opinion.parent
    ? state.opinion.versionsById[opinion.id].userVote
    : state.opinion.opinionsById[opinion.id].userVote;
  return {
    features: state.default.features,
    user: state.user.user,
    active: vote !== null && vote === value,
  };
};

const container = connect(mapStateToProps)(OpinionVotesButton);

export default createFragmentContainer(container, {
  opinion: graphql`
    fragment OpinionVotesButton_opinion on OpinionOrVersion {
      ... on Opinion {
        id
        section {
          voteWidgetType
        }
        #viewerHasVote
        #viewerVote
      }
      ... on Version {
        id
        section {
          voteWidgetType
        }
        parent {
          id
        }
      }
    }
  `,
});