Feature: Authentication

    Scenario: Logged out
        Given I open the "Organize" page
        Then I see text "Login required"
        And I see text "Login via NetrunnerDB"
        And "logout button" element doesn't exist
        When I open the "Admin" page
        Then I see text "Access denied"

    Scenario: Logging in with regular user
        Given I login with "regular" user
        And I open the "Organize" page
        Then I see text "Tournaments created by me"
        # admin is still access denied
        When I open the "Admin" page
        Then I see text "Access denied"

    Scenario: Logging in with admin user
        Given I login with "admin" user
        And I open the "Organize" page
        Then I see text "Tournaments created by me"
        # admin is still access denied
        When I open the "Admin" page
        Then I see text "Administration"