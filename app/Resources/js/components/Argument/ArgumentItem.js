// @flow
import * as React from 'react';
import { FormattedDate, FormattedMessage } from 'react-intl';
import { graphql, createFragmentContainer } from 'react-relay';
import classNames from 'classnames';
import moment from 'moment';
import Linkify from 'react-linkify';
import { ListGroupItem, Label } from 'react-bootstrap';
import UserAvatar from '../User/UserAvatar';
import UserLink from '../User/UserLink';
import ArgumentButtons from './ArgumentButtons';
import UnpublishedLabel from '../Publishable/UnpublishedLabel';
import type { ArgumentItem_argument } from './__generated__/ArgumentItem_argument.graphql';

type Props = {
  argument: ArgumentItem_argument,
};

type ListArgumentItemProps = {
  children: React.Node,
};

const ListArgumentItem = ({ children }: ListArgumentItemProps) => (
  <li className="opinion  opinion--vote block  block--bordered  box">{children}</li>
);

class ArgumentItem extends React.Component<Props> {
  renderDate = () => {
    const argument = this.props.argument;
    if (!Modernizr.intl) {
      return null;
    }
    return (
      <p className="excerpt opinion__date">
        <FormattedDate
          value={moment(argument.publishedAt ? argument.publishedAt : argument.createdAt)}
          day="numeric"
          month="long"
          year="numeric"
          hour="numeric"
          minute="numeric"
        />
      </p>
    );
  };

  render() {
    const { argument } = this.props;
    const classes = classNames({
      opinion: true,
      'opinion--argument': true,
      'bg-vip': argument.author && argument.author.vip,
    });
    return (
      <ListArgumentItem className={classes} id={`arg-${argument.id}`}>
        <div className="opinion__body">
          <UserAvatar user={argument.author} className="pull-left" />
          <div className="opinion__data">
            <p className="h5 opinion__user">
              <UserLink user={argument.author} />
              {isProfile && this.renderLabel(argument.type)}
            </p>
            {this.renderDate()}
            {/* $FlowFixMe */}
            <UnpublishedLabel publishable={argument} />
          </div>
          <p
            className="opinion__text"
            style={{
              overflow: 'hidden',
              float: 'left',
              width: '100%',
              wordWrap: 'break-word',
            }}>
            <Linkify properties={{ className: 'external-link' }}>{argument.body}</Linkify>
          </p>
          <ArgumentButtons argument={argument} />
        </div>
      </ListArgumentItem>
    );
  }
}

export default createFragmentContainer(
  ArgumentItem,
  graphql`
    fragment ArgumentItem_argument on Argument
      @argumentDefinitions(isAuthenticated: { type: "Boolean!", defaultValue: true }) {
      ...UnpublishedLabel_publishable
      id
      createdAt
      publishedAt
      ...ArgumentButtons_argument @arguments(isAuthenticated: $isAuthenticated)
      author {
        id
        slug
        displayName
        show_url
        vip
        media {
          url
        }
      }
      body
    }
  `,
);
