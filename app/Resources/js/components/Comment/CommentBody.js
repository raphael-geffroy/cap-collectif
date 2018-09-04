// @flow
import React from 'react';
import { graphql, createFragmentContainer } from 'react-relay';
import Linkify from 'react-linkify';
import { FormattedMessage } from 'react-intl';
import nl2br from 'react-nl2br';
import type { CommentBody_comment } from './__generated__/CommentBody_comment.graphql';

type Props = {
  comment: CommentBody_comment,
};

type State = {
  expanded: boolean,
};

class CommentBody extends React.Component<Props, State> {
  state = {
    expanded: false,
  };

  textShouldBeTruncated = () => {
    const { comment } = this.props;
    return comment.body && comment.body.length > 400;
  };

  generateText = () => {
    const { comment } = this.props;
    let text = '';

    if (!this.textShouldBeTruncated() || this.state.expanded) {
      text = comment.body;
    } else if (comment.body) {
      text = comment.body.substr(0, 400);
      text = text.substr(0, Math.min(text.length, text.lastIndexOf(' ')));
      if (text.indexOf('.', text.length - 1) === -1) {
        text += '...';
      }
      text += ' ';
    }

    return text;
  };

  expand = (expanded: boolean) => {
    this.setState({
      expanded,
    });
  };

  renderReadMoreOrLess = () => {
    if (this.textShouldBeTruncated() && !this.state.expanded) {
      return (
        <button className="btn-link" onClick={this.expand.bind(this, true)}>
          {<FormattedMessage id="global.read_more" />}
        </button>
      );
    }
  };

  renderTrashedLabel = () => {
    const { comment } = this.props;
    if (comment.trashed) {
      return (
        <span className="label label-default">
          {<FormattedMessage id="comment.trashed.label" />}
        </span>
      );
    }
    return null;
  };

  render() {
    return (
      <div className="opinion__text">
        {this.renderTrashedLabel()}
        <Linkify properties={{ className: 'external-link' }}>{nl2br(this.generateText())}</Linkify>
        {this.renderReadMoreOrLess()}
      </div>
    );
  }
}

export default createFragmentContainer(CommentBody, {
  comment: graphql`
    fragment CommentBody_comment on Comment {
      trashed
      body
    }
  `,
});
