import React from 'react'
import { graphql, createFragmentContainer } from 'react-relay'
import { connect } from 'react-redux'
import type { ReportData } from '~/redux/modules/report'
import { submitReport } from '~/redux/modules/report'
import ReportBox from '../Report/ReportBox'
import type { CommentReportButton_comment } from '~relay/CommentReportButton_comment.graphql'
import type { Dispatch } from '~/types'
type Props = {
  readonly dispatch: Dispatch
  readonly comment: CommentReportButton_comment
}
export class CommentReportButton extends React.Component<Props> {
  handleReport = (data: ReportData) => {
    const { comment, dispatch } = this.props
    return submitReport(comment.id, data, dispatch, 'alert.success.report.comment')
  }

  render() {
    const { comment } = this.props
    return (
      <ReportBox
        id={`comment-${comment.id}`}
        reported={comment.viewerHasReport || false}
        onReport={this.handleReport}
        author={{
          uniqueId: comment.author ? comment.author.slug : null,
        }}
        newDesign
      />
    )
  }
}
// @ts-ignore
const container = connect<any, any>()(CommentReportButton)
export default createFragmentContainer(container, {
  comment: graphql`
    fragment CommentReportButton_comment on Comment @argumentDefinitions(isAuthenticated: { type: "Boolean!" }) {
      id
      viewerHasReport @include(if: $isAuthenticated)
      author {
        slug
      }
    }
  `,
})
