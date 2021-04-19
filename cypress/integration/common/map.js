import { Given, Then, When } from "cypress-cucumber-preprocessor/steps"

Given('I set up Google map intercepts', () => {
    cy.intercept(/^https:\/\/maps\.googleapis\.com/).as('googleMaps')
})

When('I display upcoming map', () => {
    cy.get('#button-show-map').click()
})

Then('{word} Google map loads', (mapName) => {
    cy.wait('@googleMaps')
    cy.wait(2000)
    cy.get('#map').scrollIntoView().matchImageSnapshot('map-'+mapName)
})