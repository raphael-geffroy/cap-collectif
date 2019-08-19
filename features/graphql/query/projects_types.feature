@projects @read-only
Feature: Projects

Scenario: GraphQL client wants to list projects types 
  Given I send a GraphQL POST request:
  """
  {
    "query": "{
        projectTypes {
          id
          title
        }
    }"
  }
  """
  Then the JSON response should match:
  """
  {
    "data":{
      "projectTypes":[
        {"id":"1","title":"project.types.callForProject"},
        {"id":"2","title":"project.types.consultation"},
        {"id":"3","title":"project.types.interpellation"},
        {"id":"4","title":"project.types.participatoryBudgeting"},
        {"id":"5","title":"project.types.petition"},
        {"id":"6","title":"project.types.publicInquiry"},
        {"id":"7","title":"project.types.questionnaire"},
        {"id":"8","title":"project.types.suggestionBox"}
      ]
    }
  }
  """