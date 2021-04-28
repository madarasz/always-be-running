import { Given, Then, When } from "cypress-cucumber-preprocessor/steps";

Given('results data is mocked', () => {
    cy.intercept('GET', '/api/tournaments/results?limit=50', { fixture: 'results_limit_50.json' })
    cy.intercept('GET', '/api/tournaments/results?limit=1000&offset=50', { fixture: 'results_offset_50_limit_1000.json' })
    cy.intercept('GET', '/api/tournaments?concluded=0&recur=0&hide-non=1&desc=1&end=2021.04.28.', { fixture: 'results_to_be_concluded' })
    // mock date
    cy.clock(new Date(2021, 3, 27), ['Date'])
})

Then('I see the following tournament results:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateResults(row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7], row[8], row[9])
    });
})

Then('{int} tournament results are visible', (count) => {
    cy.get('#results tbody tr').filter(':visible').should('have.length', count)
})

Then('{int} tournaments to be concluded are visible', (count) => {
    cy.get('#to-be-concluded tbody tr').filter(':visible').should('have.length', count)
})

Then('I see the following to be concluded tournaments:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateToBeConcluded(row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7], row[8], row[9])
    });
})

function validateResults(title, date, location, cardpool, runner, corp, players, claims, typeIcon, icons) {
    cy.contains(title).should('be.visible').parent('td').should('have.class', 'tournament-title').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date)
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).should('have.class', 'cell-winner-v').find('img:first').should('have.attr', 'src', '/img/ids/' + runner + '.png')
        cy.get('td').eq(4).should('have.class', 'cell-winner-v').find('img:first').next().should('have.attr', 'src', '/img/ids/' + corp + '.png')
        cy.get('td').eq(5).contains(players)
        cy.get('td').eq(6).contains(claims)
        validateTypeIcon(typeIcon)
        validateIcons(icons)
    })
}

function validateToBeConcluded(title, date, location, cardpool, regs, typeIcon, icons) {
    cy.contains(title).should('be.visible').parent('td').should('have.class', 'tournament-title').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date)
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).contains('waiting')
        cy.get('td').eq(5).contains(regs)
        validateTypeIcon(typeIcon)
        validateIcons(icons)
    })
}

function validateTypeIcon(typeIcon) {
    if (typeIcon.length > 0) {
        cy.get('td.tournament-title').find('span').should('have.class', 'type-'+typeIcon)
    } else {
        cy.get('td.tournament-title').find('span').should('not.have.class', 'tournament-format')
        cy.get('td.tournament-title').find('span').should('not.have.class', 'tournament-type')
    }
}

function validateIcons(icons) {
    if (icons.length > 0) {
        icons.split(',').forEach((icon) => {
            cy.get(`td.tournament-title i[title='${icon}']`)
        })
    } else {
        cy.get('td.tournament-title').should('not.have.descendants', 'i')
    }
}