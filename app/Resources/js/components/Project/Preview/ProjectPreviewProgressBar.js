// @flow
import * as React from 'react';
import { ProgressBar } from 'react-bootstrap';
// import { FormattedMessage } from 'react-intl';

// import ProjectPreviewProgressBarItem from './ProjectPreviewProgressBarItem';

type Props = {
  project: Object,
  actualStep: Object,
};

export class ProjectPreviewProgressBar extends React.Component<Props> {
  getStyle = (stepStatus: string) => {
    if (stepStatus === 'open') {
      return 'success';
    }
  };

  getClass = (stepStatus: string) => {
    if (stepStatus === 'future') {
      return 'progress_future-step';
    } else if (stepStatus === 'closed') {
      return 'progress_closed-step';
    }
  };

  getLabel = (step: Object) => {
    if (step.timeless === true) {
      return 'Participation en continu';
    } else if (step.status === 'open') {
      return 'En cours';
    } else if (step.status === 'future') {
      return 'À venir';
    } else if (step.status === 'closed') {
      return 'Terminé';
    }

    return '';
  };

  getWidth = (step: Object) => {
    if (step.status === 'closed' || step.status === 'future' || step.timeless === true) {
      return 100;
    } else if (step.status === 'open') {
      return 50;
    }
    return 0;
  };

  render() {
    const { project, actualStep } = this.props;
    const nbSteps = project.steps.length;

    if (nbSteps > 0) {
      return (
        <div className="thumbnail__steps-bar">
          <ProgressBar
            className={this.getClass(actualStep.status)}
            bsStyle={this.getStyle(actualStep.status)}
            now={this.getWidth(actualStep)}
            label={this.getLabel(actualStep)}
          />
        </div>
      );
    }
    return null;
  }
}

export default ProjectPreviewProgressBar;
