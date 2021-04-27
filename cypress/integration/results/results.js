import { Given, Then, When } from "cypress-cucumber-preprocessor/steps";

Given('results data is mocked', () => {
    cy.intercept('GET', '/api/tournaments/results?limit=50', { fixture: 'results_limit_50.json' })
    cy.intercept('GET', '/api/tournaments/results?limit=1000&offset=50', { fixture: 'results_offset_50_limit_1000.json' })
    // mock date
    cy.clock(new Date(2021, 3, 27), ['Date'])
})

Then('I see the following tournament results:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateResults(row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7])
    });
})

function validateResults(title, date, location, cardpool, runner, corp, players, claims) {
    cy.contains(title).should('be.visible').parent('td').should('have.class', 'tournament-title').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date)
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).should('have.class', 'cell-winner-v').find('img:first').should('have.attr', 'src', '/img/ids/' + runner + '.png')
        cy.get('td').eq(4).should('have.class', 'cell-winner-v').find('img:first').next().should('have.attr', 'src', '/img/ids/' + corp + '.png')
        cy.get('td').eq(5).contains(players)
        cy.get('td').eq(6).contains(claims)
    })
}