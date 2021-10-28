// @flow
import * as React from 'react';
import { connect } from 'react-redux';
import { FormattedMessage, useIntl } from 'react-intl';
// TODO https://github.com/cap-collectif/platform/issues/7774
// eslint-disable-next-line no-restricted-imports
import { ListGroup } from 'react-bootstrap';
import {
  DragDropContext,
  Droppable,
  Draggable,
  type DropResult,
  type DraggableProvided,
  type DroppableProvided,
} from 'react-beautiful-dnd';
import { arrayMove, Field, change, arrayRemove } from 'redux-form';
import toggle from '~/components/Form/Toggle';
import InputRequirement from '~/components/Ui/Form/InputRequirement';
import type { RequirementType } from '~relay/UpdateProjectAlphaMutation.graphql';
import { RequirementDragItem, CheckboxPlaceholder } from './ProjectAdminStepForm.style';
import type { Dispatch, Uuid } from '~/types';

export type Requirement = {|
  type: RequirementType | string,
  checked?: boolean,
  label?: ?string,
  id?: ?string,
  uniqueId?: ?string,
|};

type Props = {|
  ...ReduxFormFieldArrayProps,
  requirements: Array<Requirement>,
  dispatch: Dispatch,
  formName: string,
  meta?: {| error: ?string |},
  onInputCheck: (value: boolean, field: string, requirement: Requirement) => void,
  onInputChange: (value: string, field: string, requirement: Requirement) => void,
  onInputDelete: (index: number) => void,
|};

export const getUId = (): Uuid =>
  `_${Math.random()
    .toString(36)
    .substr(2, 9)}`;

const requirementFactory = (
  type: RequirementType,
  checked: boolean,
  label: string,
  id?: ?string,
): Requirement => ({ type, checked, label, id, uniqueId: getUId() });

export const formatRequirements = (requirements: Array<Requirement>) =>
  requirements
    .filter(r => r.checked !== false)
    .map<Requirement>(r => ({
      ...r,
      uniqueId: undefined,
      checked: undefined,
      label: r.type === 'CHECKBOX' ? r.label : null,
    }));

export const doesStepSupportRequirements = (step: {
  __typename: string,
  requirements?: ?Array<Requirement>,
}): boolean => {
  return !(
    step.__typename !== 'CollectStep' &&
    step.__typename !== 'SelectionStep' &&
    step.__typename !== 'ConsultationStep' &&
    step.__typename !== 'QuestionnaireStep'
  );
};

export function createRequirements(step: {
  __typename: string,
  requirements?: ?Array<Requirement>,
}): Array<Requirement> {
  const requirements = [];

  if (!doesStepSupportRequirements(step)) {
    return requirements;
  }

  const initialRequirements = step.requirements || [];
  if (!initialRequirements.some((r: Requirement) => r.type === 'FIRSTNAME'))
    requirements.push(requirementFactory('FIRSTNAME', false, 'form.label_firstname', null));
  if (!initialRequirements.some((r: Requirement) => r.type === 'LASTNAME'))
    requirements.push(requirementFactory('LASTNAME', false, 'global.name', null));
  if (!initialRequirements.some((r: Requirement) => r.type === 'PHONE'))
    requirements.push(requirementFactory('PHONE', false, 'filter.label_phone', null));
  if (!initialRequirements.some((r: Requirement) => r.type === 'DATE_OF_BIRTH'))
    requirements.push(requirementFactory('DATE_OF_BIRTH', false, 'form.label_date_of_birth', null));
  if (!initialRequirements.some((r: Requirement) => r.type === 'POSTAL_ADDRESS'))
    requirements.push(
      requirementFactory('POSTAL_ADDRESS', false, 'admin.fields.event.address', null),
    );
  if (!initialRequirements.some((r: Requirement) => r.type === 'IDENTIFICATION_CODE'))
    requirements.push(
      requirementFactory('IDENTIFICATION_CODE', false, 'identification_code', null),
    );
  initialRequirements.forEach((requirement: Requirement) => {
    switch (requirement.type) {
      case 'FIRSTNAME':
        requirements.push(
          requirementFactory('FIRSTNAME', true, 'form.label_firstname', requirement.id),
        );
        break;
      case 'LASTNAME':
        requirements.push(requirementFactory('LASTNAME', true, 'global.name', requirement.id));
        break;
      case 'PHONE':
        requirements.push(requirementFactory('PHONE', true, 'filter.label_phone', requirement.id));
        break;
      case 'DATE_OF_BIRTH':
        requirements.push(
          requirementFactory('DATE_OF_BIRTH', true, 'form.label_date_of_birth', requirement.id),
        );
        break;
      case 'POSTAL_ADDRESS':
        requirements.push(
          requirementFactory('POSTAL_ADDRESS', true, 'admin.fields.event.address', requirement.id),
        );
        break;
      case 'CHECKBOX':
        requirements.push(
          requirementFactory('CHECKBOX', true, requirement?.label || '', requirement.id),
        );
        break;
      case 'IDENTIFICATION_CODE':
        requirements.push(
          requirementFactory('IDENTIFICATION_CODE', true, 'identification_code', requirement.id),
        );
        break;
      default:
    }
  });
  return requirements;
}

export function StepRequirementsList({
  dispatch,
  formName,
  fields,
  requirements,
  onInputChange,
  onInputCheck,
  onInputDelete,
}: Props) {
  const intl = useIntl();
  const onDragEnd = (result: DropResult) => {
    // dropped outside the list
    if (!result.destination) {
      return;
    }

    dispatch(arrayMove(formName, 'requirements', result.source.index, result.destination.index));
  };

  return (
    <ListGroup>
      <DragDropContext onDragEnd={onDragEnd}>
        <Droppable droppableId="droppableRequirement">
          {(provided: DroppableProvided) => (
            <div ref={provided.innerRef}>
              {fields.map((field: string, index: number) => {
                const requirement = requirements[index];
                if (!requirement) return;
                const isToggle = requirement.type !== 'CHECKBOX';
                const id = `requirement.${requirement.id || requirement.uniqueId || ''}`;
                return (
                  <Draggable key={id} draggableId={id} index={index}>
                    {(providedDraggable: DraggableProvided) => (
                      <div
                        ref={providedDraggable.innerRef}
                        {...providedDraggable.draggableProps}
                        {...providedDraggable.dragHandleProps}>
                        <RequirementDragItem key={index}>
                          <i className="cap cap-android-menu" />
                          {isToggle ? (
                            <Field
                              id={`requirement-${index}`}
                              name={field}
                              component={toggle}
                              props={{
                                input: {
                                  value: requirement.checked,
                                  onChange: () => {
                                    onInputCheck(!requirement.checked, field, requirements[index]);
                                  },
                                },
                              }}
                              label={<FormattedMessage id={requirement.label || ''} />}
                            />
                          ) : (
                            <>
                              <CheckboxPlaceholder>
                                <i className="fa fa-check" />
                              </CheckboxPlaceholder>
                              <Field
                                name={field}
                                component={InputRequirement}
                                props={{
                                  placeholder: intl.formatMessage({ id: 'enter-label' }),
                                  onChange: (value: string) => {
                                    onInputChange(value, field, requirements[index]);
                                  },
                                  onDelete: () => {
                                    onInputDelete(index);
                                  },
                                  initialValue: requirement.label,
                                }}
                              />
                            </>
                          )}
                        </RequirementDragItem>
                      </div>
                    )}
                  </Draggable>
                );
              })}
              {provided.placeholder}
            </div>
          )}
        </Droppable>
      </DragDropContext>
    </ListGroup>
  );
}

const mapDispatchToProps = (dispatch: Dispatch, props: Props) => ({
  onInputChange: (value: string, field: string, requirement: Requirement) => {
    dispatch(change(props.formName, field, { ...requirement, label: value }));
  },
  onInputCheck: (value: boolean, field: string, requirement: Requirement) => {
    dispatch(change(props.formName, field, { ...requirement, checked: value }));
  },
  onInputDelete: (index: number) => {
    dispatch(arrayRemove(props.formName, 'requirements', index));
  },
  dispatch,
});

export default connect<any, any, _, _, _, _>(null, mapDispatchToProps)(StepRequirementsList);
