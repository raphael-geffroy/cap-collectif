@project
Feature: Project

  Scenario: Can not sort or filter if feature projects_form is disabled
    Given I visited "projects page"
    Then I should not see "capco_app_search_project"

  @javascript @elasticsearch
  Scenario: Project can be sorted by published date
    Given feature "projects_form" is enabled
    And I visited "projects page"
    And I wait 1 seconds
    And I select "Date de publication" from "capco_app_search_project_sort"
    And I wait 1 seconds
    Then "Projet vide" should be before "Croissance, innovation, disruption" for selector ".thumbnail--custom .project__preview__title a"

  @javascript @elasticsearch
  Scenario: Project can be sorted by contributions number
    Given feature "projects_form" is enabled
    And I visited "projects page"
    And I select "Nombre de contributions" from "capco_app_search_project_sort"
    And I wait 1 seconds
    Then "Croissance, innovation, disruption" should be before "Projet vide" for selector ".thumbnail--custom .project__preview__title a"

  @javascript
  Scenario: Project can be filtered by theme
    Given feature "themes" is enabled
    And feature "projects_form" is enabled
    And I visited "projects page"
    And I wait 1 seconds
    Then I should see 7 ".thumbnail--custom" elements
    And I select "Transport" from "capco_app_search_project_theme"
    And I wait 1 seconds
    Then I should see 6 ".thumbnail--custom" elements
    And I should see "Stratégie technologique de l'Etat et services publics"
    And I should see "Projet vide"
    And I should not see "Croissance, innovation, disruption"

  @javascript
  Scenario: Project can be filtered by theme and sorted by contributions number at the same time
    Given feature "themes" is enabled
    And feature "projects_form" is enabled
    And I visited "projects page"
    And I select "Transport" from "capco_app_search_project_theme"
    And I wait 1 seconds
    And I select "Nombre de contributions" from "capco_app_search_project_sort"
    And I wait 1 seconds
    Then I should see 6 ".thumbnail--custom" elements
    And I should see "Stratégie technologique de l'Etat et services publics"
    And I should see "Projet vide"
    And I should not see "Croissance, innovation, disruption"
    And "Stratégie technologique de l'Etat et services publics" should be before "Projet vide" for selector ".thumbnail--custom .project__preview__title a"

  @javascript
  Scenario: Project can be filtered by title
    Given feature "projects_form" is enabled
    And I visited "projects page"
    When I fill in the following:
      | capco_app_search_project_term | innovation |
    And I click the ".filter__search .btn" element
    And I wait 1 seconds
    Then I should see 1 ".thumbnail--custom" elements
    And I should see "Croissance, innovation, disruption"
    And I should not see "Stratégie technologique de l'Etat et services publics"
    And I should not see "Projet vide"

  @javascript
  Scenario: Project should contain allowed types only
    Given I am logged in as user
    And I visited "consultation page" with:
      | projectSlug   | strategie-technologique-de-l-etat-et-services-publics |
      | stepSlug      | collecte-des-avis-pour-une-meilleur-strategie         |
    Then I should see 4 "Opinion nav item" on current page

  Scenario: Presentation step should display correct number of element
    Given feature "calendar" is enabled
    And feature "blog" is enabled
    And I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    And I follow "Présentation"
    Then I should see 2 ".media--news" elements
    And I should see 2 ".event" elements

  Scenario: Events menu for project should display correct number of events
    Given feature "calendar" is enabled
    And I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    And I follow "events_link"
    And I should see 3 ".event" elements

  Scenario: Posts menu for project should display correct number of posts
    Given feature "blog" is enabled
    And I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    And I follow "posts_link"
    And I should see 5 ".media--news" elements

  Scenario: Project header should display correct number of votes
    Given I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    Then I should see "8 votes"

  @javascript
  Scenario: Project header should display correct number of contributions
    Given I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    Then I should see "164 contributions"
    And I hover over the "#contributions-counter-pill" element
    And I wait 1 seconds
    And I should see "32 propositions"
    And I should see "100 arguments"
    And I should see "32 sources"

  Scenario: Project header should display correct number of participants
    Given I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    Then I should see "19 participants"

  Scenario: Can not have access to download if export is disabled
    Given I visited "consultation page" with:
      | projectSlug   | strategie-technologique-de-l-etat-et-services-publics |
      | stepSlug      | collecte-des-avis-pour-une-meilleur-strategie         |
    Then I should not see "Exporter"

  @javascript
  Scenario: Can not download a project if export is disabled
    Given I visited "home page"
    When I try to download "projets/strategie-technologique-de-l-etat-et-services-publics/projet/collecte-des-avis-pour-une-meilleur-strategie/download/xls"
    Then I should see "Désolé, cette page n'existe pas (404)"

  @javascript
  Scenario: Can not access trash if feature is disabled
    Given I am logged in as user
    And I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    Then I should not see "Corbeille"

  @javascript
  Scenario: Can not access trash if not logged in
    Given feature "project_trash" is enabled
    And I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    And I should see "Corbeille"
    When I click the "#trash-link" element
    Then I should see "Vous devez être connecté pour réaliser cette action."

  @javascript
  Scenario: Project trash display correct numbers of elements
    Given feature "project_trash" is enabled
    And I am logged in as user
    And I visited "consultation page" with:
      | projectSlug | croissance-innovation-disruption |
      | stepSlug    | collecte-des-avis                |
    When I follow "Corbeille"
    Then I should see 100 ".opinion__list .opinion" elements
    And I should see "100" in the "span.badge" element

  Scenario: I should not see opinion types menu when only one type is allowed
    Given I visited "consultation page" with:
      | projectSlug | projet-vide |
      | stepSlug    | projet      |
    Then I should see 0 ".project__nav" on current page
