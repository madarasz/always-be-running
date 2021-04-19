import { Given } from "cypress-cucumber-preprocessor/steps";

const url = 'http://localhost:8000/'

Given('I open the {string} page', (pageName) => {
    switch (pageName) {
        case 'Upcoming':
            cy.visit(url)
            break
    }
})