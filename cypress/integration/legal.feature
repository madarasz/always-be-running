Feature: Legal

    Scenario: Cookie banner
        Given I open the "Upcoming" page
        Then I see "cookie banner" element
        When I click Privacy and Cookie Policy link
        Then I see text "GDPR"
        When I click on "Allow cookies button" element
        Then I don't see "cookie banner" element
        And "polite Cookie Policy" element exists