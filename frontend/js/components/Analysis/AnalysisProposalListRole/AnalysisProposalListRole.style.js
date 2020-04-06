// @flow
import styled, { type StyledComponent } from 'styled-components';

export const AVATAR_SIZE = 35;

const AnalysisProposalListRoleContainer: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  display: flex;
  flex-direction: row;
  justify-content: space-between;

  .user-avatar {
    margin: 0;
  }

  svg {
    border: none;
  }

  .wrapper-role {
    min-width: ${AVATAR_SIZE}px;

    &.role-supervisor {
      margin: 0 45px;
    }
  }
`;

export default AnalysisProposalListRoleContainer;