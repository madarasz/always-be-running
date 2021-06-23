import { Given, Then, When } from "cypress-cucumber-preprocessor/steps";

Given('results data is mocked', () => {
    cy.intercept('GET', '/api/tournaments/results', { fixture: 'results.json' })
    cy.intercept('GET', '/api/tournaments/results?limit=100&offset=0', { fixture: 'results_limit_100_offset_0.json' })
    //cy.intercept('GET', '/api/tournaments/results?limit=50', { fixture: 'results_limit_50.json' })
    //cy.intercept('GET', '/api/tournaments/results?limit=1000&offset=50', { fixture: 'results_offset_50_limit_1000.json' })
    cy.intercept('GET', '/api/tournaments?concluded=0&recur=0&hide-non=1&desc=1&end=*', { fixture: 'results_to_be_concluded' })
    cy.intercept('GET', 'https://alwaysberunning.net/ktm/*').as('metas')
    cy.intercept('GET', 'https://alwaysberunning.net/ktm/metas.json', { fixture: 'ktm_metas.json' })
    cy.intercept('GET', 'https://alwaysberunning.net/ktm/system-gateway.json', { fixture: 'ktm_system_gateway.json' })
    cy.intercept('GET', 'https://alwaysberunning.net/ktm/uprising-ban20-09.json', { fixture: 'ktm_uprising.json' })
    cy.intercept('https://www.gstatic.com/charts/loader.js').as('googleCharts')
    // mock date
    cy.clock(new Date(2021, 3, 27), ['Date'])
})

Then('I see the following tournament results:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateResults(row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7], row[8], row[9])
    });
})

Then('{int} tournament results are visible', (count) => {
    cy.get('#results tbody tr td:nth-child(2)').should('have.length', count)
})

Then('{int} tournaments to be concluded are visible', (count) => {
    cy.get('#to-be-concluded tbody tr td:nth-child(2)').should('have.length', count)
})

Then('At least {int} featured tournament results are visible', (count) => {
    cy.get('.featured-box').should('have.length.greaterThan', count) // plus one because "Support me" is also a featured-box
})

Then('I see the following to be concluded tournaments:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        validateToBeConcluded(row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7], row[8], row[9])
    });
})

Then('I see the following featured tournament results:', (dataTable) => {
    dataTable.rawTable.slice(1).forEach(row => {
        valudateFeaturedResults(row[0], row[1], row[2], row[3], row[4], row[5], row[6], row[7], row[8], row[9], row[10], row[11])
    });
})

When('I filter results cardpool to {string}', (cardpool) => {
    cy.get('#cardpool:not([disabled])').select(cardpool)
})

When('I filter results type to {string}', (cardpool) => {
    cy.get('#tournament_type_id:not([disabled])').select(cardpool)
})

When('I filter results country to {string}', (cardpool) => {
    cy.get('#location_country:not([disabled])').select(cardpool)
})

When('I filter results format to {string}', (cardpool) => {
    cy.get('#format:not([disabled])').select(cardpool)
})

Then('{string} statistics loads', (cardpool) => {
    cy.get('#stat-chart-runner', { timeout: 10000 }).should('be.visible')
    cy.wait(500)
    cy.get('#stat-chart-runner').scrollIntoView().matchImageSnapshot('stat-runner-'+cardpool, { failureThreshold: 0.03 })
    cy.get('#stat-chart-corp').scrollIntoView().matchImageSnapshot('stat-corp-'+cardpool, { failureThreshold: 0.03 })
})

function validateResults(title, date, location, cardpool, runner, corp, players, claims, typeIcon, icons) {
    cy.contains(title).should('be.visible').parent('td').should('have.class', 'tournament-title').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date.slice(0,5))
        cy.get('td').eq(1).contains(date.slice(5,6))
        if (date.slice(-1) === '+') cy.get('td').eq(1).find("i[title='multiple day event']")
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).should('have.class', 'cell-winner-v').find('img:first').should('have.attr', 'data-src', '/img/ids/' + runner + '.png')
        cy.get('td').eq(4).should('have.class', 'cell-winner-v').find('img:first').next().should('have.attr', 'data-src', '/img/ids/' + corp + '.png')
        cy.get('td').eq(5).contains(players)
        if (claims.slice(-1) === '!') {
            cy.get('td').eq(6).contains(claims.slice(0, -1)).find("i[title='conflict']")
        } else {
            cy.get('td').eq(6).contains(claims)
            cy.get('td').eq(6).find("i[title='conflict']").should('not.exist')
        }
        validateTypeIcon(typeIcon)
        validateIcons(icons)
    })
}

function validateToBeConcluded(title, date, location, cardpool, regs, typeIcon, icons) {
    cy.contains(title).should('be.visible').parent('td').should('have.class', 'tournament-title').parent('tr').within(() => {
        cy.get('td').eq(1).contains(date.slice(0,5))
        cy.get('td').eq(1).contains(date.slice(5))
        cy.get('td').eq(2).contains(location)
        cy.get('td').eq(3).contains(cardpool)
        cy.get('td').eq(4).contains('waiting')
        cy.get('td').eq(5).contains(regs)
        validateTypeIcon(typeIcon)
        validateIcons(icons)
    })
}

function valudateFeaturedResults(title, date, cardpool, location, winner, runner, corp, iconType, players, claims, photos, videos) {
    cy.get('.featured-title').contains(title).should('be.visible').parent('div').parent('div').parent('div').parent('div').within(() => {
        cy.get('span.small-text').contains(date)
        cy.get('span.small-text').contains(cardpool)
        if (iconType.length > 0) {
            cy.get('.featured-title').find('span').should('have.class', 'type-'+iconType)
        }
        cy.get('table').contains(winner)
        cy.get('table tr td.cell-winner').find('img').eq(0).should('have.attr', 'data-src', '/img/ids/' + runner + '.png')
        cy.get('table tr td.cell-winner').find('img').eq(1).should('have.attr', 'data-src', '/img/ids/' + corp + '.png')
        cy.get('.featured-footer').contains(location).contains(players).contains(claims).contains(photos).contains(videos)
    })
}

function validateTypeIcon(typeIcon) {
    if (typeIcon && typeIcon.length > 0) {
        cy.get('td.tournament-title').find('span').should('have.class', 'type-'+typeIcon)
    } else {
        cy.get('td.tournament-title').find('span').should('not.have.class', 'tournament-format')
        cy.get('td.tournament-title').find('span').should('not.have.class', 'tournament-type')
    }
}

function validateIcons(icons) {
    if (icons && icons.length > 0) {
        icons.split(',').forEach((icon) => {
            cy.get(`td.tournament-title i[title='${icon}']`)
        })
    } else {
        cy.get('td.tournament-title').should('not.have.descendants', 'i')
    }
}