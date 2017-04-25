Feature: Deploy queue
  When someone else if already deploying the same project
  I should be able to start a new deployment and it should be queued

  Scenario: Queued deploy
    Given I am logged in as Karadoc DEVANNES
    Then I should see "complete your profile"
    When I fill golive API key field with "48liu41fds12f84tyku9k87ze"
    And I click on update profile button
    Then the profile modal should be hidden
    And I should see "My deployments"

    When I click on Plus button
    And I click on Repository entry
    Then I should see "Let's go prod!"

    When I fill repository field with "asse"
    Then I should see "ASSE/ads-manager-asse"

    When I press return on repository field
    And I fill description field with "Blocking deployment"
    And I click on new deployment button
    Then I should see "Deployment"
    And I should see "ASSE/ads-manager-asse"

    When I click on my name
    And I click on logout button
    Then I should see "Login"

    When I am logged in as Perceval LEGALLOIS
    Then I should see "complete your profile"
    When I fill golive API key field with "fze789f98fds98c4ds6c1489f4e9f"
    And I click on update profile button
    Then the profile modal should be hidden
    And I should see "My deployments"

    When I click on Plus button
    And I click on Repository entry
    Then I should see "Let's go prod!"

    When I fill repository field with "asse"
    Then I should see "ASSE/ads-manager-asse"

    When I press return on repository field
    And I fill description field with "This is my deployment"
    Then I should see "This deployment will be queued"

    When I click on new deployment button
    Then I should see "Deployment"
    And I should see "ASSE/ads-manager-asse"
    And I should see "This deployment has been queued"
    And I should see "Blocking deployment"
    And action button should be disabled

    When I click on my name
    And I click on logout button
    Then I should see "Login"

    When I am logged in as Karadoc DEVANNES
    And I click on my name
    And I click on my deployments
    Then I should see "Blocking deployment"

    When I click on the first deployment
    Then I should see "Deployment process"

    When I click on cancel deployment button
    Then I should see cancel deployment modal
    And I should see "Confirm deployment cancelling"

    When I click on confirm deployment cancel button
    Then I should see "My deployments"

    When I click on my name
    And I click on logout button
    Then I should see "Login"

    When I am logged in as Perceval LEGALLOIS
    And I click on my name
    And I click on my deployments
    Then I should see "This is my deployment"

    When I click on the first deployment
    Then I should see "Deployment process"
    And action button should be enabled

    When I click on cancel deployment button
    Then I should see cancel deployment modal
    And I should see "Confirm deployment cancelling"

    When I click on confirm deployment cancel button
    Then I should see "My deployments"
