/* @flow */
/* eslint-env jest */
import React from 'react';
import { shallow } from 'enzyme';
import { ProjectEventPreview } from './ProjectEventPreview';
import { $refType } from '~/mocks';

const baseEvent = {
  $refType,
  title: 'Lorem ipsum dolor sit amet, consectetur adipiscing.',
  url: '#',
  guestListEnabled: false,
  timeRange: {
    startAt: '2030-03-10 00:00:00',
  },
  googleMapsAddress: {
    json:
      '[{"address_components":[{"long_name":"111","short_name":"111","types":["street_number"]},{"long_name":"Avenue Jean Jaurès","short_name":"Avenue Jean Jaurès","types":["route"]},{"long_name":"Lyon","short_name":"Lyon","types":["locality","political"]},{"long_name":"Rhône","short_name":"Rhône","types":["administrative_area_level_2","political"]},{"long_name":"Auvergne-Rhône-Alpes","short_name":"Auvergne-Rhône-Alpes","types":["administrative_area_level_1","political"]},{"long_name":"France","short_name":"FR","types":["country","political"]},{"long_name":"69007","short_name":"69007","types":["postal_code"]}],"formatted_address":"111 Avenue Jean Jaurès, 69007 Lyon, France","geometry":{"location":{"lat":45.742842,"lng":4.84068000000002},"location_type":"ROOFTOP","viewport":{"south":45.7414930197085,"west":4.839331019708538,"north":45.74419098029149,"east":4.842028980291502}},"place_id":"ChIJHyD85zjq9EcR8Yaae-eQdeQ","plus_code":{"compound_code":"PRVR+47 Lyon, France","global_code":"8FQ6PRVR+47"},"types":["street_address"]}]',
  },
};

const event = {
  basic: {
    ...baseEvent,
  },
  withoutAddress: {
    ...baseEvent,
    googleMapsAddress: null,
  },
  withoutDate: {
    ...baseEvent,
    timeRange: {
      startAt: null,
    },
  },
  withGuestEnabled: {
    ...baseEvent,
    guestListEnabled: true,
  },
};

describe('<ProjectEventPreview />', () => {
  it('should render correctly', () => {
    const wrapper = shallow(<ProjectEventPreview event={event.basic} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should render correctly when no googleMapsAddress', () => {
    const wrapper = shallow(<ProjectEventPreview event={event.withoutAddress} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should render correctly when no date', () => {
    const wrapper = shallow(<ProjectEventPreview event={event.withoutDate} />);
    expect(wrapper).toMatchSnapshot();
  });

  it('should render correctly when guest enabled', () => {
    const wrapper = shallow(<ProjectEventPreview event={event.withGuestEnabled} />);
    expect(wrapper).toMatchSnapshot();
  });
});