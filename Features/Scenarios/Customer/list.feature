Feature: Customer List

    Background:
      Given I am logged in as Administrator
      
    Scenario: Edit page link
      When I am on "/admin/customer"
      Then I should see an "#edit-btn" element
      And I should see an ".archive" element
