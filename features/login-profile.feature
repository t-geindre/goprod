Feature: Login
  As a new user, I should be able to log in with Github
  I should also be prompted to set my golive API key

  Scenario: Login
    Given I am on the homepage

    When I click on login button
    Then I should see "Github oauth mock"

    When I click on Arthur Pendragon
    Then I should see "complete your profile"

    When I fill golive API key field with "invalid key"
    And I click on update profile button
    Then I should see "This API key is not valid."

    When I fill golive API key field with "fds89g7sd98f7sd89c4sd894sd89"
    And I click on update profile button
    Then the profile modal should be hidden

    When I refresh the current page
    Then I should see "Arthur PENDRAGON"
    And I should see "Logout"

    When I click on my name
    And I click on profile button
    Then I should see the profile modal

    When I fill golive API key field with "invalid key"
    And I click on update profile button
    Then I should see "This API key is not valid."

    When I fill golive API key field with " "
    And I click on update profile button
    Then the profile modal should be hidden

    When I click on my name
    And I click on logout button
    Then I should see "Login"

    When I refresh the current page
    Then I should see "Login"
