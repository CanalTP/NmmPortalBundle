Feature: Customer List

  Scenario: Edit page link
    Given I am logged in as Administrator
    When I am on "/admin/customer"
    Then I should see an ".edit-btn" element
    And I should see an ".archive" element

  Scenario: Check if "Realtime Deactivate" customer in the list
    Given I am logged in as Administrator
    When I am on "/admin/customer"
    Then I should see "Realtime Deactivate"

  Scenario: Check if deactivate link exist
    Given I am logged in as Administrator
    And I am on "/admin/customer"
    When I press "show-toggled-actions-2"
    And I wait 2 seconds
    And I follow "archive-2"
    And I wait 2 seconds
    Then I should see "Désactiver le client"

  Scenario: Deactivate customer "Realtime Deactivate"
    Given I am logged in as Administrator
    And I am on "/admin/customer"
    And I press "show-toggled-actions-2"
    And I wait 2 seconds
    And I follow "archive-2"
    And I wait 2 seconds
    When I press "Désactiver"
    Then I should see an ".alert-success" element
    When I am on "/admin/customer"
    Then I should see "CanalTP"
    And I should not see "Realtime Deactivate"
