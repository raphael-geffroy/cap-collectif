import { NextPage } from 'next';
import { PageProps } from '../types';
import React, { Suspense } from 'react';
import Layout from '../components/Layout/Layout';
import Loader from '~ui/FeedbacksIndicators/Loader';
import { useIntl } from 'react-intl';
import IdentificationCodesContent from '../components/IdentificationCodes/IdentificationCodesContent';
import withPageAuthRequired from '../utils/withPageAuthRequired';

const IdentificationCodes: NextPage<PageProps> = () => {
    const intl = useIntl();
    return (
        <Layout navTitle={intl.formatMessage({ id: 'secured-participation' })}>
            <Suspense fallback={<Loader />}>
                <IdentificationCodesContent />
            </Suspense>
        </Layout>
    );
};

export const getServerSideProps = withPageAuthRequired;

export default IdentificationCodes;