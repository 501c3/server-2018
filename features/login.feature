Feature: Login
  In order to access the administrative area
  As an admin user
  I need to login

  Background:
    Given I am on homepage
    And There is user "Role" "User" "username" "mgarber@georgiadancesport.org" "password" "678-235-8395","true", "ROLE_USER"
    When I follow "Login"

  Scenario: Login with bad User Name
    When I fill in "User Name" with "username"
    And I fill in "Password" with "password"
    And I press "Login"
    Then I should see "Welcome Role User"

  Scenario: Login with bad User Name
    When I fill in "User Name" with "baduser"
    And I fill in "Password" with "password"
    And I press "Login"
    Then I should see "Username could not be found"

  Scenario: Login with bad Password
    When I fill in "User Name" with "username"
    And I fill in "Password" with "passwordbad"
    And I press "Login"
    Then I should see "Invalid credentials"

  @javascript
  Scenario: Login with Google
    When I follow "Login via Google"
    And I pause to authenticate
    Then I should see "Welcome Role User"

  @javascript
  Scenario: Login with Facebook
    When I follow "Login via Facebook"
    And I pause to authenticate
    Then I should see "Welcome Role User"

  @javascript
  Scenario: Login with LinkedIn
    When I follow "Login via LinkedIn"
    And I pause to authenticate
    Then I should see "Welcome Role User"