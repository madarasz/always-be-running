import { Given, Then, When } from "cypress-cucumber-preprocessor/steps"

Given('I set up Google map intercepts', () => {
    cy.intercept(/^https:\/\/maps\.googleapis\.com/).as('googleMaps')
})

When('I display upcoming map', () => {
    cy.get('#button-show-map').click()
    cy.wait('@googleMaps')
    cy.wait(2000)
})

Then('Google map loads', () => {
    cy.get('#map').scrollIntoView().matchImageSnapshot()
})