import type { FC } from 'react';
import { useForm, FormProvider } from "react-hook-form";
import {
    Button,
    ButtonQuickAction,
    CapUIIcon,
    CapUIModalSize,
    Heading,
    Modal,
} from '@cap-collectif/ui';
import { IntlShape, useIntl } from 'react-intl';
import FormConfiguration, { FormValues } from './FormConfiguration';
import { graphql, useFragment } from 'react-relay';
import type { ModalOpenIDConfiguration_ssoConfiguration$key } from '@relay/ModalOpenIDConfiguration_ssoConfiguration.graphql';
import { mutationErrorToast } from '@utils/mutation-error-toast';
import UpdateOauth2SSOConfigurationMutation from '@mutations/UpdateOauth2SSOConfigurationMutation';
import CreateOauth2SSOConfigurationMutation from '@mutations/CreateOauth2SSOConfigurationMutation';

type ModalOpenIDConfigurationProps = {
    readonly ssoConfiguration: ModalOpenIDConfiguration_ssoConfiguration$key | null
    readonly ssoConnectionName: string
    readonly isEditing?: boolean
}

const FRAGMENT = graphql`
    fragment ModalOpenIDConfiguration_ssoConfiguration on Oauth2SSOConfiguration {
        id
        name
        secret
        enabled
        clientId
        logoutUrl
        profileUrl
        userInfoUrl
        accessTokenUrl
        authorizationUrl
        redirectUri
    }
`;

const onSubmit = (data: FormValues, hide: () => void, intl: IntlShape, reset: () => void, ssoConnectionName: string, isEditing: boolean, ssoId: string | undefined) => {
    const input = {
        name: data.name,
        secret: data.secret,
        enabled: true,
        clientId: data.clientId,
        logoutUrl: data.logoutUrl,
        profileUrl: data.profileUrl,
        userInfoUrl: data.userInfoUrl,
        accessTokenUrl: data.accessTokenUrl,
        authorizationUrl: data.authorizationUrl,
    }

    if(isEditing && ssoId) {
        UpdateOauth2SSOConfigurationMutation.commit({
            input: {
                id: ssoId,
                ...input,
            },
        }).catch(() => {
            mutationErrorToast(intl)
        });
    } else {
        CreateOauth2SSOConfigurationMutation.commit({
            input,
            connections: [ssoConnectionName]
        }).catch(() => {
            mutationErrorToast(intl)
        });
    }

    hide();
    reset();
}

const ModalOpenIDConfiguration: FC<ModalOpenIDConfigurationProps> = ({ ssoConfiguration: ssoConfigurationFragment, isEditing, ssoConnectionName }) => {
    const intl = useIntl();
    const ssoConfiguration = useFragment(FRAGMENT, ssoConfigurationFragment);
    const methods = useForm({
        mode: 'onChange',
        defaultValues: ssoConfiguration && isEditing ? {
            name: ssoConfiguration.name,
            secret: ssoConfiguration.secret,
            enabled: ssoConfiguration.enabled,
            clientId: ssoConfiguration.clientId,
            logoutUrl: ssoConfiguration.logoutUrl,
            profileUrl: ssoConfiguration.profileUrl,
            userInfoUrl: ssoConfiguration.userInfoUrl,
            accessTokenUrl: ssoConfiguration.accessTokenUrl,
            authorizationUrl: ssoConfiguration.authorizationUrl,
            redirectUri: ssoConfiguration.redirectUri,
        } : undefined
    });

    return (
        <Modal
            disclosure={
                isEditing ?
                    <ButtonQuickAction
                        variantColor="blue"
                        icon={CapUIIcon.Pencil}
                        label={intl.formatMessage({ id: 'action_edit' })}
                    /> : <Button variantColor="primary" variant="secondary" variantSize="medium">
                    {intl.formatMessage({ id: 'global.add' })}
                </Button>
            }
            onClose={methods.reset}
            ariaLabel={intl.formatMessage({ id: 'setup-openId' })}
            size={CapUIModalSize.Lg}>
            {({ hide }) => (
                <>

                    <Modal.Header>
                        <Modal.Header.Label>
                            {intl.formatMessage({ id: isEditing ? 'edit-authentication-method' : 'add-authentication-method' })}
                        </Modal.Header.Label>
                        <Heading>
                            {intl.formatMessage({ id: 'setup-openId' })}
                        </Heading>
                    </Modal.Header>
                    <Modal.Body>
                        <FormProvider {...methods} >
                            <FormConfiguration />
                        </FormProvider>
                    </Modal.Body>
                    <Modal.Footer>
                        <Button
                            variant="secondary"
                            variantColor="primary"
                            variantSize="medium"
                            onClick={hide}>
                            {intl.formatMessage({ id: 'cancel' })}
                        </Button>
                        <Button
                            variant="primary"
                            variantColor="primary"
                            variantSize="medium"
                            loading={methods.formState.isSubmitting}
                            onClick={e => {
                                methods.handleSubmit((data: FormValues) => onSubmit(data, hide, intl, methods.reset, ssoConnectionName, isEditing || false, ssoConfiguration?.id || undefined))(e);
                            }}>
                            {intl.formatMessage({ id: isEditing ? 'global.save' :  'action_enable' })}
                        </Button>
                    </Modal.Footer>
                </>
            )}
        </Modal>
    );
};

export default ModalOpenIDConfiguration;
