Feature: Synthesis
  As an API client
  I want to manage syntheses

  Scenario: API client wants to list syntheses
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses"
    Then the JSON response should match:
    """
    [
      {
        "id": @string@,
        "enabled": @boolean@,
        "consultation_step": {
          "slug": @string@,
          "step_type": "consultation"
        },
        "elements": [
          {
            "id": @string@,
            "title": @string@
          }
        ]
      },
      @...@
    ]
    """

  Scenario: Non admin API client wants to list syntheses
    Given I am logged in to api as user
    And I send a GET request to "/api/syntheses"
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to list syntheses
    Given I send a GET request to "/api/syntheses"
    Then the JSON response status code should be 401

  Scenario: API client wants to get a synthesis
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42"
    Then the JSON response should match:
    """
    {
      "id": "42",
      "enabled": true,
      "consultation_step": {
        "slug": "collecte-des-avis",
        "step_type": "consultation"
      },
      "elements": [
        {
          "id": "43",
          "title": "Je suis un élément"
        }
      ]
    }
    """

  Scenario: Non admin API client wants to get a synthesis
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42"
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to get a synthesis
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42"
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to create a synthesis
    Given I am logged in to api as admin
    And I send a POST request to "/api/syntheses" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 201

  Scenario: Non admin API client wants to create a synthesis
    Given I am logged in to api as user
    And I send a POST request to "/api/syntheses" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to create a synthesis
    Given I send a POST request to "/api/syntheses" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to update a synthesis
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "id": "42",
      "enabled": true,
      "consultation_step": {
        "slug": "collecte-des-avis",
        "step_type": "consultation"
      },
      "elements": [
        {
          "id": "43",
          "title": "Je suis un élément"
        }
      ]
    }
    """

  Scenario: Non admin API client wants to update a synthesis
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to update a synthesis
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42" with json:
    """
    {
      "enabled": true
    }
    """
    Then the JSON response status code should be 401

  Scenario: API client wants to get synthesis elements
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements"
    Then the JSON response should match:
    """
    [
      {
        "id": "43",
        "enabled": true,
        "archived": false,
        "title": "Je suis un élément",
        "body": "blabla",
        "notation": 4
      }
    ]
    """

  Scenario: Non admin API client wants to get synthesis elements
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements"
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to get synthesis elements
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements"
    Then the JSON response status code should be 401

  Scenario: API client wants to get a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/43"
    Then the JSON response should match:
    """
    {
      "id": "43",
      "enabled": true,
      "archived": false,
      "title": "Je suis un élément",
      "body": "blabla",
      "notation": 4
    }
    """

  Scenario: Non admin API client wants to get a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/43"
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to get a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/43"
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to create a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5
    }
    """
    Then the JSON response status code should be 201
    And the JSON response should match:
    """
    {
      "id": @string@,
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "enabled": true,
      "archived": false,
      "notation": 5
    }
    """


  Scenario: Non admin API client wants to create a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5
    }
    """
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to create a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to update a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "enabled": true,
      "notation": 2
    }
    """
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "id": "43",
      "title": "Je suis un élément",
      "body": "blabla",
      "enabled": true,
      "archived": false,
      "notation": 2
    }
    """

  Scenario: Non admin API client wants to update a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "enabled": true,
      "notation": 2
    }
    """
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to update a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "enabled": true,
      "notation": 2
    }
    """
    Then the JSON response status code should be 401

  @database
  Scenario: API client wants to divide a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements/43/divisions" with json:
    """
    {
      "elements": [
        {
          "title": "Coucou, je suis un élément.",
          "body": "blabla",
          "notation": 5
        },
        {
          "title": "Coucou, je suis un autre élément.",
          "body": "blabla",
          "notation": 3
        },
        {
          "title": "Coucou, je suis le dernier élément.",
          "body": "blabla",
          "notation": 2
        }
      ]
    }
    """
    Then the JSON response status code should be 201

  Scenario: Non admin API client wants to divide a synthesis element
    Given I am logged in to api as user
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements/43/divisions" with json:
    """
    {
      "elements": [
        {
          "title": "Coucou, je suis un élément.",
          "body": "blabla",
          "notation": 5
        },
        {
          "title": "Coucou, je suis un autre élément.",
          "body": "blabla",
          "notation": 3
        },
        {
          "title": "Coucou, je suis le dernier élément.",
          "body": "blabla",
          "notation": 2
        }
      ]
    }
    """
    Then the JSON response status code should be 403

  Scenario: Anonymous API client wants to divide a synthesis element
    Given there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements/43/divisions" with json:
    """
    {
      "elements": [
        {
          "title": "Coucou, je suis un élément.",
          "body": "blabla",
          "notation": 5
        },
        {
          "title": "Coucou, je suis un autre élément.",
          "body": "blabla",
          "notation": 3
        },
        {
          "title": "Coucou, je suis le dernier élément.",
          "body": "blabla",
          "notation": 2
        }
      ]
    }
    """
    Then the JSON response status code should be 401

  Scenario: API client wants to get a synthesis element history
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a GET request to "/api/syntheses/42/elements/43/history"
    Then the JSON response should match:
    """
    [
      {
        "id": @integer@,
        "action": "update",
        "logged_at": "@string@.isDateTime()",
        "object_id": "43",
        "object_class": "Capco\\AppBundle\\Entity\\Synthesis\\SynthesisElement",
        "version": 2,
        "data": {
          "title": "Je suis un élément"
        },
        "sentences": [
          " a mis à jour l'élément 43"
        ]
      },
      {
        "id": @integer@,
        "action": "create",
        "logged_at": "@string@.isDateTime()",
        "object_id": "43",
        "object_class": "Capco\\AppBundle\\Entity\\Synthesis\\SynthesisElement",
        "version": 1,
        "data": {
          "enabled": true,
          "archived": false,
          "title": "Je suis un nouvel élément",
          "body": "blabla",
          "notation": 4
        },
        "sentences": [
          " a créé l'élément 43"
        ]
      }
    ]
    """

  @database
  Scenario: API client wants to have a 'create' log when creating a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements" with json:
    """
    {
      "title": "Coucou, je suis un élément.",
      "body": "blabla",
      "notation": 5
    }
    """
    Then there should be a created log on response element with username "admin"

  @database
  Scenario: API client wants to have an 'update' log when updating a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "title": "Coucou, je suis un élément avec un titre modifié."
    }
    """
    Then there should be a log on element 43 with sentence "admin a mis à jour l'élément 43"

  @database
  Scenario: API client wants to have an 'move' log when changing the parent of a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 47                          |
      | title    | Coucou, je suis un élément. |
      | body     | blabla                      |
      | notation | 5                           |
      | enabled  | true                        |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "parent": 47
    }
    """
    Then there should be a log on element 43 with sentence "admin a déplacé l'élément 43"

  @database
  Scenario: API client wants to have an 'publish' log when enabling a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I create an element in synthesis 42 with values:
      | id       | 47                          |
      | title    | Coucou, je suis un élément. |
      | body     | blabla                      |
      | notation | 5                           |
      | enabled  | false                       |
    And I send a PUT request to "/api/syntheses/42/elements/47" with json:
    """
    {
      "enabled": true
    }
    """
    Then there should be a log on element 47 with sentence "admin a publié l'élément 47"

  @database
  Scenario: API client wants to have an 'unpublish' log when disabling a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "enabled": false
    }
    """
    Then there should be a log on element 43 with sentence "admin a dépublié l'élément 43"

  @database
  Scenario: API client wants to have an 'archive' log when archiving a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "archived": true
    }
    """
    Then there should be a log on element 43 with sentence "admin a marqué l'élément 43 comme traité"

  @database
  Scenario: API client wants to have an 'note' log when noting a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a PUT request to "/api/syntheses/42/elements/43" with json:
    """
    {
      "notation": 1
    }
    """
    Then there should be a log on element 43 with sentence "admin a modifié la note de l'élément 43"

  Scenario: API client wants to have an 'divide' log when dividing a synthesis element
    Given I am logged in to api as admin
    And there is a synthesis with id "42" and elements:
      | 43 |
    And I send a POST request to "/api/syntheses/42/elements/43/divisions" with json:
    """
    {
      "elements": [
        {
          "title": "Coucou, je suis un élément.",
          "body": "blabla",
          "notation": 5
        },
        {
          "title": "Coucou, je suis un autre élément.",
          "body": "blabla",
          "notation": 3
        },
        {
          "title": "Coucou, je suis le dernier élément.",
          "body": "blabla",
          "notation": 2
        }
      ]
    }
    """
    Then there should be a log on element 43 with sentence "admin a divisé l'élément 43"