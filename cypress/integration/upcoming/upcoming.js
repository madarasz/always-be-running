import { Given, Then } from "cypress-cucumber-preprocessor/steps";

Given('upcoming data is mocked', () => {
    cy.intercept('GET', '/api/tournaments/upcoming', { fixture: 'upcoming.json' })
    // mock date
    const now = new Date(2021, 4, 18).getTime()
    //cy.clock(now)
})

Then('I see {int} upcoming tournaments', (count) => {
    cy.get('#discover-table-number-total').contains(count)
    cy.get('#discover-table tbody').children().should('have.length', count)
})

Then('I see {string} tournament on {string} in {string} with {string} cardpool which is {string} with {int} regs', 
    (tournamentName, date, location, cardpool, tournamentType, regs) => {
        validateUpcoming(tournamentName, date, location, cardpool, tournamentType, regs)
    }
)

Then('I see the following upcoming tournaments:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateUpcoming(row[0], row[1], row[2], row[3], row[4], row[5])
    });
})

function validateUpcoming(tournamentName, date, location, cardpool, tournamentType, regs) {
    cy.contains(tournamentName).should('be.visible').parent('td').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date)
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).contains(tournamentType)
        cy.get('td').eq(5).contains(regs)
    })
}