import { Given, Then, When } from "cypress-cucumber-preprocessor/steps";

Given('upcoming data is mocked', () => {
    cy.intercept('GET', '/api/tournaments/upcoming', { fixture: 'upcoming.json' })
    // mock date
    cy.clock(new Date(2021, 3, 18), ['Date'])
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
        validateUpcoming(row[0], row[1], row[2], row[3], row[4], row[5], row[6])
    });
})

Then('I see the following recurring events:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateRecurring(row[0], row[1], row[2])
    })
})

When('I click day {int} in the calendar', (dayNumber) => {
    cy.get('span.fc-date').contains(dayNumber).parent('div').click()
})

Then ('calendar displays {string} tournament', (tournamentTitle) => {
    cy.get('#custom-content-reveal').contains(tournamentTitle)
})

When('I filter upcoming tournament type to {string}', (tournamentType) => {
    cy.get('#tournament_type_id').select(tournamentType)
})

When('I filter upcoming tournament country to {string}', (country) => {
    cy.get('#location_country').select(country)
})

When('I filter upcoming tournament state to {string}', (state) => {
    cy.get('#location_state').select(state)
})

Then('{int} upcoming tournaments are visible', (count) => {
    cy.get('#discover-table tbody tr').filter(':visible').should('have.length', count)
})

Then('{int} recurring events are visible', (count) => {
    cy.get('#recur-table tbody tr').filter(':visible').should('have.length', count)
})

Then('I see {int} days marked in the calendar', (count) => {
    cy.get('.fc-content').filter(':visible').should('have.length', count)
})

function validateUpcoming(tournamentName, date, location, cardpool, tournamentType, regs, icon) {
    cy.contains(tournamentName).should('be.visible').parent('td').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date)
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).contains(tournamentType)
        cy.get('td').eq(5).contains(regs)
        if (icon.length > 0) {
            cy.get('td').eq(0).get('span').should('have.class', 'type-'+icon)
        } else {
            cy.get('td').eq(0).get('span').should('not.have.class', 'tournament-format')
            cy.get('td').eq(0).get('span').should('not.have.class', 'tournament-type')
        }
    })
}

function validateRecurring(eventTitle, location, day) {
    cy.contains(eventTitle).should('be.visible').parent('td').parent('tr').within(() => {
        cy.get('td').eq(1).contains(location)
        cy.get('td').eq(2).contains(day)
    })
}