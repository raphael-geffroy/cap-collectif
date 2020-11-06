// @flow
import * as React from 'react';
import { createFragmentContainer, fetchQuery, graphql } from 'react-relay';
import { Field } from 'redux-form';
import { FormattedMessage, type IntlShape, useIntl } from 'react-intl';
import { Container } from '../common.style';
import { InstructionContainer, InfoRow, InfoMailingList, ButtonMembers } from './style';
import component from '~/components/Form/Field';
import Icon, { ICON_NAME } from '~ui/Icons/Icon';
import ModalMembers from '~/components/Admin/Emailing/ModalMembers/ModalMembers';
import ModalInternalMembers, {
  type InternalMembers,
} from '~/components/Admin/Emailing/ModalMembers/ModalInternalMembers';
import colors from '~/utils/colors';
import type { Parameter_query } from '~relay/Parameter_query.graphql';
import type { Parameter_emailingCampaign } from '~relay/Parameter_emailingCampaign.graphql';
import type { Parameter_UsersQueryResponse } from '~relay/Parameter_UsersQuery.graphql';
import environment from '~/createRelayEnvironment';

export const DEFAULT_MAILING_LIST = ['REGISTERED', 'CONFIRMED', 'NOT_CONFIRMED'];

type Props = {|
  emailingCampaign: Parameter_emailingCampaign,
  query: Parameter_query,
  disabled: boolean,
  showError: boolean,
|};

const COUNT_USERS_QUERY = graphql`
  query Parameter_UsersQuery {
    users {
      totalCount
      edges {
        node {
          id
          email
          isEmailConfirmed
        }
      }
    }
  }
`;

const loadUsersCount = (): Promise<Parameter_UsersQueryResponse> =>
  new Promise(async resolve => {
    const response = await fetchQuery(environment, COUNT_USERS_QUERY, {});
    resolve(response);
  });

const getMailingListName = (mailingInternalSelected: string, intl: IntlShape): string => {
  switch (mailingInternalSelected) {
    case 'REGISTERED':
      return intl.formatMessage({ id: 'default-mailing-list-registered' });
    case 'CONFIRMED':
      return intl.formatMessage({ id: 'default-mailing-list-registered-confirmed' });
    case 'NOT_CONFIRMED':
      return intl.formatMessage({ id: 'default-mailing-list-registered-not-confirmed' });
    default:
      return '';
  }
};

const getMailingInternalUsers = (
  mailingInternalSelected: string,
  users: InternalMembers,
): InternalMembers => {
  switch (mailingInternalSelected) {
    case 'CONFIRMED':
      return ((users.edges
        ?.filter(Boolean)
        .filter(edge => edge.node && edge.node.isEmailConfirmed): any): InternalMembers);
    case 'NOT_CONFIRMED':
      return ((users.edges
        ?.filter(Boolean)
        .filter(edge => edge.node && !edge.node.isEmailConfirmed): any): InternalMembers);
    case 'REGISTERED':
    default:
      return users;
  }
};

export const ParameterPage = ({ emailingCampaign, query, disabled, showError }: Props) => {
  const { mailingList, mailingInternal } = emailingCampaign;
  const { mailingLists } = query;
  const intl = useIntl();
  const [modalMembersOpened, showModalMembers] = React.useState<boolean>(false);
  const hasMailingList = !!mailingList || !!mailingInternal;
  const [countUsers, setCountUsers] = React.useState<number>(0);
  const [mailingInternalUsers, setMailingInternalUsers] = React.useState<?InternalMembers>(null);

  React.useEffect(() => {
    if (mailingInternal) {
      loadUsersCount().then(({ users }: Parameter_UsersQueryResponse) => {
        setCountUsers(users.totalCount);
        const usersOfChoicesMailingInternal = getMailingInternalUsers(mailingInternal, users);
        setMailingInternalUsers(usersOfChoicesMailingInternal);
      });
    }

    if (mailingList) {
      setCountUsers(mailingList.users.totalCount);
    }
  }, [mailingInternal, mailingList]);

  return (
    <Container>
      <h3>{intl.formatMessage({ id: 'admin-title-parameter-mailing-list' })}</h3>

      <Field
        type="text"
        id="senderEmail"
        name="senderEmail"
        component={component}
        label={intl.formatMessage({ id: 'sender-address' })}
        disabled
        disableValidation={!showError}
      />

      <Field
        type="select"
        id="mailingList"
        name="mailingList"
        component={component}
        label={intl.formatMessage({ id: 'admin-menu-emailing-list' })}
        disabled={disabled}
        disableValidation={!showError}>
        <option value={-1}>{intl.formatMessage({ id: 'select-list' })}</option>
        <option value="REGISTERED">
          {intl.formatMessage({ id: 'default-mailing-list-registered' })}
        </option>
        <option value="CONFIRMED">
          {intl.formatMessage({ id: 'default-mailing-list-registered-confirmed' })}
        </option>
        <option value="NOT_CONFIRMED">
          {intl.formatMessage({ id: 'default-mailing-list-registered-not-confirmed' })}
        </option>

        {mailingLists?.totalCount > 0 &&
          mailingLists?.edges
            ?.filter(Boolean)
            .map(edge => edge.node)
            .filter(Boolean)
            .map(m => (
              <option key={m.id} value={m.id}>
                {m.name}
              </option>
            ))}
      </Field>

      {hasMailingList && countUsers > 0 && (
        <InfoMailingList>
          <InfoRow>
            <Icon name={ICON_NAME.newUser} size={13} color={colors.darkText} />
            <p>
              <span>{countUsers}</span>{' '}
              <FormattedMessage id="global.members" values={{ num: countUsers }} />
            </p>

            {mailingList?.project && (
              <p>
                <Icon name={ICON_NAME.favoriteBook} size={16} color={colors.darkText} />
                <span>{mailingList.project.title}</span>
              </p>
            )}
          </InfoRow>

          {countUsers > 0 && (
            <ButtonMembers type="button" onClick={() => showModalMembers(true)}>
              {intl.formatMessage({ id: 'consult-members' })}
            </ButtonMembers>
          )}
        </InfoMailingList>
      )}

      <InstructionContainer>
        <p
          dangerouslySetInnerHTML={{
            __html: intl.formatHTMLMessage(
              { id: 'admin-help-mailing-list-access' },
              { linkProject: '/admin/capco/app/project/list' },
            ),
          }}
        />
      </InstructionContainer>

      {hasMailingList &&
        (mailingInternal ? (
          <ModalInternalMembers
            show={modalMembersOpened}
            onClose={() => showModalMembers(false)}
            mailingListName={getMailingListName(mailingInternal, intl)}
            members={mailingInternalUsers}
          />
        ) : (
          <ModalMembers
            show={modalMembersOpened}
            onClose={() => showModalMembers(false)}
            mailingList={mailingList}
          />
        ))}
    </Container>
  );
};

export default createFragmentContainer(ParameterPage, {
  emailingCampaign: graphql`
    fragment Parameter_emailingCampaign on EmailingCampaign {
      mailingList {
        name
        project {
          title
        }
        users {
          totalCount
        }
        ...ModalMembers_mailingList
      }
      mailingInternal
    }
  `,
  query: graphql`
    fragment Parameter_query on Query {
      mailingLists {
        totalCount
        edges {
          node {
            id
            name
          }
        }
      }
    }
  `,
});