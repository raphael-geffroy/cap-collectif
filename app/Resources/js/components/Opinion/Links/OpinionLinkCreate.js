import React from 'react';
import {IntlMixin} from 'react-intl';
import OpinionLinkCreateButton from './OpinionLinkCreateButton';
import SubmitButton from '../../Form/SubmitButton';
import CloseButton from '../../Form/CloseButton';
import OpinionLinkCreateInfos from './OpinionLinkCreateInfos';
import OpinionForm from './../Form/OpinionForm';
import OpinionTypeActions from '../../../actions/OpinionTypeActions';
import OpinionLinkActions from '../../../actions/OpinionLinkActions';
import {Modal} from 'react-bootstrap';

const OpinionLinkCreate = React.createClass({
  propTypes: {
    opinion: React.PropTypes.object.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      showModal: false,
      isSubmitting: false,
      availableTypes: [],
    };
  },

  componentDidMount() {
    OpinionTypeActions
      .getAvailableTypes(this.props.opinion.type.id)
      .then((availableTypes) => {
        this.setState({'availableTypes': availableTypes});
      });
  },

  handleFailure() {
    this.setState({isSubmitting: false});
  },

  handleSubmit() {
    this.setState({isSubmitting: true});
  },

  close() {
    this.setState({showModal: false});
  },

  show() {
    this.setState({showModal: true});
  },

  handleSubmitSuccess() {
    this.close();
    this.setState({isSubmitting: false});
    OpinionLinkActions.load(this.props.opinion.id, 'last');
  },

  render() {
    if (!this.props.opinion.isContribuable) {
      return null;
    }

    return (
      <div>
        <OpinionLinkCreateButton handleClick={this.show.bind(null, this)} />
        <Modal
          animation={false}
          show={this.state.showModal}
          onHide={this.close.bind(null, this)}
          bsSize="large"
          aria-labelledby="contained-modal-title-lg"
        >
          <Modal.Header closeButton>
            <Modal.Title id="contained-modal-title-lg">
              { this.getIntlMessage('source.add') }
            </Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <OpinionLinkCreateInfos opinion={this.props.opinion} />
            <OpinionForm
              action="create"
              opinion={this.props.opinion}
              availableTypes={this.state.availableTypes}
              isSubmitting={this.state.isSubmitting}
              onValidationFailure={this.handleFailure.bind(null, this)}
              onSubmitSuccess={this.handleSubmitSuccess.bind(null, this)}
              onSubmitFailure={this.handleFailure.bind(null, this)}
            />
          </Modal.Body>
          <Modal.Footer>
            <CloseButton onClose={this.close.bind(null, this)} />
            <SubmitButton
              id="confirm-opinion-link-create"
              isSubmitting={this.state.isSubmitting}
              onSubmit={this.handleSubmit.bind(null, this)}
            />
          </Modal.Footer>
        </Modal>
      </div>
    );
  },

});

export default OpinionLinkCreate;
