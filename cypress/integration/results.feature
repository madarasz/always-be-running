Feature: Results

    Background:
        Given results data is mocked
        And I open the "Results" page

    Scenario: Tournament results show up
        Then 50 tournament results are visible
        Then I see the following tournament results:
            | title                                         | date        | location   | cardpool          | runner | corp  | players | claims | icon-type  | icons                             |
            | Oxford Mini evening GNK                       | 2021.04.12. | Oxford     | System Gateway    | 26066  | 31050 | 6       | 0      |            | match data                        |
            | "2020" "Stevenage" Store Championship!        | 2021.04.10. | Stevenage  | Salvaged Memories | 21063  | 21009 | 32      | 12!    | store      | match data,top cut                |
            | Startup Tournament - Premade decks Available  | 2021.04.10. | online     | System Gateway    | 26066  | 30035 | 32      | 11     | other      | match data                        |
            | Hemophilia Charity Tournament                 | 2020.12.05. | online     | Uprising          | 08063  | 21009 | 57      | 9      | other      | charity,match data,top cut,photo  |
            | Janksgiving Cube                              | 2020.11.21. | TX, Austin | Uprising          | 00006  | 00005 | 7       | 1      | cube-draft |                                   |
    
    Scenario: Tournaments waiting for conclusion
        When I click on "waiting for conclusion tab" element
        Then I see "conclude login warning" element
        And 19 tournaments to be concluded are visible
        And I see the following to be concluded tournaments:
            | title                                 | date        | location   | cardpool          | regs | icon-type | icons |
            | Open Gate(way) Tournament             | 2021.04.25. | online     | System Gateway    | 3    |           |       |
            | Torneo Startup Iniciación en español  | 2021.04.17. | Barcelona  | System Gateway    | 8    | startup   | photo |
            | GLC Gateway Tournament                | 2021.03.29. | online     | System Gateway    | 83   | other     |       |
            | Perth City Grid - Store Championships | 2021.02.13. | Cannington | Salvaged Memories | 3    | store     |       |
        And "results conclude button" element doesn't exist
        # login
        When I login with "regular" user
        And I open the "Results" page
        And I click on "waiting for conclusion tab" element
        Then I see "results conclude button" element

    Scenario: Featured
        Then At least 1 featured tournament results are visible
        And I see "support me featured box" element
        And I see the following featured tournament results:
            | title       | date        | cardpool | location      | winner | runner | corp  | icon-type | players | claims | photos | videos |
            | NISEI World | 2020.10.09. | Uprising | United States | Limes  | 26066  | 22026 | world     | 294     | 80     | 1      | 3      |

    Scenario: Filtering
        # filter cardpool to Uprising
        When I filter results cardpool to "Uprising"
        Then 28 tournament results are visible
        And current url is "results?cardpool=Uprising"
        And I see the following tournament results:
            | title                                              | date         | location        | cardpool | runner | corp  | players | claims | icon-type  | icons                          |
            | Same Old Lang Syne: 1st Eternal Event 2021 babyyyy | 2021.01.16.  | online          | Uprising | 07030  | 06105 | 7       | 1      | eternal    | match data                     |
            | NISEI World Championship 2020                      | 2020.10.09.+ | CA, California  | Uprising | 26066  | 22026 | 294     | 77     | world      | match data,top cut,photo,video |
        When I click on "waiting for conclusion tab" element
        Then 2 tournaments to be concluded are visible
        And I see the following to be concluded tournaments:
            | title                                         | date        | location            | cardpool | regs | icon-type | icons |
            | Esslinger Store Championship - Date to follow | 2020.12.19. | Esslingen am Neckar | Uprising | 8    | store     |       |
        And I see "conclude login warning" element
        And "results conclude button" element doesn't exist
        When I click on "tournament results tab" element
        # filter type to store championship
        And I filter results type to "store championship"
        Then 6 tournament results are visible
        And current url is "results?cardpool=Uprising&type=store%20championship"
        # filter country to Italy
        When I filter results country to "Italy"
        Then 1 tournament results are visible
        And current url is "results?cardpool=Uprising&type=store%20championship&country=Italy"
        When I click on "waiting for conclusion tab" element
        Then 0 tournaments to be concluded are visible
        And I see text "no tournaments waiting for conclusion"
        And current url is "results?cardpool=Uprising&type=store%20championship&country=Italy#tab-to-be-concluded"
        When I click on "tournament results tab" element
        # filter format to cube draft
        And I filter results format to "cube draft"
        Then 0 tournament results are visible
        And current url is "results?cardpool=Uprising&type=store%20championship&country=Italy&format=cube%20draft"
        And I see text "no tournaments to show"
        # matchdata
        When I filter results cardpool to "---"
        And I filter results type to "---"
        And I filter results country to "United Kingdom"
        And I filter results format to "---"
        And I turn checkbox "matchdata" ON
        Then 4 tournament results are visible
        And current url is "results?country=United%20Kingdom&matchdata=true"
        # video
        When I turn checkbox "videos" ON
        And I filter results country to "---"
        Then 2 tournament results are visible
        And current url is "results?videos=true&matchdata=true"

    Scenario: Paging
        Then I see text "showing 1-50 of 65"
        And 50 tournament results are visible
        When I click on "results back button" element
        Then I see text "showing 51-65 of 65"
        And 15 tournament results are visible
        When I click on "results 100 option" element
        Then I see text "showing 1-65 of 65"
        And 65 tournament results are visible
        When I click on "results all option" element
        Then I see text "showing 1-65 of 65"
        And 65 tournament results are visible
        # flag vs text
        And I see "online flag" element
        When I click on "results text option" element
        Then I don't see "online flag" element
        And I see text "United Kingdom, Oxford"
        # filter
        When I filter results country to "United Kingdom"
        Then I see text "showing 1-6 of 6"
        And 6 tournament results are visible

    Scenario: Filtering with URLs
        When I visit url "results?cardpool=System-Gateway&mwl=Standard-Ban-List-21.04&format=standard&videos=true&matchdata=true"
        Then 1 tournament results are visible
        And I see text "Early Bird Gateway Tournament"
        When I visit url "results?type=store%20championship&country=Netherlands"
        Then 1 tournament results are visible
        And I see text "F2F corona-proof SC @ Deventer"

    Scenario: User's default country
        When I open the "Organize" page 
        And I login with "regular" user
        And I open the "Organize" page 
        And I click on "Profile menu" element
        And I click on "Profile Edit button" element
        And I select "Germany" in "country_id" selector
        And I turn checkbox name "autofilter_results" ON
        And I click on "Profile Save button" element
        And I open the "Results" page
        Then current url is "results?country=Germany"
        And 3 tournament results are visible
        And select "location_country" should have "Germany" selected
        And I see "results default country label" element
        When I filter results country to "United Kingdom"
        Then element "results default country label" should not exist
        And 6 tournament results are visible

    Scenario: Know the Meta stats
        Then "System Gateway" statistics loads
        When I filter results cardpool to "Uprising"
        Then "Uprising + Ban20.09" statistics loads
        When I filter results cardpool to "Core Set"
        Then I see text "no stats available"

        # TODO: navigate to #tab-to-be-concluded
        # paging on more than 100