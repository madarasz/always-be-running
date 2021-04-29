import { Then } from "cypress-cucumber-preprocessor/steps";

Then('I turn checkbox {string} {word}', (checkboxID, state) => {
    if (state == 'ON') {
        cy.get(`#${checkboxID}`).check()
    } else {
        cy.get(`#${checkboxID}`).uncheck()
    }
})