import { Then, When } from "cypress-cucumber-preprocessor/steps";

Then('I see {string} element', (elementName) => {
    getElement(elementName).should('be.visible')
})

Then('{string} element exists', (elementName) => {
    getElement(elementName)
})

Then("{string} element doesn't exist", (elementName) => {
    getElement(elementName).should('not.exist')
})

Then("I don't see {string} element", (elementName) => {
    getElement(elementName).should('not.be.visible')
})

Then("element {string} should not exist", (elementName) => {
    getElement(elementName).should('not.exist')
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
        case 'cookie banner':
            return cy.get("div[aria-label='cookieconsent']")
        case 'Privacy and Cookie Policy link':
            return cy.get("a[aria-label='learn more about cookies']")
        case 'Allow cookies button':
            return cy.get("a[aria-label='dismiss cookie message']")
        case 'polite Cookie Policy':
            return cy.get("div.cc-bottom")
        case 'logout button':
            return cy.get("#button-logout")
        case 'tournament results tab':
            return cy.get(".nav-link[href='#tab-results']")
        case 'waiting for conclusion tab':
            return cy.get(".nav-link[href='#tab-to-be-concluded']")
        case 'conclude login warning':
            return cy.get('#warning-conclude')
        case 'results conclude button':
            return cy.get("button[data-target='#concludeModal']")
        case 'support me featured box':
            return cy.get('#featured-support-me')
        case 'results forward button':
            return cy.get('#results-paging-forward')
        case 'results back button':
            return cy.get('#results-paging-back')
        case 'results 50 option':
            return cy.get('#results-options span.control-paging:nth-child(1)')
        case 'results 100 option':
            return cy.get('#results-options span.control-paging:nth-child(2)')
        case 'results all option':
            return cy.get('#results-options span.control-paging:nth-child(3)')
        case 'results flag option':
            return cy.get('#results-options .control-flag')
        case 'results text option':
            return cy.get('#results-options .control-text')
        case 'Profile menu':
            return cy.get('a.nav-link span#nav-profile')
        case 'Profile Edit button':
            return cy.get('#button-edit')
        case 'Profile Save button':
            return cy.get('#button-save')
        case 'results default country label':
            return cy.get('#label-default-country')
        default:
            throw new Error("No element defined for name: " + elementName)
    }
}