import { Then, When } from "cypress-cucumber-preprocessor/steps";

Then('I turn checkbox {string} {word}', (checkboxID, state) => {
    if (state == 'ON') {
        cy.get(`#${checkboxID}`).check()
    } else {
        cy.get(`#${checkboxID}`).uncheck()
    }
})

Then('I turn checkbox name {string} {word}', (checkboxName, state) => {
    if (state == 'ON') {
        cy.get(`input[name='${checkboxName}']`).check()
    } else {
        cy.get(`input[name='${checkboxName}']`).uncheck()
    }
})

When('I select {string} in {string} selector', (option, selectId) => {
    cy.get(`#${selectId}`).select(option)
})

Then('select {string} should have {string} selected', (selectId, option) => {
    cy.get(`#${selectId} option:selected`).should('have.text', option)
})