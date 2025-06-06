<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the fileresponse question definition class.
 *
 * @package    qtype_fileresponse
 * @copyright  2022 Luca Bösch, BFH Bern University of Applied Sciences luca.boesch@bfh.ch
 * @copyright  based on work by 2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_fileresponse;

use qtype_fileresponse_question;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/fileresponse/tests/helper.php');
require_once($CFG->dirroot . '/question/type/fileresponse/question.php');
require_once($CFG->dirroot . '/question/type/fileresponse/renderer.php');

/**
 * Unit tests for the fileresponse question definition class.
 *
 * @package    qtype_fileresponse
 * @copyright  2022 Luca Bösch, BFH Bern University of Applied Sciences luca.boesch@bfh.ch
 * @copyright  based on work by 2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class question_test extends \advanced_testcase {

    /**
     * Makes a qtype_sc question.
     * @return qtype_kprime
     */
    public function make_a_fileresponse_question() {
        \question_bank::load_question_definition_classes('fileresponse');
        $fileresponse = new qtype_fileresponse_question();
        \test_question_maker::initialise_a_question($fileresponse);
        $fileresponse->name = 'Essay question';
        $fileresponse->questiontext = 'Write an essay.';
        $fileresponse->generalfeedback = 'I hope you wrote an interesting essay.';
        $fileresponse->penalty = 0;
        $fileresponse->qtype = \question_bank::get_qtype('fileresponse');

        $fileresponse->responseformat = 'editor';
        $fileresponse->responserequired = 1;
        $fileresponse->responsefieldlines = 15;
        $fileresponse->attachments = 0;
        $fileresponse->attachmentsrequired = 0;
        $fileresponse->responsetemplate = '';
        $fileresponse->responsetemplateformat = FORMAT_MOODLE;
        $fileresponse->graderinfo = '';
        $fileresponse->graderinfoformat = FORMAT_MOODLE;

        return $fileresponse;
    }

    /**
     * Test the behaviour of get_question_summary() method.
     *
     * @covers ::get_question_summary
     */
    public function test_get_question_summary(): void {
        $fileresponse = $this->make_a_fileresponse_question();
        $fileresponse->questiontext = 'Hello <img src="http://example.com/globe.png" alt="world" />';
        $this->assertEquals('Hello [world]', $fileresponse->get_question_summary());
    }

    /**
     * Test the behaviour of summarise_response() method.
     *
     * @covers ::summarise_response
     */
    public function test_summarise_response(): void {
        $longstring = str_repeat('0123456789', 50);
        $fileresponse = $this->make_a_fileresponse_question();
        $this->assertEquals($longstring, $fileresponse->summarise_response(
                ['answer' => $longstring, 'answerformat' => FORMAT_HTML]));
    }

    /**
     * Test the behaviour of summarise_response() method.
     *
     * @covers ::is_same_response()
     */
    public function test_is_same_response(): void {
        $fileresponse = $this->make_a_fileresponse_question();

        $fileresponse->responsetemplate = '';

        $fileresponse->start_attempt(new \question_attempt_step(), 1);

        $this->assertTrue($fileresponse->is_same_response(
                [],
                ['answer' => '']));

        $this->assertTrue($fileresponse->is_same_response(
                ['answer' => ''],
                ['answer' => '']));

        $this->assertTrue($fileresponse->is_same_response(
                ['answer' => ''],
                []));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => 'Hello'],
                []));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => 'Hello'],
                ['answer' => '']));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => 0],
                ['answer' => '']));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => ''],
                ['answer' => 0]));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => '0'],
                ['answer' => '']));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => ''],
                ['answer' => '0']));
    }

    /**
     * Test the behaviour of is_same_response() method.
     *
     * @covers ::is_same_response()
     */
    public function test_is_same_response_with_template(): void {
        $fileresponse = $this->make_a_fileresponse_question();

        $fileresponse->responsetemplate = 'Once upon a time';

        $fileresponse->start_attempt(new \question_attempt_step(), 1);

        $this->assertTrue($fileresponse->is_same_response(
                [],
                ['answer' => 'Once upon a time']));

        $this->assertTrue($fileresponse->is_same_response(
                ['answer' => ''],
                ['answer' => 'Once upon a time']));

        $this->assertTrue($fileresponse->is_same_response(
                ['answer' => 'Once upon a time'],
                ['answer' => '']));

        $this->assertTrue($fileresponse->is_same_response(
                ['answer' => ''],
                []));

        $this->assertTrue($fileresponse->is_same_response(
                ['answer' => 'Once upon a time'],
                []));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => 0],
                ['answer' => '']));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => ''],
                ['answer' => 0]));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => '0'],
                ['answer' => '']));

        $this->assertFalse($fileresponse->is_same_response(
                ['answer' => ''],
                ['answer' => '0']));
    }

    /**
     * Test the behaviour of is_complete_response() method.
     *
     * @covers ::is_complete_response()
     */
    public function test_is_complete_response(): void {

        $fileresponse = $this->make_a_fileresponse_question();
        $fileresponse->start_attempt(new \question_attempt_step(), 1);

        // The empty string should be considered an empty response, as should a lack of a response.
        $this->assertFalse($fileresponse->is_complete_response(['answer' => '']));
        $this->assertFalse($fileresponse->is_complete_response([]));

        // Any nonempty string should be considered a complete response.
        $this->assertTrue($fileresponse->is_complete_response(['answer' => 'A student response.']));
        $this->assertTrue($fileresponse->is_complete_response(['answer' => '0 times.']));
        $this->assertTrue($fileresponse->is_complete_response(['answer' => '0']));
    }
}
