import {VOTE_WIDGET_DISABLED, VOTE_WIDGET_BOTH} from '../../constants/VoteConstants';

import OpinionPreview from './OpinionPreview';
import OpinionButtons from './OpinionButtons';
import OpinionAppendices from './OpinionAppendices';
import OpinionBody from './OpinionBody';
import VotePiechart from '../Utils/VotePiechart';
import UserAvatar from '../User/UserAvatar';
import VotesBar from '../Utils/VotesBar';

const Row = ReactBootstrap.Row;
const Col = ReactBootstrap.Col;
const Well = ReactBootstrap.Well;

const FormattedMessage = ReactIntl.FormattedMessage;

const OpinionBox = React.createClass({
  propTypes: {
    opinion: React.PropTypes.object.isRequired,
    rankingThreshold: React.PropTypes.number,
    opinionTerm: React.PropTypes.number,
  },
  mixins: [ReactIntl.IntlMixin],

  getMaxVotesValue() {
    return this.getOpinionType().votesThreshold;
  },

  getOpinionType() {
    return this.isVersion() ? this.props.opinion.parent.type : this.props.opinion.type;
  },

  getBoxLabel() {
    return this.isVersion() ? 'opinion.header.version'
      : this.props.opinionTerm === 0
        ? 'opinion.header.opinion'
        : 'opinion.header.article'
    ;
  },

  isVersion() {
    return this.props.opinion && this.props.opinion.parent ? true : false;
  },

  renderVotesHelpText() {
    const helpText = this.getOpinionType().votesHelpText;
    if (helpText) {
      return <Well bsSize="small" style={{marginBottom: '10px', fontSize: '14px'}}>{helpText}</Well>;
    }
  },

  renderUserAvatarVotes() {
    const opinion = this.props.opinion;
    const votes = opinion.votes;
    let moreVotes = null;

    if (opinion.votes_total > 5) {
      moreVotes = opinion.votes_total - 5;
    }

    return (
      <div style={{paddingTop: '20px'}}>
      {
        votes.map((vote) => {
          return <UserAvatar key={vote.user.id} user={vote.user} style={{marginRight: 5}} />;
        })
      }
      {moreVotes !== null
        ? <span>+ {moreVotes}</span>
        : null
      }
      </div>
    );
  },

  renderPieChart() {
    const opinion = this.props.opinion;
    return (
      <VotePiechart top={20} height={180} ok={opinion.votes_ok} nok={opinion.votes_nok}
                          mitige={opinion.votes_mitige}/>
    );
  },

  renderVotesBar() {
    const opinion = this.props.opinion;
    return (
      <div>
        {this.getOpinionType().votesThreshold ?
          <VotesBar max={this.getOpinionType().votesThreshold} value={opinion.votes_ok}
                    helpText={this.getOpinionType().votesThresholdHelpText}/>
          : null}
        {this.renderUserAvatarVotes()}
        <div><FormattedMessage message={this.getIntlMessage('global.votes')} num={opinion.votes_total}/></div>
      </div>
    );
  },

  renderVotes() {
    const opinion = this.props.opinion;
    const widgetType = this.getOpinionType().voteWidgetType;
    if (widgetType !== VOTE_WIDGET_DISABLED && (opinion.votes.length > 0 || this.getOpinionType().votesThreshold)) {
      if (opinion.votes.length > 0 && widgetType === VOTE_WIDGET_BOTH) {
        return (
          <Row style={{borderTop: '1px solid #ddd'}}>
            <Col sm={12} md={4}>
              {this.renderPieChart()}
            </Col>
            <Col sm={12} md={7} style={{paddingTop: '15px'}}>
              {this.renderVotesBar()}
            </Col>
          </Row>
        );
      }
      return (
        <Row style={{borderTop: '1px solid #ddd'}}>
          <Col sm={12} mdOffset={2} md={8} style={{paddingTop: '15px'}}>
            {this.renderVotesBar()}
          </Col>
        </Row>
      );
    }
    return null;
  },

  render() {
    const opinion = this.props.opinion;
    const color = this.getOpinionType().color;
    const backLink = this.isVersion() ? opinion.parent._links.show : opinion._links.type;
    const backTitle = this.isVersion() ? opinion.parent.title : this.getOpinionType().title;
    const headerTitle = this.getBoxLabel();

    const colorClass = 'opinion opinion--' + color + ' opinion--current';
    return (
      <div className="block block--bordered opinion__details">
        <div className={colorClass}>
          <div className="opinion__header opinion__header--centered">
            <a className="pull-left btn btn-default opinion__header__back" href={backLink}>
              <i className="cap cap-arrow-1-1"></i>
              <span className="hidden-xs hidden-sm"> {backTitle}</span>
            </a>
            <h2 className="h4 opinion__header__title">{this.getIntlMessage(headerTitle)}</h2>
          </div>
          <OpinionPreview rankingThreshold={this.props.rankingThreshold} opinionTerm={this.props.opinionTerm} opinion={opinion} link={false} />
        </div>
        <OpinionAppendices opinion={opinion} />
        <div className="opinion__description">
          <p className="h4" style={{marginTop: '0'}}>{opinion.title}</p>
          <OpinionBody opinion={opinion} />
          <div className="opinion__buttons" style={{marginTop: '15px', marginBottom: '15px'}} aria-label={this.getIntlMessage('vote.form')}>
            {this.renderVotesHelpText()}
            <OpinionButtons {...this.props} opinion={opinion} />
          </div>
          {this.renderVotes()}
        </div>
        {opinion.answer
          ? <div className="opinion__answer" id="answer">
              {opinion.answer.title
                ? <p className="h4" style={{marginTop: '0'}}>{opinion.answer.title}</p>
                : null
              }
              <div dangerouslySetInnerHTML={{__html: opinion.answer.body}} />
            </div>
          : null
        }
      </div>
    );
  },

});

export default OpinionBox;
