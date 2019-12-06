// @flow
import * as React from 'react';
import { FormattedMessage } from 'react-intl';
// TODO https://github.com/cap-collectif/platform/issues/7774
// eslint-disable-next-line no-restricted-imports
import { Col, ListGroup, Row } from 'react-bootstrap';
import { DragDropContext, DropTarget } from 'react-dnd';
import HTML5Backend from 'react-dnd-html5-backend';
import ReactDOM from 'react-dom';
import RankingBox from './RankingBox';
import { ITEM_TYPE } from '../../constants/RankingConstants';

const itemTarget = {
  drop() {},
};

type Props = {
  field: Object,
  connectDropTarget: Function,
  onRankingChange: Function,
  disabled: boolean,
  onBlur: Function,
};

type State = {
  items: {
    pickBox: Array<Object>,
    choiceBox: Array<Object>,
  },
  choicesHeight: string,
};

export class RankingBlock extends React.Component<Props, State> {
  static displayName = 'RankingBlock';

  static defaultProps = { disabled: false };

  choiceBox: ?React.Component<*>;

  pickBox: ?React.Component<*>;

  constructor(props: Props) {
    super(props);
    this.state = {
      items: { pickBox: props.field.choices, choiceBox: props.field.values || [] },
      choicesHeight: 'auto',
    };
  }

  componentDidMount() {
    this.recalculateChoicesHeight();
  }

  moveItem = (atList: number, atIndex: number, it: Object) => {
    const { onRankingChange, onBlur } = this.props;
    const { items } = this.state;
    const { item, list, index } = this.findItem(it.id);
    const elements = JSON.parse(JSON.stringify(items));
    elements[list].splice(index, 1);
    elements[atList].splice(atIndex, 0, item);
    this.setState(
      {
        items: elements,
      },
      () => {
        onRankingChange(elements.choiceBox);
        this.recalculateChoicesHeight();
      },
    );

    onBlur();
  };

  recalculateChoicesHeight() {
    const domNode = ReactDOM.findDOMNode(this.choiceBox);
    if (!domNode) return;
    const height = `${$(domNode).height()}px`;
    if (height !== '0px' && height !== 'undefinedpx') {
      this.setState({ choicesHeight: height });
    }
  }

  findItem(id: string) {
    const { items } = this.state;
    let itemList = null;
    let item = null;
    let itemIndex = null;
    Object.keys(items).map(listKey => {
      items[listKey].map((i, iKey) => {
        if (i.id === id) {
          itemList = listKey;
          item = i;
          itemIndex = iKey;
        }
      });
    });
    return { item, list: itemList, index: itemIndex };
  }

  render() {
    if (__SERVER__) {
      return <span />;
    }
    const { field, connectDropTarget, disabled } = this.props;
    const { items, choicesHeight } = this.state;

    let spotsNb = field.choices.length;

    if (field.values) {
      spotsNb += field.values.length;
    }

    return connectDropTarget(
      <div>
        <Row>
          <Col xs={6}>
            <h5 className="h5 mt-0 hidden-print">
              {<FormattedMessage id="global.form.ranking.pickBox.title" />}
            </h5>
            <ListGroup className="ranking__pick-box">
              <RankingBox
                ref={c => {
                  this.pickBox = c;
                }}
                items={items.pickBox}
                spotsNb={spotsNb}
                listType="pickBox"
                fieldId={field.id}
                moveItem={this.moveItem}
                disabled={disabled}
              />
            </ListGroup>
          </Col>
          <Col xs={6} className="hidden-print">
            <h5 className="h5">{<FormattedMessage id="global.form.ranking.choiceBox.title" />}</h5>
            <ListGroup className="ranking__choice-box" style={{ height: choicesHeight }}>
              <RankingBox
                ref={c => {
                  this.choiceBox = c;
                }}
                items={items.choiceBox}
                spotsNb={spotsNb}
                listType="choiceBox"
                fieldId={field.id}
                moveItem={this.moveItem}
                disabled={disabled}
              />
              {items.choiceBox.length === 0 ? (
                <div
                  className="hidden-xs ranking__choice-box__placeholder"
                  style={{ height: `${spotsNb * 45}px` }}>
                  <span>{<FormattedMessage id="global.form.ranking.choiceBox.placeholder" />}</span>
                </div>
              ) : null}
            </ListGroup>
          </Col>
        </Row>
      </div>,
    );
  }
}

export default DragDropContext(HTML5Backend)(
  DropTarget(ITEM_TYPE, itemTarget, connect => ({
    connectDropTarget: connect.dropTarget(),
  }))(RankingBlock),
);