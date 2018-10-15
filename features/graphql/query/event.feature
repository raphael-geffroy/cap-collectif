@event
Feature: Events

Scenario: GraphQL client wants to list events
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      events(first: 5) {
        totalCount
        edges {
          node {
            id
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
     "data":{
        "events":{
           "totalCount":10,
           "edges":[
              {
                 "node":{
                    "id":"event1"
                 }
              },
              {
                 "node":{
                    "id":"event10"
                 }
              },
              {
                 "node":{
                    "id":"event2"
                 }
              },
              {
                 "node":{
                    "id":"event3"
                 }
              },
              {
                 "node":{
                    "id":"event4"
                 }
              }
           ]
        }
     }
  }
  """

Scenario: GraphQL client wants to list passed events
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      events(first: 5, time: FUTURE) {
        totalCount
        edges {
          node {
            id
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
{
   "data":{
      "events":{
         "totalCount":4,
         "edges":[
            {
               "node":{
                  "id":"event8"
               }
            },
            {
               "node":{
                  "id":"event10"
               }
            },
            {
               "node":{
                  "id":"event7"
               }
            },
            {
               "node":{
                  "id":"event9"
               }
            }
         ]
      }
   }
}
  """

Scenario: GraphQL client wants to list current and future events
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "{
      events(first: 5, time: PASSED) {
        totalCount
        edges {
          node {
            id
          }
        }
      }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
     "data":{
        "events":{
           "totalCount":2,
           "edges":[
              {
                 "node":{
                    "id":"event2"
                 }
              },
              {
                 "node":{
                    "id":"event5"
                 }
              }
           ]
        }
     }
  }
  """

@read-only
Scenario: GraphQL client wants to list event in project
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "query getEventsByProject ($projectId: ID!, $first: Int){
      events(projects: $projectId, first: $first) {
      totalCount
        edges {
          node {
            id
          }
        }
      }
    }",
    "variables": {
      "projectId": "project1",
      "first": 5
    }
  }
  """
  Then the JSON response should match:
  """
  {
     "data":{
        "events":{
           "totalCount":3,
           "edges":[
              {
                 "node":{
                    "id":"event1"
                 }
              },
              {
                 "node":{
                    "id":"event2"
                 }
              },
              {
                 "node":{
                    "id":"event3"
                 }
              }
           ]
        }
     }
  }
  """

@read-only
Scenario: GraphQL client wants to list events with theme2
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "query getEventsByTheme ($themeId: ID!, $first: Int){
      events(themes: $themeId, first: $first) {
      totalCount
        edges {
          node {
            id
          }
        }
      }
    }",
    "variables": {
      "themeId": "theme2",
      "first": 5
    }
  }
  """
  Then the JSON response should match:
  """
  {
     "data":{
        "events":{
           "totalCount":2,
           "edges":[
              {
                 "node":{
                    "id":"event1"
                 }
              },
              {
                 "node":{
                    "id":"event2"
                 }
              }
           ]
        }
     }
  }
  """

@read-only
Scenario: GraphQL client wants to list events from a term
  Given I am logged in to graphql as admin
  And I send a GraphQL POST request:
  """
  {
    "query": "query getEventsByTerm ($term: String!, $first: Int){
      events(term: $term, first: $first) {
      totalCount
        edges {
          node {
            id
          }
        }
      }
    }",
    "variables": {
      "term": "registrations",
      "first": 5
    }
  }
  """
  Then the JSON response should match:
  """
  {
     "data":{
        "events":{
           "totalCount":2,
           "edges":[
              {
                 "node":{
                    "id":"event1"
                 }
              },
              {
                 "node":{
                    "id":"event3"
                 }
              }
           ]
        }
     }
  }
  """
