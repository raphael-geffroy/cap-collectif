/* eslint-env jest */
import * as React from 'react';
import ReactTestRenderer from 'react-test-renderer';
import { graphql, useLazyLoadQuery } from 'react-relay';
import { createMockEnvironment, MockPayloadGenerator } from 'relay-test-utils';
import {
    addsSupportForPortals,
    clearSupportForPortals,
    RelaySuspensFragmentTest,
} from 'tests/testUtils';
import ProjectModalCreateProject from './ProjectModalCreateProject';
import type { ProjectModalCreateProjectTestQuery } from '@relay/ProjectModalCreateProjectTestQuery.graphql';

describe('<ProjectModalCreateProject />', () => {
    let environment: any;
    let testComponentTree: any;
    let TestModalCreateProject: any;

    const query = graphql`
        query ProjectModalCreateProjectTestQuery @relay_test_operation {
            ...ProjectModalCreateProject_query
            viewer {
                ...ProjectModalCreateProject_viewer
            }
        }
    `;

    const defaultMockResolvers = {
        Project: () => ({
            id: 'UHJvamVjdDpwcm9qZWN0SWRmMw==',
            title: 'Budget Participatif IdF 3',
            contributions: {
                totalCount: 5,
            },
        }),
        User: () => ({
            id: 'VXNlcjp1c2VyMQ==',
            username: 'lbrunet',
            isAdmin: true,
            isOnlyProjectAdmin: false,
            organizations: null,
        }),
    };

    beforeEach(() => {
        addsSupportForPortals();
        environment = createMockEnvironment();
        const viewerId = 'VXNlcjp1c2VyMQ==';
        const queryVariables = {};

        const TestRenderer = ({ componentProps, queryVariables: variables }) => {
            const data = useLazyLoadQuery<ProjectModalCreateProjectTestQuery>(query, variables);
            if (data) {
                return (
                    <ProjectModalCreateProject
                        query={data}
                        viewer={data.viewer}
                        orderBy="DESC"
                        term=""
                        hasProjects={false}
                        {...componentProps}
                    />
                );
            }

            return null;
        };

        TestModalCreateProject = componentProps => (
            <RelaySuspensFragmentTest environment={environment}>
                <TestRenderer componentProps={componentProps} queryVariables={queryVariables} />
            </RelaySuspensFragmentTest>
        );

        environment.mock.queueOperationResolver(operation =>
            MockPayloadGenerator.generate(operation, defaultMockResolvers),
        );
    });

    afterEach(() => {
        clearSupportForPortals();
    });

    describe('<TestModalCreateProject />', () => {
        it('should render correctly', () => {
            testComponentTree = ReactTestRenderer.create(<TestModalCreateProject />);
            expect(testComponentTree).toMatchSnapshot();
        });
        it('should render modal open', () => {
            testComponentTree = ReactTestRenderer.create(<TestModalCreateProject />);
            const fakeEvent = {};
            testComponentTree.root.findByType('button').props.onClick(fakeEvent);
            expect(testComponentTree).toMatchSnapshot();
        });
    });
});
