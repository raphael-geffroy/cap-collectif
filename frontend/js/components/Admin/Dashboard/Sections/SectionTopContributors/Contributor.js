// @flow
import * as React from 'react';
import { graphql, useFragment } from 'react-relay';
import { useIntl, type IntlShape } from 'react-intl';
import type {
  Contributor_contributor$key,
  PlatformAnalyticsContributorContributionType,
} from '~relay/Contributor_contributor.graphql';
import Flex from '~ui/Primitives/Layout/Flex';
import Text from '~ui/Primitives/Text';
import { headingStyles } from '~ui/Primitives/Heading';
import UserAvatar from '~/components/User/NewUserAvatar';
import { FontWeight, LineHeight } from '~ui/Primitives/constants';

type Props = {|
  +contributor: Contributor_contributor$key,
|};

const FRAGMENT = graphql`
  fragment Contributor_contributor on PlatformAnalyticsTopContributor {
    user {
      username
      ...NewUserAvatar_user
    }
    contributions {
      type
      totalCount
    }
  }
`;

const getContributionWording = (
  type: PlatformAnalyticsContributorContributionType,
  intl: IntlShape,
  count: number,
) => {
  switch (type) {
    case 'PROPOSAL':
    case 'OPINION':
      return intl.formatMessage({ id: 'count-proposal' }, { num: count });
    case 'OPINION_VERSION':
      return intl.formatMessage({ id: 'amendment-count' }, { count });
    case 'REPLY':
      return intl.formatMessage({ id: 'shortcut.answer' }, { num: count });
    case 'SOURCE':
      return intl.formatMessage({ id: 'source-count' }, { count });
    case 'ARGUMENT':
      return intl.formatMessage({ id: 'argument-count' }, { count });
    case 'COMMENT':
      return intl.formatMessage({ id: 'comments-count' }, { count });
    case 'DEBATE_ARGUMENT':
      return intl.formatMessage({ id: 'count-debate-argument' }, { num: count });
    default:
      throw Error();
  }
};

const Contributor = ({ contributor: contributorFragment }: Props): React.Node => {
  const contributor = useFragment(FRAGMENT, contributorFragment);
  const intl = useIntl();
  const { user, contributions } = contributor;

  return (
    <Flex direction="column" align="center" spacing={1}>
      <UserAvatar user={user} size="xl" mb={2} />

      <Text color="blue.900" {...headingStyles.h5} fontWeight={FontWeight.Semibold}>
        {user.username}
      </Text>

      {contributions.map(contribution => (
        <Text
          color="gray.900"
          key={contribution.type}
          fontSize={1}
          lineHeight={LineHeight.Normal}
          capitalize>
          {getContributionWording(contribution.type, intl, contribution.totalCount)}
        </Text>
      ))}
    </Flex>
  );
};

export default Contributor;
