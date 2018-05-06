Feature:
  In order to assign permissions to users
  As superadmin
  I need to see all users that have registered for administrative access and assign permissions

  Background:
    Given users and superadmin are loaded
    And I am on homepage
    When I follow "Login"
    And I fill in "User Name" with "superadmin"
    And I fill in "Password" with "password"
    And I press "Login"
    Then I should see "Welcome Super Admin"
    And I should not see "Register"
    And I should not see "Login"
    And I should see "Channels"


  Scenario: I
