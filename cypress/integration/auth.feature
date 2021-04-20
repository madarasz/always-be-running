Feature: Authentication

    Scenario: Logged out
        Given I open the "Organize" page
        Then I see text "Login required"
        And I see text "Login via NetrunnerDB"
        And "logout button" element doesn't exist
        When I open the "Admin" page
        Then I see text "Access denied"

    Scenario: Logging in
        Given I login with "regular" user
        And I open the "Organize" page
        Then I see text "Tournaments created by me"
        # admin is still access denied
        When I open the "Admin" page
        Then I see text "Access denied"