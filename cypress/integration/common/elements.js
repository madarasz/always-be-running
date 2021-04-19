import { Then, When } from "cypress-cucumber-preprocessor/steps";

Then('I see {string} element', (elementName) => {
    getElement(elementName).should('be.visible')
})

Then("I don't see {string} element", (elementName) => {
    getElement(elementName).should('not.be.visible')
})

Then("I see text {string}", (text) => {
    cy.contains(text).should('be.visible')
})

Then("I don't see text {string}", (text) => {
    cy.contains(text).should('not.be.visible')
})

When("I click on {string} element", (elementName) => {
    getElement(elementName).click()
})

function getElement(elementName) {
    switch(elementName) {
        case 'online flag':
            return cy.get("img[title='online']")
        case 'Canada flag':
            return cy.get("img[title='Canada']")
        case 'text button':
            return cy.get("span.control-text").first()
        case 'flag button':
            return cy.get("span.control-flag").first()
        case 'upcoming forward button':
            return cy.get('#discover-table-controls-forward')
        case 'upcoming all button':
            return cy.get("#discover-table-options span.control-paging:nth-of-type(3)")
        case 'show map button':
            return cy.get('#button-show-map')
        case 'include online checkbox':
            return cy.get('#include-online')
        default:
            throw new Error("No element defined for name: " + elementName)
    }
}