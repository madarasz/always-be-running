Feature: Upcoming page

    Background:
        Given upcoming data is mocked
        And I open the "Upcoming" page

    Scenario: Loading upcoming tournaments table
        Then I see 18 upcoming tournaments
        And I see the following upcoming tournaments:
            | title                                         | date        | location   | cardpool             | type               | regs |
            | Baltic Grid GNK tournament                    | 2021.04.18. | online     | System Gateway       | online event       | 4    |
            | GLC Retrunner2 - Core + Genesis + C&C         | 2021.04.19. | online     | Creation and Control | online event       | 1    |
            | Double Elimination Bracket #3                 | 2021.05.05. | Queensland | System Gateway       | GNK / seasonal     | 0    |
            | NtscapeNavigator's online Store Championship  | 2021.05.08. | York       | System Gateway       | store championship | 18   |

    Scenario: Upcoming tournament table controlls
        # flag/text switch
        Then I see "online flag" element
        And I see "Canada flag" element
        And I don't see text "Canada,"
        When I click on "text button" element
        Then I don't see "Canada flag" element
        And I see text "Canada,"
        When I click on "flag button" element
        Then I see "Canada flag" element
        And I don't see text "Canada,"
        # paging
        Then I don't see text "Greenpeace charity event"
        When I click on "upcoming forward button" element
        Then I see text "Greenpeace charity event"
        And I don't see text "Double Elimination Bracket #2"
        When I click on "upcoming all button" element
        Then I see text "Greenpeace charity event"
        And I see text "Double Elimination Bracket #2"

    #Scenario: Filtering upcoming

    #Scenario: Loading recurring table

    #Scenario: Upcoming calendar

    Scenario: Upcoming map
        Given I set up Google map intercepts
        When I display upcoming map
        Then Google map loads