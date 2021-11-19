import { commitLocalUpdate } from 'react-relay';
import { Environment, Network, RecordSource, Store } from 'relay-runtime';
import executeFunction from '~/network/executeFunction';

const store = new Store(new RecordSource());
const network = Network.create(executeFunction);

const environment = new Environment({
    network,
    store,
});

const getEnvironment = featureFlags => {
    commitLocalUpdate(environment, storeProxy => {
        const newfeatureFlagsRecords = Object.keys(featureFlags).map(key => {
            const enabled = featureFlags[key];
            const newRecord = storeProxy.create(`client:root:featureFlags:${key}`, 'FeatureFlag');
            newRecord.setValue(enabled, 'enabled');
            newRecord.setValue(key, 'type');
            return newRecord;
        });
        storeProxy.getRoot().setLinkedRecords(newfeatureFlagsRecords, 'featureFlags');
    });

    return environment;
};

export default getEnvironment;