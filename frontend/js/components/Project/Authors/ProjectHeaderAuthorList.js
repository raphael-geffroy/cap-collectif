// @flow
import * as React from 'react';
import { graphql, useFragment } from 'react-relay';
import { useDisclosure } from '@liinkiing/react-hooks';
import type { ProjectHeaderAuthorList_project$key } from '~relay/ProjectHeaderAuthorList_project.graphql';
import ProjectHeaderLayout from '~ui/Project/ProjectHeader';
import Avatar from '~ds/Avatar/Avatar';
import ProjectHeaderAuthorsModal from '~/components/Project/Authors/ProjectHeaderAuthorsModal';
import useFeatureFlag from '~/utils/hooks/useFeatureFlag';

const FRAGMENT = graphql`
  fragment ProjectHeaderAuthorList_project on Project {
    ...ProjectHeaderAuthorsModal_project
    authors {
      id
      username
      url
      avatarUrl
    }
  }
`;
export type Props = {|
  +project: ProjectHeaderAuthorList_project$key,
|};
const ProjectHeaderAuthorList = ({ project }: Props): React.Node => {
  const profilesToggle = useFeatureFlag('profiles');
  const { isOpen, onOpen, onClose } = useDisclosure(false);
  const data = useFragment(FRAGMENT, project);
  if (data.authors && data.authors.length > 0) {
    if (data.authors.length === 1) {
      return (
        <ProjectHeaderLayout.Authors
          active={profilesToggle}
          style={{ cursor: profilesToggle ? 'pointer' : 'default' }}
          onClick={() => (profilesToggle ? window.open(data.authors[0].url, '_self') : null)}>
          <Avatar
            key={data.authors[0].id}
            name={data.authors[0].username}
            src={data.authors[0].avatarUrl}
          />
        </ProjectHeaderLayout.Authors>
      );
    }
    return (
      <>
        <ProjectHeaderAuthorsModal project={data} onClose={onClose} show={isOpen} />
        <ProjectHeaderLayout.Authors
          active={profilesToggle}
          style={{ cursor: profilesToggle ? 'pointer' : 'default' }}
          onClick={onOpen}>
          {data.authors.map(author => (
            <Avatar key={author.id} name={author.username} src={author.avatarUrl} />
          ))}
        </ProjectHeaderLayout.Authors>
      </>
    );
  }
  return null;
};

export default ProjectHeaderAuthorList;