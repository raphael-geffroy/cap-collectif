import * as React from 'react';
import styled from 'styled-components';
import { storiesOf } from '@storybook/react';
import { boolean, text, withKnobs } from '@storybook/addon-knobs';
import { setIntlConfig, withIntl } from 'storybook-addon-intl';
import { addLocaleData, injectIntl } from 'react-intl';
import frLocaleData from 'react-intl/locale-data/fr';
import { Button } from 'react-bootstrap';

import { UserAvatar } from '../components/User/UserAvatar';
import Navbar from '../components/Navbar/Navbar';
import { TabsItemContainer, TabsLink, TabsDivider } from '../components/Ui/TabsBar/styles';
import TabsBarDropdown from '../components/Ui/TabsBar/TabsBarDropdown';

import { items, itemWithChildren } from './mocks/navbarItems';
import { author as userMock } from './mocks/users';

addLocaleData(frLocaleData);

// Provide your messages
const messages = {
  en: {
    'global.navbar.see_more': 'See more',
    'navbar.homepage': 'Home',
    'navbar.skip_links.menu': 'Go to menu',
    'navbar.skip_links.content': 'Go to content',
    'active.page': 'active page',
  },
  fr: {
    'global.navbar.see_more': 'Plus',
    'navbar.homepage': 'Accueil',
    'navbar.skip_links.menu': 'Aller au menu',
    'navbar.skip_links.content': 'Aller au contenu',
    'active.page': 'page active',
  },
};

const getMessages = locale => messages[locale];

setIntlConfig({
  locales: ['fr', 'en'],
  defaultLocale: 'fr',
  getMessages,
});

const Container = styled.div`
  border: 1px solid #ddd;
`;

const ButtonsContainer = styled.div`
  padding: ${props => (props.vertical ? '10px 15px' : '0 15px')};
`;

const ContentRight = ({ intl, user, features, vertical }) => (
  <>
    {features.search && (
      <TabsItemContainer vertical={vertical} as="div" role="search" aria-label="Rechercher">
        <TabsLink id="navbar-search" eventKey={1} href="/search">
          <i className="cap cap-magnifier" />
          <span className="visible-xs-inline" style={{ whiteSpace: 'nowrap' }}>
            {' Rechercher'}
          </span>
        </TabsLink>
      </TabsItemContainer>
    )}
    {user ? (
      <TabsBarDropdown
        pullRight
        eventKey={3}
        intl={intl}
        vertical={vertical}
        id="navbar-username"
        toggleElement={
          <span>
            <UserAvatar user={user} size={34} anchor={false} />
            <span>{user.username}</span>
          </span>
        }>
        {user.isAdmin && (
          <TabsLink eventKey={3.1} href="/admin">
            <i className="cap-setting-gears-1 mr-10" aria-hidden="true" />
            Administration
          </TabsLink>
        )}
        {features.profiles && (
          <TabsLink eventKey={3.2} href={`/profile/${user.uniqueId}`}>
            <i className="cap cap-id-8 mr-10" aria-hidden="true" />
            Mon profil
          </TabsLink>
        )}
        {user.isEvaluer && (
          <TabsLink eventKey={3.3} href="/evaluations">
            <i className="cap cap-edit-write mr-10" aria-hidden="true" />
            Mes analyses
          </TabsLink>
        )}
        <TabsLink eventKey={3.4} href="/profile/edit-profile">
          <i className="cap cap-setting-adjustment mr-10" aria-hidden="true" />
          Paramètres
        </TabsLink>
        <TabsDivider aria-hidden="true" />
        <TabsLink key={3.6} eventKey={3.6} id="logout-button" onClick={() => {}}>
          <i className="cap cap-power-1 mr-10" aria-hidden="true" />
          Déconnexion
        </TabsLink>
      </TabsBarDropdown>
    ) : (
      <ButtonsContainer vertical={vertical}>
        <Button
          onClick={() => {}}
          aria-label="Ouvrir la modale d'inscription"
          className="btn--registration navbar-btn">
          Inscription
        </Button>{' '}
        <Button
          onClick={() => {}}
          aria-label="Ouvrir la modale d'inscription"
          className="btn--connection navbar-btn btn-darkest-gray">
          Connexion
        </Button>
      </ButtonsContainer>
    )}
  </>
);

const ContentRightWithIntl = injectIntl(ContentRight);

storiesOf('NavBar', module)
  .addDecorator(withKnobs)
  .addDecorator(withIntl)
  .add('with 2 items', () => {
    const siteName = text('site name', 'Cap-Collectif');
    const logo = text(
      'logo url',
      'https://cap-collectif.com/uploads/2016/03/logo-complet-site.png',
    );

    return (
      <Container>
        <Navbar logo={logo} items={[items[0], items[1]]} siteName={siteName} />
      </Container>
    );
  })
  .add('with many items', () => {
    const siteName = text('site name', 'Cap-Collectif');
    const logo = text(
      'logo url',
      'https://cap-collectif.com/uploads/2016/03/logo-complet-site.png',
    );

    return (
      <Container>
        <Navbar logo={logo} items={items} siteName={siteName} />
      </Container>
    );
  })
  .add('with a submenu', () => {
    const siteName = text('site name', 'Cap-Collectif');
    const logo = text(
      'logo url',
      'https://cap-collectif.com/uploads/2016/03/logo-complet-site.png',
    );

    const newItems = items.slice(0);
    newItems.splice(5, 0, itemWithChildren);

    return (
      <Container>
        <Navbar logo={logo} items={newItems} siteName={siteName} />
      </Container>
    );
  })
  .add('not logged', () => {
    const withSearch = boolean('with search', true);
    const siteName = text('site name', 'Cap-Collectif');
    const logo = text(
      'logo url',
      'https://cap-collectif.com/uploads/2016/03/logo-complet-site.png',
    );

    const contentRight = <ContentRightWithIntl user={null} features={{ search: withSearch }} />;

    return (
      <Container>
        <Navbar logo={logo} items={items} siteName={siteName} contentRight={contentRight} />
      </Container>
    );
  })
  .add('logged', () => {
    const withSearch = boolean('with search', true);
    const siteName = text('site name', 'Cap-Collectif');
    const logo = text(
      'logo url',
      'https://cap-collectif.com/uploads/2016/03/logo-complet-site.png',
    );

    const contentRight = (
      <ContentRightWithIntl user={userMock} features={{ search: withSearch, profiles: true }} />
    );

    return (
      <Container>
        <Navbar logo={logo} items={items} siteName={siteName} contentRight={contentRight} />
      </Container>
    );
  });
