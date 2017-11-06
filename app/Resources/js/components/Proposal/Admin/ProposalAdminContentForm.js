// @flow
import React, { Component } from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import type { IntlShape } from 'react-intl';
import { connect } from 'react-redux';
import { reduxForm, Field, FieldArray } from 'redux-form';
import { createFragmentContainer, graphql } from 'react-relay';
import { ButtonToolbar, Button } from 'react-bootstrap';
import Fetcher from '../../../services/Fetcher';
import ChangeProposalContentMutation from '../../../mutations/ChangeProposalContentMutation';
import component from '../../Form/Field';
import select from '../../Form/Select';
import ProposalMediaResponse from '../Page/ProposalMediaResponse';
import AlertAdminForm from '../../Alert/AlertAdminForm';
import type { ProposalAdminContentForm_proposal } from './__generated__/ProposalAdminContentForm_proposal.graphql';
import type { GlobalState, Dispatch, FeatureToggles } from '../../../types';

type FormValues = Object;
type DefaultProps = void;
type PassedProps = {
  proposal: ProposalAdminContentForm_proposal,
};
type Props = {
  proposal: ProposalAdminContentForm_proposal,
  themes: Array<Object>,
  features: FeatureToggles,
  handleSubmit: () => void,
  intl: IntlShape,
  isSuperAdmin: boolean,
  submitSucceeded: boolean,
  submitFailed: boolean,
  pristine: boolean,
  invalid: boolean,
  valid: boolean,
  submitting: boolean,
};
type State = void;

const formName = 'proposal-admin-edit';

const onSubmit = (values: FormValues, dispatch: Dispatch, props: Props) => {
  // Only used for the user view
  delete values.addressText;

  // We must remove Files to upload from variables and put them in uploadables
  const uploadables = {};
  if (values.media instanceof File) {
    // User wants to upload a new media
    uploadables.media = values.media;
  }
  delete values.media;

  values.responses = values.responses.filter(res => {
    if (!res.medias) {
      // We only send value responses
      return true;
    }
    for (const media of res.medias) {
      if (media instanceof File) {
        uploadables[`responses_${res.question}`] = media;
      }
    }
    return false;
  });

  // Only super admin can edit author
  if (!props.isSuperAdmin) {
    delete values.author;
  }

  const variables = {
    input: { ...values, id: props.proposal.id },
  };

  return ChangeProposalContentMutation.commit(variables, uploadables);
};

const validate = (values: FormValues, { proposal, features }: Props) => {
  const errors = {};
  const form = proposal.form;

  if (!values.title || values.title.length <= 2) {
    errors.title = 'proposal.constraints.title';
  }
  if (values.summary && (values.summary.length > 140 || values.summary.length < 2)) {
    errors.summary = 'proposal.constraints.summary';
  }
  if (!values.body || values.body.length <= 2) {
    errors.body = 'proposal.constraints.body';
  }
  if (form.usingAddress && !values.address) {
    errors.addressText = 'proposal.constraints.address';
  }
  if (
    form.categories.length &&
    form.usingCategories &&
    form.categoryMandatory &&
    !values.category
  ) {
    errors.category = 'proposal.constraints.category';
  }
  if (features.districts && form.usingDistrict && form.districtMandatory && !values.district) {
    errors.theme = 'proposal.constraints.theme';
  }
  if (features.themes && form.usingThemes && form.themeMandatory && !values.theme) {
    errors.theme = 'proposal.constraints.theme';
  }
  form.questions.map(field => {
    if (field.required) {
      const response = values.responses.filter(res => res && res.question.id === field.id)[0];
      if (!response) {
        errors['responses[1].value'] = 'proposal.constraints.field_mandatory';
      }
    }
  });
  return errors;
};

export class ProposalAdminContentForm extends Component<Props, State> {
  static defaultProps: DefaultProps;
  render() {
    const {
      pristine,
      invalid,
      valid,
      submitSucceeded,
      submitFailed,
      proposal,
      features,
      submitting,
      isSuperAdmin,
      themes,
      handleSubmit,
    } = this.props;
    const form = proposal.form;
    const categories = proposal.form.categories;
    const optional = (
      <span className="excerpt">
        <FormattedMessage id="global.form.optional" />
      </span>
    );

    return (
      <div className="box box-primary container">
        <form onSubmit={handleSubmit}>
          <div className="box-header">
            <h3 className="box-title">
              <FormattedMessage id="proposal.admin.glimpse" />
            </h3>
            <a
              className="pull-right link"
              target="_blank"
              rel="noopener noreferrer"
              href="https://aide.cap-collectif.com/article/86-editer-une-proposition-dune-etape-de-depot#contenu">
              <i className="fa fa-info-circle" /> Aide
            </a>
          </div>
          <div className="box-content box-content__content-form">
            <Field
              name="title"
              component={component}
              type="text"
              id="proposal_title"
              label={<FormattedMessage id="proposal.title" />}
            />
            <Field
              name="summary"
              component={component}
              type="textarea"
              id="proposal_summary"
              label={
                <span>
                  <FormattedMessage id="proposal.summary" />
                  {optional}
                </span>
              }
            />
            <Field
              name="author"
              label="Auteur"
              disabled={!isSuperAdmin}
              id="proposal-admin-author"
              labelClassName="control-label"
              inputClassName="fake-inputClassName"
              component={select}
              clearable={false}
              autoload
              loadOptions={terms =>
                Fetcher.postToJson(`/users/search`, { terms }).then(res => ({
                  options: res.users
                    .map(u => ({
                      value: u.id,
                      label: u.displayName,
                    }))
                    .concat([
                      {
                        value: proposal.author.id,
                        label: proposal.author.displayName,
                      },
                    ]),
                }))}
            />
            {features.themes &&
              form.usingThemes && (
                <Field
                  name="theme"
                  id="proposal_theme"
                  type="select"
                  component={component}
                  label={
                    <span>
                      <FormattedMessage id="proposal.theme" />
                      {!form.themeMandatory && optional}
                    </span>
                  }>
                  <FormattedMessage id="proposal.select.theme">
                    {message => <option value="">{message}</option>}
                  </FormattedMessage>
                  {themes.map(theme => (
                    <option key={theme.id} value={theme.id}>
                      {theme.title}
                    </option>
                  ))}
                </Field>
              )}
            {categories.length > 0 &&
              form.usingCategories && (
                <Field
                  id="proposal_category"
                  type="select"
                  name="category"
                  component={component}
                  label={
                    <span>
                      <FormattedMessage id="proposal.category" />
                      {!form.categoryMandatory && optional}
                    </span>
                  }>
                  <FormattedMessage id="proposal.select.category">
                    {message => <option value="">{message}</option>}
                  </FormattedMessage>
                  {categories.map(category => (
                    <option key={category.id} value={category.id}>
                      {category.name}
                    </option>
                  ))}
                </Field>
              )}
            {features.districts &&
              form.usingDistrict &&
              form.districts.length > 0 && (
                <Field
                  id="proposal_district"
                  type="select"
                  name="district"
                  component={component}
                  label={
                    <span>
                      <FormattedMessage id="proposal.district" />
                      {!form.districtMandatory && optional}
                    </span>
                  }>
                  <FormattedMessage id="proposal.select.district">
                    {message => <option value="">{message}</option>}
                  </FormattedMessage>
                  {form.districts.map(district => (
                    <option key={district.id} value={district.id}>
                      {district.name}
                    </option>
                  ))}
                </Field>
              )}
            {form.usingAddress && (
              <Field
                id="proposal_address"
                component={component}
                type="address"
                name="addressText"
                formName={formName}
                label={<FormattedMessage id="proposal.map.form.field" />}
                placeholder="proposal.map.form.placeholder"
              />
            )}
            <Field
              id="proposal_body"
              type="editor"
              name="body"
              component={component}
              label={<FormattedMessage id="proposal.body" />}
            />
            <FieldArray
              name="responses"
              component={({ fields }) => (
                <div>
                  {fields.map((field, index) => {
                    const response = this.props.proposal.responses.filter(
                      res => res && res.question.id === field.id,
                    )[0];
                    return (
                      <div key={index}>
                        <Field
                          key={field.id}
                          id={field.id}
                          name={`responses.${index}.${field.type !== 'medias'
                            ? 'value'
                            : 'medias'}`}
                          type={field.type}
                          component={component}
                          label={field.title}
                        />
                        {response &&
                          response.medias &&
                          response.medias.length && (
                            <ProposalMediaResponse medias={response.medias} />
                          )}
                      </div>
                    );
                  })}
                </div>
              )}
              fields={form.questions}
            />
            <Field
              id="proposal_media"
              name="media"
              component={component}
              type="image"
              image={proposal && proposal.media ? proposal.media.url : null}
              label={
                <span>
                  <FormattedMessage id="proposal.media" />
                  {optional}
                </span>
              }
            />
            <ButtonToolbar style={{ marginBottom: 10 }} className="box-content__toolbar">
              <Button type="submit" bsStyle="primary" disabled={pristine || invalid || submitting}>
                <FormattedMessage id={submitting ? 'global.loading' : 'global.save'} />
              </Button>
              <AlertAdminForm
                valid={valid}
                invalid={invalid}
                submitSucceeded={submitSucceeded}
                submitFailded={submitFailed}
                submitting={submitting}
              />
            </ButtonToolbar>
          </div>
        </form>
      </div>
    );
  }
}

const form = reduxForm({
  onSubmit,
  // onSubmitSuccess,
  validate,
  form: formName,
})(ProposalAdminContentForm);

const mapStateToProps = (state: GlobalState, { proposal }: PassedProps) => ({
  isSuperAdmin: !!(state.user.user && state.user.user.roles.includes('ROLE_SUPER_ADMIN')),
  features: state.default.features,
  themes: state.default.themes,
  initialValues: {
    title: proposal.title,
    body: proposal.body,
    summary: proposal.summary,
    author: proposal.author.id,
    theme: state.default.features.themes ? (proposal.theme ? proposal.theme.id : null) : undefined,
    category: proposal.category ? proposal.category.id : null,
    district: state.default.features.districts
      ? proposal.district ? proposal.district.id : null
      : undefined,
    address: proposal.address,
    media: null,
    responses: proposal.form.questions.map(field => {
      const response = proposal.responses.filter(res => res && res.question.id === field.id)[0];
      if (response) {
        if (response.value) {
          return {
            question: parseInt(field.id, 10),
            value: response.value,
          };
        }
        return {
          question: parseInt(field.id, 10),
          medias: response.medias,
        };
      }
      if (field.type === 'medias') {
        return { question: parseInt(field.id, 10), medias: [] };
      }
      return { question: parseInt(field.id, 10), value: null };
    }),
    addressText: proposal.address && JSON.parse(proposal.address)[0].formatted_address,
  },
});

const container = connect(mapStateToProps)(injectIntl(form));
export default createFragmentContainer(
  container,
  graphql`
    fragment ProposalAdminContentForm_proposal on Proposal {
      id
      title
      body
      summary
      responses {
        question {
          id
        }
        ... on ValueResponse {
          value
        }
        ... on MediaResponse {
          medias {
            id
            name
            size
            url
          }
        }
      }
      media {
        id
        url
      }
      form {
        districts {
          id
          name
        }
        categories {
          id
          name
        }
        questions {
          id
          title
          type
          position
          private
          required
        }
        usingDistrict
        districtMandatory
        districtHelpText
        usingThemes
        themeMandatory
        usingCategories
        categoryMandatory
        categoryHelpText
        usingAddress
        titleHelpText
        descriptionHelpText
      }
      author {
        id
        displayName
      }
      theme {
        id
      }
      category {
        id
      }
      address
      district {
        id
      }
    }
  `,
);
