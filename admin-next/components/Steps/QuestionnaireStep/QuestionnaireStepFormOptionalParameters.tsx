import React from 'react';
import { Accordion, CapUIAccordionColor, FormLabel } from '@cap-collectif/ui';
import { useIntl } from 'react-intl';
import { useFormContext } from 'react-hook-form';
import { FieldInput, FormControl } from '@cap-collectif/form';
import TextEditor from '../../Form/TextEditor/TextEditor';

type Props = {
    defaultLocale: string,
    selectedLocale: string,
    isEditing: boolean,
};

const QuestionnaireStepFormOptionalParameters: React.FC<Props> = ({
    defaultLocale,
    selectedLocale,
    isEditing,
}) => {
    const intl = useIntl();

    const { control } = useFormContext();

    return (
        <Accordion color={CapUIAccordionColor.Transparent}>
            <Accordion.Item id={intl.formatMessage({ id: 'optional-settings' })}>
                <Accordion.Button>
                    {intl.formatMessage({ id: 'optional-settings' })}
                </Accordion.Button>
                <Accordion.Panel>
                    <FormControl name="metaDescription" control={control}>
                        <FormLabel
                            htmlFor="metaDescription"
                            label={intl.formatMessage({
                                id: 'global.meta.description',
                            })}
                        />
                        <FieldInput
                            id="metaDescription"
                            name="metaDescription"
                            control={control}
                            type="textarea"
                        />
                    </FormControl>

                    <TextEditor
                        name="footer"
                        label={intl.formatMessage({ id: 'global.footer' })}
                        required
                        platformLanguage={defaultLocale}
                        selectedLanguage={selectedLocale}
                        buttonLabels={{
                            submit: isEditing
                                ? intl.formatMessage({ id: 'global.edit' })
                                : intl.formatMessage({ id: 'global.add' }),
                        }}
                    />
                    <TextEditor
                        name="customCode"
                        required
                        label={intl.formatMessage({ id: 'admin.customcode' })}
                        platformLanguage={defaultLocale}
                        selectedLanguage={selectedLocale}
                        buttonLabels={{
                            submit: isEditing
                                ? intl.formatMessage({ id: 'global.edit' })
                                : intl.formatMessage({ id: 'global.add' }),
                        }}
                    />
                </Accordion.Panel>
            </Accordion.Item>
        </Accordion>
    );
};

export default QuestionnaireStepFormOptionalParameters;
