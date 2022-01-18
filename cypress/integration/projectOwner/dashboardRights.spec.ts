/** import { AdminProjectsListPage, AdminDashboardPage } from '~e2e-pages/index'

describe('Project Owner', () => {
  describe('Dashboard rights', () => {
    beforeEach(() => {
      cy.directLoginAs('project_owner')
    })
    it('should allow a project admin to go to the back-office projects list page', () => {
      AdminProjectsListPage.visit()
      AdminProjectsListPage.checkProjectExistence('Projet avec administrateur de projet')
    })
    it('should redirect a project admin when going to the admin dashboard page', () => {
      AdminDashboardPage.visit()
      cy.url().should('not.contain', AdminDashboardPage.path)
      cy.url().should('contain', AdminProjectsListPage.path)
    })
    it('should redirect a project admin when navigating to back office from the main menu', () => {
      cy.visit('/')
      AdminDashboardPage.clickAdminLink()
      cy.url().should('not.contain', AdminDashboardPage.path)
      cy.url().should('contain', AdminProjectsListPage.path)
    })
  })
}) */
