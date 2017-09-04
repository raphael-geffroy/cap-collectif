@district
Feature: District

  @database @dev
  Scenario: GraphQL client wants to trash a proposal
    Given I am logged in to graphql as admin
    And I send a GraphQL POST request:
    """
    {
      "query": "mutation ($input: ChangeDistrictInput!) {
        changeDistrict(input: $input) {
          district {
            name
            geojson
          }
        }
      }",
      "variables": {
        "input": {
          "name": "New name",
          "displayedOnMap": false,
          "districtId": "1",
          "geojson": null
        }
      }
    }
    """
    Then the JSON response should match:
    """
    {
      "data": {
        "changeDistrict": {
          "district": {
            "name": "New name",
            "displayedOnMap": false,
            "geojson": null
          }
        }
      }
    }
    """
