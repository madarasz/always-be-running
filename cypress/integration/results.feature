Feature: Results

    Background:
        Given results data is mocked
        And I open the "Results" page

    Scenario: Tournament results show up
        Then I see the following tournament results:
            | title                                         | date        | location   | cardpool          | runner | corp  | players | claims |
            | Oxford Mini evening GNK                       | 2021.04.12. | Oxford     | System Gateway    | 26066  | 31050 | 6       | 0      |
            | "2020" "Stevenage" Store Championship!        | 2021.04.10. | Stevenage  | Salvaged Memories | 21063  | 21009 | 32      | 12     |
            | Startup Tournament - Premade decks Available  | 2021.04.10. | online     | System Gateway    | 26066  | 30035 | 32      | 11     |
            | Hemophilia Charity Tournament                 | 2020.12.05. | online     | Uprising          | 08063  | 21009 | 57      | 9      |
            | Janksgiving Cube                              | 2020.11.21. | TX, Austin | Uprising          | 00006  | 00005 | 7       | 1      |