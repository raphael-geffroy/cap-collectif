// @flow
import * as React from 'react';
import { createPaginationContainer, graphql, type RelayPaginationProp } from 'react-relay';
import { FormattedMessage, useIntl } from 'react-intl';
import PickableList, { usePickableList } from '~ui/List/PickableList';
import { useDashboardMailingListContext } from './DashboardMailingList.context';
import * as S from './DashboardMailingList.style';
import ClearableInput from '~ui/Form/Input/ClearableInput';
import EmailingLoader from '../../EmailingLoader/EmailingLoader';
import ModalConfirmDelete from '~/components/Admin/Emailing/EmailingList/ModalConfirmDelete/ModalConfirmDelete';
import MailingListItem from '~/components/Admin/Emailing/EmailingList/MailingListItem/MailingListItem';
import ModalMembers from '~/components/Admin/Emailing/ModalMembers/ModalMembers';
import { type DashboardMailingList_query } from '~relay/DashboardMailingList_query.graphql';
import NoMailingList from '~/components/Admin/Emailing/EmailingList/NoMailingList/NoMailingList';

export const MAILING_LIST_PAGINATION = 30;

type Props = {|
  relay: RelayPaginationProp,
  query: DashboardMailingList_query,
|};

type HeaderProps = {|
  showModalDelete: boolean => void,
|};

const DashboardHeader = ({ showModalDelete }: HeaderProps) => {
  const { selectedRows, rowsCount } = usePickableList();
  const intl = useIntl();

  return (
    <React.Fragment>
      {selectedRows.length > 0 ? (
        <React.Fragment>
          <p>
            {selectedRows.length}{' '}
            <FormattedMessage
              id="campaign-selected"
              values={{
                num: selectedRows.length,
              }}
            />
          </p>

          <S.ButtonDelete type="button" onClick={() => showModalDelete(true)}>
            {intl.formatMessage({ id: 'admin.global.delete' })}
          </S.ButtonDelete>
        </React.Fragment>
      ) : (
        <React.Fragment>
          <p>
            {rowsCount}{' '}
            <FormattedMessage
              id="global-mailing-list"
              values={{
                num: rowsCount,
              }}
            />
          </p>
        </React.Fragment>
      )}
    </React.Fragment>
  );
};

export const DashboardMailingList = ({ query, relay }: Props) => {
  const { mailingLists } = query;
  const { selectedRows } = usePickableList();
  const { parameters, dispatch, status } = useDashboardMailingListContext();
  const [isModalDeleteOpen, showModalDelete] = React.useState<boolean>(false);
  const [mailingListSelected, setMailingListSelected] = React.useState<?string>(null);
  const intl = useIntl();
  const hasMailingLists = mailingLists?.totalCount > 0;

  return (
    <>
      <S.Header>
        <ClearableInput
          id="search"
          name="search"
          type="text"
          icon={<i className="cap cap-magnifier" />}
          disabled={!hasMailingLists}
          onClear={() => {
            if (parameters.filters.term !== null) {
              dispatch({ type: 'CLEAR_TERM' });
            }
          }}
          initialValue={parameters.filters.term}
          onSubmit={term => {
            if (term === '' && parameters.filters.term !== null) {
              dispatch({ type: 'CLEAR_TERM' });
            } else if (term !== '' && parameters.filters.term !== term) {
              dispatch({ type: 'SEARCH_TERM', payload: term });
            }
          }}
          placeholder={intl.formatMessage({ id: 'global.menu.search' })}
        />
      </S.Header>

      <PickableList
        isLoading={status === 'loading'}
        useInfiniteScroll={hasMailingLists}
        onScrollToBottom={() => {
          relay.loadMore(MAILING_LIST_PAGINATION);
        }}
        hasMore={mailingLists?.pageInfo.hasNextPage}
        loader={<EmailingLoader key="loader" />}>
        <S.DashboardMailingListHeader isSelectable={hasMailingLists} disabled={!hasMailingLists}>
          <DashboardHeader showModalDelete={showModalDelete} />
        </S.DashboardMailingListHeader>

        <PickableList.Body>
          {hasMailingLists ? (
            mailingLists?.edges
              ?.filter(Boolean)
              .map(edge => edge.node)
              .filter(Boolean)
              .map(mailingList => (
                <MailingListItem
                  key={mailingList.id}
                  rowId={mailingList.id}
                  mailingList={mailingList}
                  selected={selectedRows.includes(mailingList.id)}
                  setMailingListSelected={setMailingListSelected}
                />
              ))
          ) : (
            <NoMailingList />
          )}
        </PickableList.Body>
      </PickableList>

      <ModalConfirmDelete show={isModalDeleteOpen} onClose={() => showModalDelete(false)} />

      {mailingListSelected && (
        <ModalMembers
          show={!!mailingListSelected}
          onClose={() => setMailingListSelected(null)}
          mailingList={mailingLists?.edges
            ?.filter(Boolean)
            .map(edge => edge.node)
            .filter(Boolean)
            .find(m => m.id === mailingListSelected)}
        />
      )}
    </>
  );
};

export default createPaginationContainer(
  DashboardMailingList,
  {
    query: graphql`
      fragment DashboardMailingList_query on Query
        @argumentDefinitions(
          count: { type: "Int" }
          cursor: { type: "String" }
          term: { type: "String", defaultValue: null }
        ) {
        mailingLists(first: $count, after: $cursor, term: $term)
          @connection(key: "DashboardMailingList_mailingLists", filters: ["term"]) {
          totalCount
          pageInfo {
            hasNextPage
          }
          edges {
            cursor
            node {
              id
              ...MailingListItem_mailingList
              ...ModalMembers_mailingList
            }
          }
        }
      }
    `,
  },
  {
    direction: 'forward',
    /*
     * Based on node_modules/react-relay/ReactRelayPaginationContainer.js.flow, when I ask something
     * in the pageInfo node, it forces me to include everything (e.g hasPrevPage, startCursor and
     * endCursor) but I only need `hasNextPage`
     * $FlowFixMe
     * */
    getConnectionFromProps(props: Props) {
      return props.query.mailingLists;
    },
    getFragmentVariables(prevVars) {
      return {
        ...prevVars,
      };
    },
    getVariables(props: Props, { count, cursor }, fragmentVariables) {
      return {
        ...fragmentVariables,
        count,
        cursor,
      };
    },
    query: graphql`
      query DashboardMailingListPaginatedQuery($count: Int, $cursor: String, $term: String) {
        ...DashboardMailingList_query @arguments(count: $count, cursor: $cursor, term: $term)
      }
    `,
  },
);