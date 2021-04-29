import { Given, Then } from "cypress-cucumber-preprocessor/steps";

const url = 'http://localhost:8000/'

Given('I open the {string} page', (pageName) => {
    switch (pageName) {
        case 'Upcoming':
            cy.visit(url)
            break
        case 'Results':
            cy.visit(url+'results')
            break
        case 'Organize':
            cy.visit(url+'organize')
            break
        case 'Admin':
            cy.visit(url+'admin', { failOnStatusCode: false })
            break
    }
})

Then('current url is {string}', (desiredUrl) => {
    cy.url().should('eq', url + desiredUrl)
})