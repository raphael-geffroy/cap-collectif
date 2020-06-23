/* eslint-dev jest*/
const ProjectContributorsQuery = /* GraphQL */ `
  query ProjectContributors(
    $projectId: ID!
    $userTypeId: ID
    $stepId: ID
    $isVip: Boolean
    $orderBy: UserOrder
  ) {
    project: node(id: $projectId) {
      id
      ... on Project {
        contributors(
          userType: $userTypeId
          step: $stepId
          vip: $isVip
          first: 5
          orderBy: $orderBy
        ) {
          totalCount
          edges {
            node {
              id
              userType {
                id
              }
              vip
            }
          }
        }
      }
    }
  }
`;

describe('Internal.projects.contributors', () => {
  it('fetches contributors filtered by project, step, user type, vip', async () => {
    await expect(
      graphql(
        ProjectContributorsQuery,
        {
          projectId: 'UHJvamVjdDpwcm9qZWN0Ng==',
          stepId: 'Q29sbGVjdFN0ZXA6Y29sbGVjdHN0ZXAx',
          isVip: true,
          userTypeId: 1,
        },
        'internal_admin',
      ),
    ).resolves.toMatchSnapshot();
  });

  it('fetches contributors filtered by project, step', async () => {
    await expect(
      graphql(
        ProjectContributorsQuery,
        {
          projectId: 'UHJvamVjdDpwcm9qZWN0Ng==',
          stepId: 'Q29sbGVjdFN0ZXA6Y29sbGVjdHN0ZXAx',
        },
        'internal_admin',
      ),
    ).resolves.toMatchSnapshot();
  });

  it('fetches contributors sorted by their activities desc', async () => {
    await expect(
      graphql(
        ProjectContributorsQuery,
        {
          projectId: 'UHJvamVjdDpwcm9qZWN0Ng==',
          stepId: 'Q29sbGVjdFN0ZXA6Y29sbGVjdHN0ZXAx',
          orderBy: { field: 'ACTIVITY', direction: 'DESC' },
        },
        'internal_admin',
      ),
    ).resolves.toMatchSnapshot();
  });

  it('fetches contributors sorted by their activities asc', async () => {
    await expect(
      graphql(
        ProjectContributorsQuery,
        {
          projectId: 'UHJvamVjdDpwcm9qZWN0Ng==',
          stepId: 'Q29sbGVjdFN0ZXA6Y29sbGVjdHN0ZXAx',
          orderBy: { field: 'ACTIVITY', direction: 'ASC' },
        },
        'internal_admin',
      ),
    ).resolves.toMatchSnapshot();
  });
});
