Feature: Customer List

    Background:
      Given I am logged in as Administrator
      
    Scenario: Edit page link
      When I am on "/admin/customer"
      Then I should see an ".edit-btn" element
      And I should see an ".archive" element

    Scenario: Deactivate a customer
      When I am on "/admin/customer"
      And I press "show-toggled-actions-2"
      And I wait 2 seconds
      And I follow "archive-2"
      And I wait 2 seconds
      Then I should see "Désactiver le client"
      When I press "Désactiver"
      Then I should be on "/admin/customer"
      And I should see an ".alert-success" element