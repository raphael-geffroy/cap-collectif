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
        "_links": {
          "self": { "href": "@string@.startsWith('/api/syntheses/')" },
          "elements": { "href": "@string@.startsWith('/api/syntheses/').endsWith('/elements')" }
        },
        "_embedded": {
          "elements": [
            {
              "id": @string@,
              "title": @string@,
              "_links": {
                "self": { "href": "@string@.startsWith('/api/syntheses/').contains('/elements/')" },
                "divide": { "href": "@string@.startsWith('/api/syntheses/').contains('/elements/').endsWith('/divisions')" },
                "history": { "href": "@string@.startsWith('/api/syntheses/').contains('/elements/').endsWith('/history')" }
              }
            }
          ]
        }
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
      "_links": {
        "self": { "href": "/api/syntheses/42" },
        "elements": { "href": "/api/syntheses/42/elements" }
      },
      "_embedded": {
        "elements": [
          {
            "id": "43",
            "title": "Je suis un élément",
            "_links": {
              "self": { "href": "/api/syntheses/42/elements/43" },
              "divide": { "href": "/api/syntheses/42/elements/43/divisions" },
              "history": { "href": "/api/syntheses/42/elements/43/history" }
            }
          }
        ]
      }
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
    And the JSON response should match:
    """
    {
      "id": @string@,
      "enabled": true,
      "_links": {
        "self": { "href": "@string@.startsWith('/api/syntheses/')" },
        "elements": { "href": "@string@.startsWith('/api/syntheses/').endsWith('/elements')" }
      }
    }
    """

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
      "enabled": false
    }
    """
    Then the JSON response status code should be 200
    And the JSON response should match:
    """
    {
      "id": "42",
      "enabled": false,
      "consultation_step": {
        "slug": "collecte-des-avis",
        "step_type": "consultation"
      },
      "_links": {
        "self": { "href": "/api/syntheses/42" },
        "elements": { "href": "/api/syntheses/42/elements" }
      },
      "_embedded": {
          "elements": [
            {
              "id": "43",
              "title": "Je suis un élément",
              "_links": {
                "self": { "href": "/api/syntheses/42/elements/43" },
                "divide": { "href": "/api/syntheses/42/elements/43/divisions" },
                "history": { "href": "/api/syntheses/42/elements/43/history" }
              }
            }
          ]
        }
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
        "notation": 4,
        "_links": {
          "self": { "href": "/api/syntheses/42/elements/43" },
          "divide": { "href": "/api/syntheses/42/elements/43/divisions" },
          "history": { "href": "/api/syntheses/42/elements/43/history" }
        }
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
      "notation": 4,
      "_links": {
        "self": { "href": "/api/syntheses/42/elements/43" },
        "divide": { "href": "/api/syntheses/42/elements/43/divisions" },
        "history": { "href": "/api/syntheses/42/elements/43/history" }
      }
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
      "notation": 5,
      "_links": {
        "self": { "href": "@string@.startsWith('/api/syntheses/42/elements/')" },
        "divide": { "href": "@string@.startsWith('/api/syntheses/42/elements/').endsWith('/divisions')" },
        "history": { "href": "@string@.startsWith('/api/syntheses/42/elements/').endsWith('/history')" }
      }
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
      "notation": 2,
      "_links": {
        "self": { "href": "/api/syntheses/42/elements/43" },
        "divide": { "href": "/api/syntheses/42/elements/43/divisions" },
        "history": { "href": "/api/syntheses/42/elements/43/history" }
      }
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
        "version": 2,
        "sentences": [
          " a mis à jour l'élément 43"
        ]
      },
      {
        "id": @integer@,
        "action": "create",
        "logged_at": "@string@.isDateTime()",
        "version": 1,
        "sentences": [
          " a créé l'élément 43"
        ]
      }
    ]
    """

  @database
  Scenario: After creating an element, there should be a 'create' log
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
  Scenario: After updating an element, there should be an 'update' log
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
  Scenario: After changing an element's parent, there should be a 'move' log
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
  Scenario: After publishing an element, there should be a 'publish' log
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
  Scenario: After unpublishing an element, there should be an 'unpublish' log
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
  Scenario: After archiving an element, there should be an 'archive' log
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
  Scenario: After noting an element, there should be a 'note' log
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

  @database
  Scenario: After dividing an element, there should be a 'divide' log
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