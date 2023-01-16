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
 * Unit tests for the fileresponse question type class.
 *
 * @package    qtype_fileresponse
 * @copyright  2022 Luca Bösch, BFH Bern University of Applied Sciences luca.boesch@bfh.ch
 * @copyright  based on work by 2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_fileresponse;

use advanced_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/fileresponse/questiontype.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/fileresponse/edit_fileresponse_form.php');


/**
 * Unit tests for the fileresponse question type class.
 *
 * @copyright  2022 Luca Bösch, BFH Bern University of Applied Sciences luca.boesch@bfh.ch
 * @copyright  based on work by 2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_type_test  extends \advanced_testcase {
    protected $qtype;

    /**
     * explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected function setUp(): void {
        $this->qtype = new \qtype_fileresponse();
    }

    /**
     * explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected function tearDown(): void {
        $this->qtype = null;
    }

    /**
     * explained here https://docs.moodle.org/dev/Unit_test_API
     * @var array
     */
    protected function get_test_question_data() {
        $q = new \stdClass();
        $q->id = 1;

        return $q;
    }

    /**
     * Test the name property of the question type.
     *
     * @covers ::name()
     */
    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'fileresponse');
    }

    /**
     * Test the can analyse responses function of the question type.
     *
     * @covers ::can_analyse_responses()
     */
    public function test_can_analyse_responses() {
        $this->assertFalse($this->qtype->can_analyse_responses());
    }

    /**
     * Test the get random guess score function of the question type.
     *
     * @covers ::get_random_guess_score()
     */
    public function test_get_random_guess_score() {
        $q = $this->get_test_question_data();
        $this->assertEquals(0, $this->qtype->get_random_guess_score($q));
    }

    /**
     * Test the get possible responses function of the question type.
     *
     * @covers ::get_possible_responses()
     */
    public function test_get_possible_responses() {
        $q = $this->get_test_question_data();
        $this->assertEquals(array(), $this->qtype->get_possible_responses($q));

    }
}
