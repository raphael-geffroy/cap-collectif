// @flow
import React from 'react';
import { Field } from 'redux-form';
import { OverlayTrigger } from 'react-bootstrap';
import { createFragmentContainer, graphql } from 'react-relay';
import { type IntlShape, FormattedMessage } from 'react-intl';
import select from '~/components/Form/Select';
import renderComponent from '~/components/Form/Field';
import UserListField from '../../Field/UserListField';
import { type Author } from '../Form/ProjectAdminForm';
import ProjectTypeListField from '../../Field/ProjectTypeListField';
import { type ProjectContentAdminForm_project } from '~relay/ProjectContentAdminForm_project.graphql';
import { loadThemeOptions, loadDistrictOptions } from '../Metadata/ProjectMetadataAdminForm';
import Tooltip from '~/components/Utils/Tooltip';
import { ProjectBoxHeader, ProjectSmallInput } from '../Form/ProjectAdminForm.style';

export type FormValues = {|
  title: string,
  authors: Author[],
  opinionTerm: number,
  projectType: string,
  themes: Option[],
  Cover: ?{
    id: string,
    description: ?string,
    name: ?string,
    size: ?string,
    url: ?string,
  },
  metaDescription: ?string,
  video: ?string,
  districts: Option[],
|};

type Props = {|
  ...ReduxFormFormProps,
  project: ?ProjectContentAdminForm_project,
  intl: IntlShape,
|};

export const renderOptionalLabel = (id: string, intl: IntlShape, helpText?: string) => (
  <div>
    {intl.formatMessage({ id })}
    <span className="excerpt inline">
      {intl.formatMessage({ id: 'global.optional' })}{' '}
      {helpText && (
        <OverlayTrigger
          key="top"
          placement="top"
          overlay={
            <Tooltip id="tooltip-top" className="text-left" style={{ wordBreak: 'break-word' }}>
              {intl.formatMessage({ id: helpText })}
            </Tooltip>
          }>
          <i className="fa fa-info-circle" style={{ opacity: '.5' }} />
        </OverlayTrigger>
      )}
    </span>
  </div>
);

export const validate = (props: FormValues) => {
  const { title, authors } = props;
  const errors = {};

  if (!title || title.length < 2) {
    errors.title = 'global.required';
  }

  if (!authors || authors.length <= 0) {
    errors.authors = 'global.required';
  }

  return errors;
};

export const ProjectContentAdminForm = ({ intl }: Props) => (
  <div className="col-md-12">
    <div className="box box-primary container-fluid">
      <ProjectBoxHeader>
        <h4>
          <FormattedMessage id="global.general" />
        </h4>
      </ProjectBoxHeader>
      <div className="box-content">
        <Field
          type="text"
          name="title"
          label={<FormattedMessage id="global.title" />}
          component={renderComponent}
        />
        <UserListField
          id="project-author"
          name="authors"
          clearable
          selectFieldIsObject
          debounce
          autoload={false}
          multi
          placeholder=" "
          labelClassName="control-label"
          inputClassName="fake-inputClassName"
          label={<FormattedMessage id="admin.fields.project.authors" />}
          ariaControls="EventListFilters-filter-author-listbox"
        />

        <ProjectSmallInput>
          <ProjectTypeListField optional placeholder=" " />
        </ProjectSmallInput>
        <div className="row mr-0 ml-0">
          <Field
            id="cover"
            name="Cover"
            component={renderComponent}
            type="image"
            label={renderOptionalLabel('proposal.media', intl)}
          />
        </div>
        <Field
          type="text"
          name="video"
          id="video"
          label={renderOptionalLabel(
            'admin.fields.project.video',
            intl,
            'admin.help.project.video',
          )}
          placeholder="https://"
          component={renderComponent}
        />
        <Field
          selectFieldIsObject
          debounce
          autoload
          labelClassName="control-label"
          inputClassName="fake-inputClassName"
          component={select}
          id="themes"
          name="themes"
          placeholder=" "
          label={renderOptionalLabel('global.themes', intl)}
          role="combobox"
          aria-autocomplete="list"
          aria-haspopup="true"
          loadOptions={loadThemeOptions}
          multi
          clearable
        />
        <Field
          role="combobox"
          aria-autocomplete="list"
          aria-haspopup="true"
          loadOptions={loadDistrictOptions}
          component={select}
          id="districts"
          name="districts"
          clearable
          selectFieldIsObject
          debounce
          autoload
          multi
          placeholder=" "
          labelClassName="control-label"
          inputClassName="fake-inputClassName"
          label={renderOptionalLabel('proposal_form.districts', intl)}
        />
        <Field
          name="metaDescription"
          type="textarea"
          label={renderOptionalLabel('global.meta.description', intl, 'admin.help.metadescription')}
          component={renderComponent}
        />
      </div>
    </div>
  </div>
);

export default createFragmentContainer(ProjectContentAdminForm, {
  project: graphql`
    fragment ProjectContentAdminForm_project on Project {
      id
      title
      metaDescription
      authors {
        value: id
        label: username
      }
      opinionTerm
      type {
        id
      }
      Cover: cover {
        id
        name
        size
        url
      }
      video
      themes {
        value: id
        label: title
      }
      districts {
        edges {
          node {
            value: id
            label: name
          }
        }
      }
    }
  `,
});
