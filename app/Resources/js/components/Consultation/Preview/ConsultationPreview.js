// @flow
import * as React from 'react';
import { createFragmentContainer, graphql } from 'react-relay';
import { Col } from 'react-bootstrap';
import type { ConsultationPreview_consultation } from '~relay/ConsultationPreview_consultation.graphql';
import Card from '../../Ui/Card/Card';
import DefaultProjectImage from '../../Project/Preview/DefaultProjectImage';
import ProjectPreviewCounter from '../../Project/Preview/ProjectPreviewCounter';
import TagsList from '../../Ui/List/TagsList';

type RelayProps = {|
  +consultation: ConsultationPreview_consultation
|}

type Props = {|
  ...RelayProps,
|}

const ConsultationPreviewCover = ({ consultation: { url, title, illustration } }: { consultation: ConsultationPreview_consultation }) => (
  <Card.Cover>
    <a href={url} alt={title}>
      {illustration && illustration.url ? (
        <img src={illustration.url} alt={title} className="img-responsive"/>
      ) : (
        <div className="bg--project"><DefaultProjectImage/></div>
      )}
    </a>
  </Card.Cover>
);

const ConsultationPreviewBody = ({ consultation }: { consultation: ConsultationPreview_consultation }) => {
  const { contributions, url, title, contributors, votesCount } = consultation;
  return (
    <Card.Body>
      <Card.Title>
        <a href={url}>{title}</a>
      </Card.Title>
      <div className="flex-1">
        <TagsList>
            <ProjectPreviewCounter
              value={contributions.totalCount}
              label="project.preview.counters.contributions"
              showZero
              icon="cap-baloon-1"
            />
            <ProjectPreviewCounter
              value={contributors.totalCount}
              label="project.preview.counters.contributors"
              showZero
              icon="cap-user-2-1"
            />
            <ProjectPreviewCounter
              value={votesCount}
              label="project.preview.counters.votes"
              icon="cap-hand-like-2-1"
            />
        </TagsList>
      </div>
    </Card.Body>
  );
};


const ConsultationPreview = ({ consultation }: Props) => {
  const { id } = consultation;
  return (
    <Col xs={12} sm={6} md={4} lg={3} className="d-flex">
      <Card id={id} className="consultation-preview">
        <ConsultationPreviewCover consultation={consultation}/>
        <ConsultationPreviewBody consultation={consultation}/>
      </Card>
    </Col>
  );
};

export default createFragmentContainer(ConsultationPreview, {
  consultation: graphql`
      fragment ConsultationPreview_consultation on Consultation {
          id
          title
          url
          illustration {
              url
          }
          contributions {
              totalCount
          }
          contributors {
              totalCount
          }
          votesCount
      }
  `,
});
