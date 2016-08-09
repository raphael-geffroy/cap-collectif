import React from 'react';
import { IntlMixin, FormattedMessage } from 'react-intl';
import SubmitButton from '../../Form/SubmitButton';
import CloseButton from '../../Form/CloseButton';
import IdeaActions from '../../../actions/IdeaActions';
import { Modal } from 'react-bootstrap';

const IdeaDeleteModal = React.createClass({
  propTypes: {
    idea: React.PropTypes.object.isRequired,
    show: React.PropTypes.bool.isRequired,
    onToggleModal: React.PropTypes.func.isRequired,
  },
  mixins: [IntlMixin],

  getInitialState() {
    return {
      isSubmitting: false,
    };
  },

  handleSubmit() {
    const { idea } = this.props;
    this.setState({ isSubmitting: true });
    IdeaActions
      .delete(idea.id)
      .then(() => {
        window.location.href = idea._links.index;
      })
      .catch(() => {
        this.setState({ isSubmitting: false });
      })
    ;
  },

  close() {
    const { onToggleModal } = this.props;
    onToggleModal(false);
  },

  show() {
    const { onToggleModal } = this.props;
    onToggleModal(true);
  },

  render() {
    const {
      idea,
      show,
    } = this.props;
    return (
      <div>
        <Modal
          animation={false}
          show={show}
          onHide={this.close}
          bsSize="large"
          aria-labelledby="contained-modal-title-lg"
        >
          <Modal.Header closeButton>
            <Modal.Title id="contained-modal-title-lg">
              { this.getIntlMessage('global.remove') }
            </Modal.Title>
          </Modal.Header>
          <Modal.Body>
            <p>
              <FormattedMessage
                message={this.getIntlMessage('idea.delete.confirm')}
                title={idea.title}
              />
            </p>
          </Modal.Body>
          <Modal.Footer>
            <CloseButton onClose={this.close} />
            <SubmitButton
              id="confirm-idea-delete"
              isSubmitting={this.state.isSubmitting}
              onSubmit={this.handleSubmit}
              label="global.remove"
              bsStyle="danger"
            />
          </Modal.Footer>
        </Modal>
      </div>
    );
  },

});

export default IdeaDeleteModal;
