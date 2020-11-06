// @flow
import * as React from 'react';
import { createPaginationContainer, graphql, type RelayPaginationProp } from 'react-relay';
import { FormattedMessage, useIntl } from 'react-intl';
import PickableList, { usePickableList } from '~ui/List/PickableList';
import Collapsable from '~ui/Collapsable';
import DropdownSelect from '~ui/DropdownSelect';
import { useDashboardCampaignContext } from './DashboardCampaign.context';
import { ORDER_BY, type CampaignsStateValues } from './DashboardCampaign.reducer';
import * as S from './DashboardCampaign.style';
import InlineSelect from '~ui/InlineSelect';
import ClearableInput from '~ui/Form/Input/ClearableInput';
import CampaignItem from '~/components/Admin/Emailing/EmailingCampaign/CampaignItem/CampaignItem';
import EmailingLoader from '../../EmailingLoader/EmailingLoader';
import ModalConfirmDelete from '~/components/Admin/Emailing/EmailingCampaign/ModalConfirmDelete/ModalConfirmDelete';
import { type DashboardCampaign_query } from '~relay/DashboardCampaign_query.graphql';
import CreateEmailingCampaignMutation from '~/mutations/CreateEmailingCampaignMutation';
import FluxDispatcher from '~/dispatchers/AppDispatcher';
import { TYPE_ALERT, UPDATE_ALERT } from '~/constants/AlertConstants';

export const CAMPAIGN_PAGINATION = 30;

type Props = {|
  relay: RelayPaginationProp,
  query: DashboardCampaign_query,
|};

type HeaderProps = {|
  query: DashboardCampaign_query,
  showModalDelete: boolean => void,
|};

const createCampaign = () => {
  return CreateEmailingCampaignMutation.commit({ input: {} })
    .then(response => {
      if (response.createEmailingCampaign?.error) {
        return FluxDispatcher.dispatch({
          actionType: UPDATE_ALERT,
          alert: {
            type: TYPE_ALERT.ERROR,
            content: 'global.error.server.form',
          },
        });
      }

      if (response.createEmailingCampaign?.emailingCampaign?.id) {
        window.location.replace(
          `/admin/mailingCampaign/edit/${response.createEmailingCampaign.emailingCampaign.id}`,
        );
      }
    })
    .catch(() => {
      return FluxDispatcher.dispatch({
        actionType: UPDATE_ALERT,
        alert: {
          type: TYPE_ALERT.ERROR,
          content: 'global.error.server.form',
        },
      });
    });
};

const DashboardHeader = ({ query, showModalDelete }: HeaderProps) => {
  const { campaigns } = query;
  const { selectedRows, rowsCount } = usePickableList();
  const { dispatch, parameters } = useDashboardCampaignContext();

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
              id="global-campaign"
              values={{
                num: campaigns.totalCount,
              }}
            />
          </p>

          <Collapsable align="right">
            <Collapsable.Button>
              <FormattedMessage id="argument.sort.label" />
            </Collapsable.Button>
            <Collapsable.Element ariaLabel={intl.formatMessage({ id: 'sort-by' })}>
              <DropdownSelect
                shouldOverflow
                value={parameters.sort}
                defaultValue={ORDER_BY.NEWEST}
                onChange={newValue => dispatch({ type: 'CHANGE_SORT', payload: newValue })}
                title={intl.formatMessage({ id: 'sort-by' })}>
                <DropdownSelect.Choice value={ORDER_BY.NEWEST}>
                  {intl.formatMessage({
                    id: 'global.filter_f_last',
                  })}
                </DropdownSelect.Choice>
                <DropdownSelect.Choice value={ORDER_BY.OLDEST}>
                  {intl.formatMessage({
                    id: 'global.filter_f_old',
                  })}
                </DropdownSelect.Choice>
              </DropdownSelect>
            </Collapsable.Element>
          </Collapsable>
        </React.Fragment>
      )}
    </React.Fragment>
  );
};

export const DashboardCampaign = ({ query, relay }: Props) => {
  const { campaigns, campaignsAll, campaignsDraft, campaignsSent, campaignsPlanned } = query;
  const intl = useIntl();
  const { selectedRows } = usePickableList();
  const { parameters, dispatch, status } = useDashboardCampaignContext();
  const hasCampaigns = campaigns.totalCount > 0;

  const [isModalDeleteOpen, showModalDelete] = React.useState<boolean>(false);

  return (
    <>
      <S.Header>
        <InlineSelect
          value={parameters.filters.state}
          onChange={newValue => {
            dispatch({
              type: 'CHANGE_STATE_FILTER',
              payload: ((newValue: any): CampaignsStateValues),
            });
          }}>
          <InlineSelect.Choice value="ALL">
            <FormattedMessage
              id="filter.count.status.all"
              values={{ num: campaignsAll.totalCount }}
            />
          </InlineSelect.Choice>
          <InlineSelect.Choice value="SENT">
            <FormattedMessage
              id="filter.count.status.sent"
              values={{ num: campaignsSent.totalCount }}
            />
          </InlineSelect.Choice>
          <InlineSelect.Choice value="PLANNED">
            <FormattedMessage
              id="filter.count.status.planned"
              values={{ num: campaignsPlanned.totalCount }}
            />
          </InlineSelect.Choice>
          <InlineSelect.Choice value="DRAFT">
            <FormattedMessage
              id="filter.count.status.draft"
              values={{ num: campaignsDraft.totalCount }}
            />
          </InlineSelect.Choice>
        </InlineSelect>

        <div>
          <ClearableInput
            id="search"
            name="search"
            type="text"
            icon={<i className="cap cap-magnifier" />}
            disabled={false}
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

          <S.ButtonCreate type="button" onClick={createCampaign}>
            <FormattedMessage id="create-mail" />
          </S.ButtonCreate>
        </div>
      </S.Header>

      <PickableList
        isLoading={status === 'loading'}
        useInfiniteScroll={hasCampaigns}
        onScrollToBottom={() => {
          relay.loadMore(CAMPAIGN_PAGINATION);
        }}
        hasMore={campaigns?.pageInfo.hasNextPage}
        loader={<EmailingLoader key="loader" />}>
        <S.DashboardCampaignHeader isSelectable={hasCampaigns}>
          <DashboardHeader query={query} showModalDelete={showModalDelete} />
        </S.DashboardCampaignHeader>

        <PickableList.Body>
          {hasCampaigns ? (
            campaigns?.edges
              ?.filter(Boolean)
              .map(edge => edge.node)
              .filter(Boolean)
              .map(campaign => (
                <CampaignItem
                  campaign={campaign}
                  rowId={campaign.id}
                  key={campaign.id}
                  selected={selectedRows.includes(campaign.id)}
                />
              ))
          ) : (
            <p>Pas de campagnes</p>
          )}
        </PickableList.Body>
      </PickableList>

      <ModalConfirmDelete
        show={isModalDeleteOpen}
        onClose={() => showModalDelete(false)}
        campaignsIds={selectedRows}
      />
    </>
  );
};

export default createPaginationContainer(
  DashboardCampaign,
  {
    query: graphql`
      fragment DashboardCampaign_query on Query
        @argumentDefinitions(
          count: { type: "Int" }
          cursor: { type: "String" }
          term: { type: "String", defaultValue: null }
          orderBy: {
            type: EmailingCampaignOrder
            defaultValue: { field: SEND_AT, direction: DESC }
          }
          status: { type: EmailingCampaignStatusFilter, defaultValue: null }
        ) {
        campaigns: emailingCampaigns(
          first: $count
          after: $cursor
          term: $term
          orderBy: $orderBy
          status: $status
        ) @connection(key: "DashboardCampaign_campaigns", filters: ["term", "orderBy", "status"]) {
          totalCount
          pageInfo {
            hasNextPage
          }
          edges {
            cursor
            node {
              id
              status
              ...CampaignItem_campaign
            }
          }
        }
        campaignsAll: emailingCampaigns(status: null) {
          totalCount
        }
        campaignsDraft: emailingCampaigns(status: DRAFT) {
          totalCount
        }
        campaignsSent: emailingCampaigns(status: SENT) {
          totalCount
        }
        campaignsPlanned: emailingCampaigns(status: PLANNED) {
          totalCount
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
      return props.query.campaigns;
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
      query DashboardCampaignPaginatedQuery(
        $count: Int
        $cursor: String
        $term: String
        $orderBy: EmailingCampaignOrder
        $status: EmailingCampaignStatusFilter
      ) {
        ...DashboardCampaign_query
          @arguments(
            count: $count
            cursor: $cursor
            term: $term
            orderBy: $orderBy
            status: $status
          )
      }
    `,
  },
);