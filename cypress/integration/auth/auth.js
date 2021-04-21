import { When } from "cypress-cucumber-preprocessor/steps";

When('I login with {string} user', (user) => {
    cy.clearCookies()
    cy.clearLocalStorage()
    cy.readFile('.env').then((env) => {
        const clientid_pattern = /NETRUNNERDB_CLIENT_ID=(.*)/
        const redirecturi_pattern = /NETRUNNERDB_REDIRECT_URL=(.*)/
        const clientid = env.match(clientid_pattern)[1]
        const redirecturi = env.match(redirecturi_pattern)[1]
        const netrunnerdb_auth_url = 'https://netrunnerdb.com/oauth/v2/auth'
        const netrunnerdb_login_url = 'https://netrunnerdb.com/oauth/v2/auth_login_check'
        const username = Cypress.env(user + '_username')
        const password = Cypress.env(user + '_password')
        
        cy.request({
            method: 'POST',
            url: netrunnerdb_login_url,
            form: true,
            body: {
                _username: username,
                _password: password,
                _submit: 'Log In'
            },
            followRedirect: false
        }).then(({status, headers, body}) => {
            //console.log(status, headers, body)
        })
        
        cy.request({
            method: 'GET',
            url: netrunnerdb_auth_url,
            qs: {
                type: 'web_server',
                client_id: clientid,
                redirect_uri: redirecturi,
                response_type: 'code',
                scope: ''
            },
            followRedirect: false
        }).then(({status, headers, body}) => {
            //console.log(status, headers, body)
            const token = getToken(body)
            //console.log('token:', token)
            cy.request({
                method: 'POST',
                url: netrunnerdb_auth_url,
                qs: {
                    type: 'web_server',
                    client_id: clientid,
                    redirect_uri: 'http://' + redirecturi + '/oauth2/redirect',
                    response_type: 'code',
                    scope: ''
                },
                form: true,
                body: {
                    accepted: 'Allow',
                    'fos_oauth_server_authorize_form[client_id]': clientid,
                    'fos_oauth_server_authorize_form[response_type]': 'code',
                    'fos_oauth_server_authorize_form[redirect_uri]': redirecturi,
                    'fos_oauth_server_authorize_form[state]': '',
                    'fos_oauth_server_authorize_form[scope]': '',
                    'fos_oauth_server_authorize_form[_token]': token
                }
            }).then(({status, headers, body}) => {
                //console.log(status, headers, body)
            })
        })
    })    
})

function getToken(body) {
    const pattern = /name=\"fos_oauth_server_authorize_form\[_token\]\" value=\"(.*)\"/
    return body.match(pattern)[1]
}