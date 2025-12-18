@qtype @qtype_fileresponse
Feature: Teachers can comment the grade for a file response question
  As a teacher
  In order to grade file response questions
  I must be able to note a grade and add a comment.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student0@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | name    | intro              | course | idnumber | grade |
      | quiz       | Quiz 1  | Quiz 1 description | C1     | quiz1    | 20    |
    And the following "question categories" exist:
      | contextlevel    | reference | name           |
      | Activity module | quiz1     | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype        | name          |
      | Test questions   | fileresponse | File Response |
    And quiz "Quiz 1" contains the following questions:
      | question      | page |
      | File Response | 1    |
    And the following "user private files" exist:
      | user     | filepath                                               |
      | student1 | question/type/fileresponse/tests/fixtures/testfile.txt |

  @javascript @_switch_window @_file_upload @_bug_phantomjs @editor_tiny
  Scenario: Comment on a response to an fileresponse question attempt.
    When I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I click on "Add..." "button"
    Then I should see "Upload a file"
    And I should see "Private files"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "testfile.txt" "link"
    And I click on "Select this file" "button"
    And I follow "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I log out
    And I log in as "teacher1"
    And I am on the "Quiz 1 > student1 > Attempt 1" "mod_quiz > Attempt review" page
    And I follow "Make comment or override mark"
    And I switch to "commentquestion" window
    And I set the field "Comment" to "Teacher's comment"
    And I set the field "Mark" to "1"
    And I press "Save" and switch to main window
    And I switch to the main window
    Then I should see "Teacher's comment" in the ".history table" "css_element"
    # This time is same as time the window is open. So wait for it to close before proceeding.
    And I wait "2" seconds
