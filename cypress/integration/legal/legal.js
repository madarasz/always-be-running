import { When, Then } from "cypress-cucumber-preprocessor/steps";

When('I click Privacy and Cookie Policy link', () => {
    cy.get("a[aria-label='learn more about cookies']").invoke('removeAttr', 'target').click()
})