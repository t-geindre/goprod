Feature: Deploy by repository
  As a new user, I should be able to log in with Github and setup my account
  Then I shloud be able to deploy by repository.

  Scenario: Success deploy
    Given I am logged in as Léo DAGAN

    Then I should see "complete your profile"
    When I fill golive API key field with "8ezfds5c18zf7ds89ds61vcsd6v5498"
    And I click on update profile button
    Then the profile modal should be hidden
    And I should see "My deployments"

    When I click on Plus button
    And I click on Repository entry
    Then I should see "Let's go prod!"
    And I should see "You're about to create a new deployment"

    When I fill repository field with "asse"
    Then I should see "ASSE/ads-manager-asse"

    When I press return on repository field
    Then I should see "ASSE"

    When I click on new deployment button
    Then I should see "This value should not be blank."

    When I fill description field with "This is my deployment"
    And I click on new deployment button
    Then I should see "Deployment"
    And I should see "ASSE/ads-manager-asse"
    And I should see "This is my deployment"
    And I should see "Deployment process"
    And I should see "Deploy project"

    When I click on action button
    Then I should see a pending Golive deployment
    And I should see a successfull golive deployment

    When I click on action button
    Then I should see "My deployments"

