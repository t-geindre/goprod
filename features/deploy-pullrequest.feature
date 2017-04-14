Feature: Deploy by pullrequest
  As a new user, I should be able to log in with Github and setup my account
  Then I shloud be able to deploy by pullrequest.

  Scenario: Success deploy
    Given I am logged in as Guenièvre PENDRAGON

    Then I should see "complete your profile"
    When I fill golive API key field with "fds89g7sd98f7sd88sd8d4sd894sd89liu98"
    And I click on update profile button
    Then the profile modal should be hidden
    And I should see "My deployments"

    When I click on Plus button
    And I click on Pullrequest entry
    Then I should see "Let's go prod!"
    And I should see "Select pull request"

    When I click on Issue 1000003
    Then I should see "You're about to create a new deployment"

    When I click on new deployment button
    Then I should see "Deployment"
    And I should see "OM/team-players-om"
    And I should see "Pullrequest"
    And I should see "Merge options"

    When I click on action button
    Then I should see a merged Pullrequest
    And I should see "Deploy project"

    When I click on action button
    Then I should see a pending golive deployment
    And I should see a successfull golive deployment
    And I should see "Confirm deployment is over"

    When I click on action button
    Then I should see "My deployments"

  Scenario: Unmergeable pullrequest shouldn't be deployable
    Given I am logged in as Guenièvre PENDRAGON

    When I click on Plus button
    And I click on Pullrequest entry
    Then I should see "Let's go prod!"
    And I should see "Select pull request"

    When I click on Issue 1003
    Then I should see "You're about to create a new deployment"
    And new deployment button should be disabled

  Scenario: Deploy fail two times then I cancel my deployment
    Given I am logged in as Guenièvre PENDRAGON

    When I click on Plus button
    And I click on Pullrequest entry
    Then I should see "Let's go prod!"
    And I should see "Select pull request"

    When I click on Issue 10003
    Then I should see "You're about to create a new deployment"

    When I click on new deployment button
    Then I should see "Deployment"
    And I should see "ASSE/team-players-asse"
    And I should see "Pullrequest"
    And I should see "Merge options"

    When I click on action button
    Then I should see a merged Pullrequest
    And I should see "Deploy project"

    When I click on action button
    Then I should see a pending golive deployment
    And I should see a failed golive deployment
    And action button should be enabled

    When I click on action button
    Then I should see a pending golive deployment
    And I should see a failed golive deployment
    And cancel deployment button should be enabled

    When I click on cancel deployment button
    Then I should see cancel deployment modal
    And I should see "Confirm deployment cancelling"

    When I click on confirm deployment cancel button
    Then I should see "My deployments"

  Scenario: Deploy closed Pullrequest
    Given I am logged in as Guenièvre PENDRAGON

    When I click on Plus button
    And I click on Pullrequest entry
    Then I should see "Let's go prod!"
    And I should see "Select pull request"

    When I click on closed PR button
    Then I should see "OM/team-players-om"

    When I click on Issue 1000003
    Then I should see "You're about to create a new deployment"

    When I click on new deployment button
    Then I should see "Deployment"
    And I should see "OM/team-players-om"
    And I should see "Pullrequest"
    And I should see a merged Pullrequest
    And I should see "Deploy project"

    When I click on action button
    Then I should see a pending golive deployment
    And I should see a successfull golive deployment
    And I should see "Confirm deployment is over"

    When I click on action button
    Then I should see "My deployments"
