// @flow
import * as React from 'react';
import { type IntlShape, injectIntl, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import { createFragmentContainer, graphql } from 'react-relay';
import { type FormProps, Field, reduxForm, formValueSelector } from 'redux-form';
import component from '../../Form/Field';
import toggle from '../../Form/Toggle';
import type { Dispatch, FeatureToggles, GlobalState } from '../../../types';
import type { EventForm_event } from '~relay/EventForm_event.graphql';
import type { EventForm_query } from '~relay/EventForm_query.graphql';
import UserListField from '../../Admin/Field/UserListField';
import SelectTheme from '../../Utils/SelectTheme';
import SelectProject from '../../Utils/SelectProject';
import CustomPageFields from '../../Admin/Field/CustomPageFields';

type Props = {|
  ...FormProps,
  event: ?EventForm_event,
  query: EventForm_query,
  features: FeatureToggles,
  dispatch: Dispatch,
  intl: IntlShape,
  initialValues: Object,
  currentValues?: ?{},
  autoload: boolean,
  multi: boolean,
|};

export const formName = 'EventForm';

export class EventForm extends React.Component<Props> {
  render() {
    const { features, event, query, currentValues } = this.props;

    return (
      <form className="eventForm">
        <div className="box-header">
          <h3 className="box-title">
            <FormattedMessage id="proposal.admin.general" />
          </h3>
        </div>
        <div className="box-body">
          <Field
            name="title"
            label={
              <div>
                <FormattedMessage id="admin.fields.group.title" />
                <div className="excerpt inline">
                  <FormattedMessage id="global.mandatory" />
                </div>
              </div>
            }
            component={component}
            type="text"
            id="event_title"
          />
          {query.viewer.isAdmin && (
            <UserListField
              clearable={false}
              label={
                <div>
                  <FormattedMessage id="admin.fields.argument_vote.voter" />
                  <div className="excerpt inline">
                    <FormattedMessage id="global.mandatory" />
                  </div>
                </div>
              }
              ariaControls="EventForm-filter-user-listbox"
              inputClassName="fake-inputClassName"
              autoload
              disabled={!query.viewer.isAdmin}
              id="event_author"
              name="author"
              placeholder={null}
              labelClassName={null}
              selectFieldIsObject
            />
          )}
          <Field
            id="event_address"
            component={component}
            type="address"
            name="addressText"
            formName={formName}
            label={<FormattedMessage id="admin.fields.proposal.address" />}
            placeholder="proposal.map.form.placeholder"
          />
          {/* This part is tempory, it will be delete after migration complete */}
          {query.viewer.isSuperAdmin && (
            <div className="mb-5">
              <div>
                {event && event.fullAddress && (
                  <div className="clearfix mb-5">
                    <FormattedMessage id="old-address" />: {event.fullAddress}
                  </div>
                )}
                {event && event.lat && event.lng && (
                  <div className="clearfix mb-5">
                    {' '}
                    <FormattedMessage id="old-latitude" />: &nbsp; {event.lat} / {event.lng}
                  </div>
                )}
                {event &&
                  event.googleMapsAddress &&
                  event.googleMapsAddress.lat &&
                  event.googleMapsAddress.lng && (
                    <div className="clearfix mb-5">
                      {' '}
                      <FormattedMessage id="new-latitude" />
                      :&nbsp; {event.googleMapsAddress.lat} / {event.googleMapsAddress.lng}
                    </div>
                  )}
              </div>
            </div>
          )}
          <Field
            id="event_body"
            type="editor"
            name="body"
            component={component}
            label={
              <div>
                <FormattedMessage id="admin.fields.proposal_form.description" />
                <div className="excerpt inline">
                  <FormattedMessage id="global.mandatory" />
                </div>
              </div>
            }
          />
          <div className="datePickContainer">
            <Field
              timeFormat={false}
              id="event_startAt"
              dateTimeInputProps={{ id: 'event_input_startAt' }}
              component={component}
              type="datetime"
              name="startAt"
              formName={formName}
              label={
                <div>
                  <FormattedMessage id="start-date" />
                  <span className="excerpt inline">
                    <FormattedMessage id="global.mandatory" />
                  </span>
                </div>
              }
              addonAfter={<i className="cap-calendar-2" />}
            />
            <Field
              id="event_endAt"
              dateTimeInputProps={{ id: 'event_input_endAt' }}
              component={component}
              type="datetime"
              className="adminDate"
              name="endAt"
              formName={formName}
              label={<FormattedMessage id="ending-date" />}
              addonAfter={<i className="cap-calendar-2" />}
            />
          </div>
          <Field
            id="event_media"
            name="media"
            label={<FormattedMessage id="admin.fields.proposal.media" />}
            component={component}
            type="image"
          />
        </div>
        <div className="box-header">
          <h3 className="box-title">
            <FormattedMessage id="form.label_category" />
          </h3>
        </div>
        {features.themes && (
          <SelectTheme
            query={query}
            multi
            clearable
            name="themes"
            divId="event_theme"
            label="admin.fields.event.themes"
          />
        )}
        <SelectProject query={query} multi clearable name="projects" label="admin.group.project" />
        <div>
          <div>
            <div className="box-header">
              <h3 className="box-title">
                <FormattedMessage id="proposal_form.admin.settings.options" />
              </h3>
            </div>
            <div className="ml-10 pl-10">
              <Field
                name="guestListEnabled"
                id="event_registrable"
                type="checkbox"
                component={component}
                disabled={!!(currentValues && currentValues.link && currentValues.link !== null)}
                children={<FormattedMessage id="admin.fields.event.registration_enable" />}
              />
            </div>
            <div className="clearfix">
              <Field
                name="link"
                label={<FormattedMessage id="admin.fields.event.link" />}
                component={component}
                placeholder="http://"
                type="text"
                disabled={
                  currentValues &&
                  currentValues.guestListEnabled &&
                  currentValues.guestListEnabled !== null
                    ? currentValues.guestListEnabled
                    : false
                }
                id="event_link"
              />
            </div>
            <div className="ml-10 pl-10">
              <Field
                name="commentable"
                id="event_commentable"
                type="checkbox"
                component={component}
                children={<FormattedMessage id="admin.fields.blog_post.is_commentable" />}
              />
            </div>
          </div>
          {query.viewer.isAdmin && (
            <div>
              <div className="box-header">
                <h3 className="box-title">
                  <FormattedMessage id="admin.fields.page.advanced" />
                </h3>
              </div>
              <CustomPageFields />
              <div className="box-header pt-0">
                <h3 className="box-title">
                  <FormattedMessage id="admin.fields.project.published_at" />
                </h3>
              </div>
              <Field
                name="enabled"
                id="event_enabled"
                type="checkbox"
                component={toggle}
                label={<FormattedMessage id="proposal.state.published" />}
              />
            </div>
          )}
        </div>
      </form>
    );
  }
}

const selector = formValueSelector(formName);

const formContainer = reduxForm({
  form: formName,
})(EventForm);

const mapStateToProps = (state: GlobalState, props: Props) => {
  if (props.event) {
    return {
      features: state.default.features,
      initialValues: {
        id: props.event && props.event.id ? props.event.id : null,
        title: props.event && props.event.title ? props.event.title : null,
        startAt: props.event && props.event.timeRange ? props.event.timeRange.startAt : null,
        endAt: props.event && props.event.timeRange ? props.event.timeRange.endAt : null,
        body: props.event ? props.event.body : null,
        enabled: props.event ? props.event.enabled : null,
        commentable: props.event ? props.event.commentable : null,
        guestListEnabled: props.event ? props.event.guestListEnabled : null,
        link: props.event ? props.event.link : null,
        metadescription: props.event ? props.event.metaDescription : null,
        customcode: props.event ? props.event.customCode : null,
        media: props.event ? props.event.media : null,
        projects: props.event
          ? props.event.projects.map(p => ({
              value: p.id,
              label: p.title,
            }))
          : [],
        themes: props.event
          ? props.event.themes.map(th => ({
              value: th.id,
              label: th.title,
            }))
          : [],
        author:
          props.event && props.event.author
            ? { value: props.event.author.id, label: props.event.author.displayName }
            : null,
        addressText:
          props.event && props.event.googleMapsAddress
            ? props.event.googleMapsAddress.formatted
            : null,
        addressJson:
          props.event && props.event.googleMapsAddress ? props.event.googleMapsAddress.json : null,
      },
      currentValues: selector(state, 'guestListEnabled', 'link'),
    };
  }

  return {
    features: state.default.features,
    currentValues: selector(state, 'guestListEnabled', 'link'),
  };
};

const container = connect(mapStateToProps)(injectIntl(formContainer));

export default createFragmentContainer(container, {
  query: graphql`
    fragment EventForm_query on Query {
      ...SelectTheme_query
      ...SelectProject_query
      viewer {
        isAdmin
        isSuperAdmin
      }
    }
  `,
  event: graphql`
    fragment EventForm_event on Event {
      id
      timeRange {
        startAt
        endAt
      }
      title
      googleMapsAddress {
        formatted
        json
        lat
        lng
      }
      enabled
      body
      commentable
      metaDescription
      customCode
      guestListEnabled
      link
      themes {
        id
        title
      }
      projects {
        id
        title
      }
      media {
        url
        id
      }
      author {
        id
        displayName
      }
      lat
      lng
      fullAddress
    }
  `,
});