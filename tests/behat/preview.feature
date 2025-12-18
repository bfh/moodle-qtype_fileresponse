@qtype @qtype_fileresponse
Feature: Preview File response questions
  As a teacher
  In order to check my File response questions will work for students
  I need to preview them

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
      | student  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity | name   | intro        | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 intro | C1     | quiz1    |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | private_files | System       | 1         | my-index        | side-post     |

  @javascript @_switch_window
  Scenario: Preview an File response question for Moodle ≤ 4.2
    Given the site is running Moodle version 4.2 or lower
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "File Response" question filling the form with:
      | Question name                             | File Response 001                       |
      | Question text                             | Upload a PDF file, please.              |
      | General feedback                          | This is general feedback                |
      | File click context menu and File download | Rename, Delete (file download disabled) |
      | File picker plugins                       | Disable ("Upload a file" only)          |
    Then I should see "File Response 001"
    When I am on the "File Response 001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I should see "Upload a PDF file, please."

  @javascript @_switch_window
  Scenario: Preview an File response question for Moodle ≥ 4.3 and Moodle ≤ 4.5
    Given the site is running Moodle version 4.3 or higher
    And the site is running Moodle version 4.5 or lower
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "File Response" question filling the form with:
      | Question name                             | File Response 001                       |
      | Question text                             | Upload a PDF file, please.              |
      | General feedback                          | This is general feedback                |
      | File click context menu and File download | Rename, Delete (file download disabled) |
      | File picker plugins                       | Disable ("Upload a file" only)          |
    Then I should see "File Response 001"
    When I am on the "File Response 001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Save preview options and start again"
    And I should see "Upload a PDF file, please."

  @javascript @_switch_window
  Scenario: Preview an File response question for Moodle ≥ 5.0
    Given the site is running Moodle version 5.0 or higher
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "File Response" question filling the form with:
      | Question name                             | File Response 001                       |
      | Question text                             | Upload a PDF file, please.              |
      | General feedback                          | This is general feedback                |
      | File click context menu and File download | Rename, Delete (file download disabled) |
      | File picker plugins                       | Disable ("Upload a file" only)          |
    Then I should see "File Response 001"
    When I am on the "File Response 001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Save preview options and start again"
    And I should see "Upload a PDF file, please."
