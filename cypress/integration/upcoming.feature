Feature: Upcoming page

    Background:
        Given upcoming data is mocked
        And I open the "Upcoming" page

    Scenario: Loading upcoming tournaments table
        Then I see 18 upcoming tournaments
        And I see the following upcoming tournaments:
            | title                                         | date        | location   | cardpool             | type               | regs | icon    |
            | Baltic Grid GNK tournament                    | 2021.04.18. | online     | System Gateway       | online event       | 4    | startup |
            | GLC Retrunner2 - Core + Genesis + C&C         | 2021.04.18. | online     | Creation and Control | online event       | 1    |         |
            | Double Elimination Bracket #3                 | 2021.05.05. | Queensland | System Gateway       | GNK / seasonal     | 0    | startup |
            | NtscapeNavigator's online Store Championship  | 2021.05.08. | York       | System Gateway       | store championship | 18   | store   |
    # TODO featured

    Scenario: Upcoming tournament table controlls
        Then 10 upcoming tournaments are visible
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
        And 18 upcoming tournaments are visible

    Scenario: Filtering upcoming
        When I filter upcoming tournament type to "store championship"
        Then 6 upcoming tournaments are visible
        And I see the following upcoming tournaments:
            | title                                          | date        | location     | cardpool             | type               | regs | icon    |
            | Italian Store Championship 2021 - Holy edition | 2021.05.09. | Vatican City | System Gateway       | store championship | 1    | store   |
            | Augsburg SC - still no new date                | 2021.07.01. | Augsburg     | Uprising             | store championship | 10   | store   |
            | NtscapeNavigator's online Store Championship   | 2021.05.08. | York         | System Gateway       | store championship | 18   | store   |
        When I filter upcoming tournament country to "Germany"
        Then 1 upcoming tournaments are visible
        And I see the following upcoming tournaments:
            | title                                          | date        | location     | cardpool             | type               | regs | icon   |
            | Augsburg SC - still no new date                | 2021.07.01. | Augsburg     | Uprising             | store championship | 10   | store   |
        When I filter upcoming tournament type to "---"
        And I filter upcoming tournament country to "United States"
        And I click on "include online checkbox" element
        Then 2 upcoming tournaments are visible
        And I filter upcoming tournament state to "TX"
        Then 1 upcoming tournaments are visible
        And 4 recurring events are visible

    Scenario: Loading recurring table
        And I see the following recurring events:
            | title                                  | location      | day    |
            | Boswash Bash! Netrunner Meetup         | CT, Newington | Monday |
            | Cheap Thrills Weekly Netrunner         | Newport       | Monday |
            | WegRunner - a Weekly Netrunner Meetup! | MD, Columbia  | Monday |

    Scenario: Upcoming calendar
        Then I see 6 days marked in the calendar
        When I click day 18 in the calendar
        Then calendar displays "Baltic Grid GNK tournament" tournament
        And calendar displays "GLC Retrunner2 - Core + Genesis + C&C" tournament
        # filter
        When I filter upcoming tournament country to "Australia"
        And I click on "include online checkbox" element
        Then I see 1 days marked in the calendar

    Scenario: Upcoming map
        Given I set up Google map intercepts
        When I display upcoming map
        Then worldwide Google map loads
        # filter
        When I filter upcoming tournament country to "Germany"
        Then German Google map loads