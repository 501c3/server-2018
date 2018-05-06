Feature:
  In order to be assigned administrative privileges
  As a competition organizer or registrar
  I need to register for administrative access

  Background:
    Given I am on the homepage
    When I follow "Register"
    Then I should see "Register for Administrative Privileges"

  Scenario: Register using form
    When I fill in "First" with "Admin"
    And  I fill in "Last" with "User"
    And  I fill in "Username" with "admin"
    And  I fill in "Password" with "password"
    And  I fill in "Repeat Password" with "password"
    And  I fill in "Email" with "admin@email.org"
    And  I fill in "Repeat Email" with "admin@email.org"
    And  I fill in "Mobile#" with "6782358395"
    And  I press "Register"
    Then I am on the homepage
    And  I should see "Admin User"
    When I follow "Logout"
    Then I should see "Index Page"
    And I should see "Register for Medal Test"
    And I should see "Enter a Competition"
    And I should see "User History"
    And I should see "Management/Administration"

  @javascript
  Scenario: Register using Facebook
    When I follow "Register via Facebook"
    And I pause to authenticate
    Then I should see "Welcome"
    When I follow "Logout"
    Then I should see "Index Page"

  @javascript
  Scenario: Register using LinkedIn
    When I follow "Register via LinkedIn"
    And I pause to authenticate
    Then I should see "Welcome"
    When I follow "Logout"
    Then I should see "Index Page"


  @javascript
  Scenario Outline:
    When I follow "<socialLink>"
    And I pause to authenticate
    Then I should see "Welcome"
    When I follow "Logout"
    Then I should see "Index Page"
    Examples:
      |socialLink|
      |Register via Google  |
      |Register via Facebook|
      |Register via LinkedIn|

