// @flow
import * as React from 'react';
import { storiesOf } from '@storybook/react';
import { text, boolean, select } from '@storybook/addon-knobs';
import { Button, ButtonGroup, ButtonToolbar, DropdownButton, MenuItem } from 'react-bootstrap';

const bsStyleOptions = {
  Warning: 'warning',
  Danger: 'danger',
  Success: 'success',
  Info: 'info',
  Primary: 'primary',
  Link: 'link',
  Null: null,
};

const bsSizeOptions = {
  Large: 'large',
  Normal: null,
  Small: 'small',
  XSmall: 'xsmall',
};

storiesOf('Actions', module)
  .add(
    'Buttons',
    () => {
      const content = text('Content', 'My button');
      const bsStyle = select('BsStyle', bsStyleOptions, 'primary');
      const bsSize = select('BsSize', bsSizeOptions, null);
      const disabled = boolean('Disabled', false);
      const outline = boolean('Outline', false);

      return (
        <Button
          bsStyle={bsStyle}
          bsSize={bsSize}
          className={outline ? 'btn--outline' : ''}
          disabled={disabled}>
          {content}
        </Button>
      );
    },
    {
      info: {
        text: `
          Ce composant est utilisé ...
        `,
      },
    },
  )
  .add(
    'Buttons group',
    () => {
      const bsSize = select('BsSize', bsSizeOptions, null);

      return (
        <ButtonGroup bsSize={bsSize}>
          <Button bsStyle="primary">Left</Button>
          <Button bsStyle="primary">Middle</Button>
          <Button bsStyle="primary">Right</Button>
        </ButtonGroup>
      );
    },
    {
      info: {
        text: `
          Ce composant est utilisé ...
        `,
        propTablesExclude: [Button],
      },
    },
  )
  .add(
    'Buttons toolbar',
    () => (
      <ButtonToolbar>
        <Button bsStyle="primary">Button</Button>
        <Button bsStyle="primary">Other button</Button>
      </ButtonToolbar>
    ),
    {
      info: {
        text: `
          Ce composant est utilisé ...
        `,
        propTablesExclude: [Button],
      },
    },
  )
  .add(
    'Dropdown button',
    () => {
      const bsSize = select('BsSize', bsSizeOptions, null);
      const bsStyle = select('BsStyle', bsStyleOptions, 'primary');
      const title = text('Title', 'My title');
      const active = boolean('Active props for first item', false);
      const header = boolean('Header props for first item', false);

      return (
        <DropdownButton bsStyle={bsStyle} bsSize={bsSize} title={title}>
          <MenuItem header={header} active={active}>
            Item
          </MenuItem>
          <MenuItem divider />
          <MenuItem>Other item</MenuItem>
        </DropdownButton>
      );
    },
    {
      info: {
        text: `
          Ce composant est utilisé ...
        `,
      },
    },
  );
