import {RECEIVE_COUNT, RECEIVE_ELEMENTS, RECEIVE_ELEMENT, ARCHIVE_ELEMENT, NOTE_ELEMENT, MOVE_ELEMENT, UPDATE_ELEMENT_SUCCESS, UPDATE_ELEMENT_FAILURE} from '../constants/SynthesisElementConstants';
import BaseStore from './BaseStore';

function removeElementFromArray(els, el) {
  let index = -1;
  index = els.map((e) => {
    return e.id;
  }).indexOf(el.id);

  if (index > -1) {
    els.splice(index, 1);
  }
  return els;
}

function addElementToArray(els, el) {
  let index = -1;
  index = els.map((e) => {
    return e.id;
  }).indexOf(el.id);

  if (index === -1) {
    els.push(el);
  }
  return els;
}

class SynthesisElementStore extends BaseStore {

  constructor() {
    super();
    this.register(this._registerToActions.bind(this));
    this._element = null;
    this._elements = {
      'new': [],
      'archived': [],
      'published': [],
      'unpublished': [],
      'all': [],
      'tree': [],
    };
    this._countNew = 0;
    this._isElementSync = false;
    this._isCountSync = false;
    this._isInboxSync = {
      'new': false,
      'archived': false,
      'published': false,
      'unpublished': false,
      'all': false,
      'tree': false,
    };
    this._messages = {
      errors: [],
      success: [],
    };
  }

  _registerToActions(action) {
    switch (action.actionType) {
      case RECEIVE_COUNT:
        this._countNew = action.count;
        this._isCountSync = true;
        this.emitChange();
        break;
      case RECEIVE_ELEMENT:
        this._element = action.element;
        this._isElementSync = true;
        this.emitChange();
        break;
      case RECEIVE_ELEMENTS:
        this._elements[action.type] = action.elements;
        this._isInboxSync[action.type] = true;
        this.emitChange();
        break;
      case ARCHIVE_ELEMENT:
        this._resetMessages();
        // Update data
        this._elements.new = removeElementFromArray(this._elements.new, this._element);
        this._countNew = this._elements.new.length;
        this.elements.archived = addElementToArray(this._elements.archived, this._element);
        if (action.published) {
          this._elements.published = addElementToArray(this._elements.published, this._element);
          this._elements.unpublished = removeElementFromArray(this._elements.unpublished, this._element);
        } else {
          this._elements.unpublished = addElementToArray(this._elements.unpublished, this._element);
          this._elements.published = removeElementFromArray(this._elements.published, this._element);
        }
        // Apply changes to element
        this._element.archived = action.archived;
        this._element.published = action.published;
        this.emitChange();
        break;
      case NOTE_ELEMENT:
        this._element.notation = action.notation;
        this._resetMessages();
        this.emitChange();
        break;
      case MOVE_ELEMENT:
        this._element.parent = action.parent;
        this._resetMessages();
        this.emitChange();
        break;
      case UPDATE_ELEMENT_SUCCESS:
        this._resetMessages();
        this._messages.success.push(action.message);
        this.emitChange();
        break;
      case UPDATE_ELEMENT_FAILURE:
        this._messages.errors.push(action.message);
        this._messages.success = [];
        this._isElementSync = false;
        this._isCountSync = false;
        this._resetInboxSync();
        this.emitChange();
        break;
      default: break;
    }
  }

  get isElementSync() {
    return this._isElementSync;
  }

  get isCountSync() {
    return this._isCountSync;
  }

  get isInboxSync() {
    return this._isInboxSync;
  }

  get element() {
    return this._element;
  }

  get elements() {
    return this._elements;
  }

  get countNew() {
    return this._countNew;
  }

  get messages() {
    return this._messages;
  }

  _resetMessages() {
    this._messages.errors = [];
    this._messages.success = [];
  }

  _resetInboxSync() {
    this._isInboxSync.new = false;
    this._isInboxSync.archived = false;
    this._isInboxSync.published = false;
    this._isInboxSync.unpublished = false;
    this._isInboxSync.tree = false;
  }

}

export default new SynthesisElementStore();
