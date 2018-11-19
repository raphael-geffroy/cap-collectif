// @flow
import React from 'react';
import { injectIntl, FormattedMessage, type IntlShape } from 'react-intl';
import { Button, Row, Col, Popover, OverlayTrigger } from 'react-bootstrap';
import { connect, type MapStateToProps } from 'react-redux';
import { reduxForm, Field, formValueSelector, type FormProps } from 'redux-form';
import select from '../../Form/Select';
import type { GlobalState, Dispatch, FeatureToggles, Uuid } from '../../../types';
import config from '../../../config';
import component from '../../Form/Field';
import { changeEventMobileListView } from '../../../redux/modules/event';
import EventListToggleMobileViewBtn from './EventListToggleMobileViewBtn';

type Theme = { id: Uuid, title: string };

type Props = FormProps & {
  themes: Array<Theme>,
  projects: {},
  features: FeatureToggles,
  dispatch: Dispatch,
  theme: ?string,
  project: ?string,
  search: ?string,
  intl: IntlShape,
  addToggleViewButton: ?boolean,
};

const countFilters = (theme: ?string, project: ?string, search: ?string): number => {
  let nbFilter = 0;
  if (theme) {
    nbFilter++;
  }
  if (project) {
    nbFilter++;
  }
  if (config.isMobile && search) {
    nbFilter++;
  }

  return nbFilter;
};

export class EventListFilters extends React.Component<Props> {
  render() {
    const {
      features,
      themes,
      projects,
      theme,
      project,
      search,
      reset,
      intl,
      addToggleViewButton,
      dispatch,
    } = this.props;

    const filters = [];
    const nbFilter = countFilters(theme, project, search);

    if (theme !== null || project !== null) {
      if (nbFilter > 0) {
        filters.push(
          <div className="d-flex justify-content-end">
            <Button className="btn--outline btn-dark-gray" onClick={reset}>
              <FormattedMessage id="reset-filters" />
            </Button>
          </div>,
        );
      }
    }

    if (features.themes) {
      filters.push(
        <Field
          component={select}
          clearable
          id="event-theme"
          name="theme"
          placeholder={intl.formatMessage({ id: 'type-theme' })}
          options={themes.map(th => ({ value: th.id, label: th.title }))}
        />,
      );
    }
    if (features.projects_form) {
      filters.push(
        <Field
          component={select}
          clearable
          id="project"
          name="project"
          placeholder={intl.formatMessage({ id: 'type-project' })}
          options={Object.keys(projects)
            .map(key => projects[key])
            .map(p => ({ value: p.id, label: p.title }))}
        />,
      );
    }

    if (config.isMobile) {
      filters.push(
        <Field
          clearable
          id="event-search-input"
          name="search"
          type="text"
          addonAfter={<i className="cap cap-magnifier" />}
          component={component}
          placeholder={intl.formatMessage({ id: 'proposal-search' })}
          groupClassName="event-search-group pull-right"
        />,
      );
    }

    const popoverBottom = (
      <Popover id="popover-positioned-bottom" className="w-260">
        <div>
          <form>
            {filters.map((filter, index) => (
              <Col key={index} className="mt-5">
                <div>{filter}</div>
              </Col>
            ))}
          </form>
        </div>
      </Popover>
    );

    const filterCount = function() {
      if (nbFilter > 0) {
        return <span className="ml-5"> ({nbFilter}) </span>;
      }
    };
    return (
      <Row className={config.isMobile ? 'mb-10 ml-0' : 'mb-10'}>
        <Col xs={12} md={8} className="pl-0">
          <OverlayTrigger
            trigger="click"
            placement="bottom"
            overlay={popoverBottom}
            className="w-25"
            id="event-list-filters-d">
            <Button className="btn--outline btn-dark-gray" id="event-button-filter">
              <i className="cap-filter-1 mr-5" />
              <FormattedMessage id="link_filters" />
              {filterCount()}
              <i className="cap-arrow-60 ml-5" />
            </Button>
          </OverlayTrigger>
          {config.isMobile && addToggleViewButton && features.display_map ? (
            <EventListToggleMobileViewBtn
              showMapButton
              isMobileListView
              onChange={isListView => {
                dispatch(changeEventMobileListView(isListView));
              }}
            />
          ) : null}
        </Col>
        <Col md={4} smHidden xsHidden>
          <form>
            <Field
              id="event-search-input"
              name="search"
              type="text"
              component={component}
              placeholder={intl.formatMessage({ id: 'proposal-search' })}
              addonAfter={<i className="cap cap-magnifier" />}
              divClassName="event-search-group pull-right w-100"
            />
          </form>
        </Col>
      </Row>
    );
  }
}

const selector = formValueSelector('EventListFilters');

const mapStateToProps: MapStateToProps<*, *, *> = (state: GlobalState) => ({
  features: state.default.features,
  themes: state.default.themes,
  theme: selector(state, 'theme'),
  project: selector(state, 'project'),
  search: selector(state, 'search'),
  projects: state.project.projectsById,
});

const form = reduxForm({
  form: 'EventListFilters',
  destroyOnUnmount: false,
})(EventListFilters);

export default connect(mapStateToProps)(injectIntl(form));
