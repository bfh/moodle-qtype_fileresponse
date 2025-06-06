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
 * File response question definition class.
 *
 * @package    qtype_fileresponse
 * @copyright  2012 Luca Bösch luca.boesch@bfh.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Commenting.VariableComment.Missing
// phpcs:disable moodle.Commenting.MissingDocblock.Function
// phpcs:disable moodle.Commenting.DocblockDescription.Missing
// phpcs:disable Generic.Arrays.DisallowLongArraySyntax

/**
 * Represents a fileresponse question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_fileresponse_question extends question_with_responses {

    public $responseformat;

    /** @var int Indicates whether an inline response is required ('0') or optional ('1')  */
    public $responserequired;

    public $responsefieldlines;
    public $attachments;

    /** @var int The number of attachments required for a response to be complete. */
    public $attachmentsrequired;

    /** @var int Whether to add ?forcedownload=1 to file links. */
    public $forcedownload;

    /** @var int Whether to allow file picker repositories. */
    public $allowpickerplugins;

    public $graderinfo;
    public $graderinfoformat;
    public $responsetemplate;
    public $responsetemplateformat;

    /** @var array The string array of file types accepted upon file submission. */
    public $filetypeslist;

    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        return question_engine::make_behaviour('manualgraded', $qa, $preferredbehaviour);
    }

    /**
     * @param moodle_page $page the page we are outputting to.
     * @return qtype_fileresponse_format_renderer_base the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_fileresponse', 'format_' . $this->responseformat);
    }

    public function get_expected_data() {
        if ($this->responseformat == 'editorfilepicker') {
            $expecteddata = array('answer' => question_attempt::PARAM_RAW_FILES);
        } else {
            $expecteddata = array('answer' => PARAM_RAW);
        }
        $expecteddata['answerformat'] = PARAM_ALPHANUMEXT;
        if ($this->attachments != 0) {
            $expecteddata['attachments'] = question_attempt::PARAM_FILES;
        }
        return $expecteddata;
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            /* Return a list of the files' uploaded names. */
            return html_to_text(format_text($response['answer'], FORMAT_HTML, array('para' => false)), 0, false);
        } else {
            return null;
        }
    }

    public function un_summarise_response(string $summary) {
        if (!empty($summary)) {
            return ['answer' => text_to_html($summary)];
        } else {
            return [];
        }
    }

    public function get_correct_response() {
        return null;
    }

    public function is_complete_response(array $response) {
        // Determine if the given response has online text and attachments.
        $hasinlinetext = array_key_exists('answer', $response) && ($response['answer'] !== '');
        $hasattachments = array_key_exists('attachments', $response)
            && $response['attachments'] instanceof question_response_files;

        // Determine the number of attachments present.
        if ($hasattachments) {
            // Check the filetypes.
            $filetypesutil = new \core_form\filetypes_util();
            $allowlist = $filetypesutil->normalize_file_types($this->filetypeslist);
            $wrongfiles = array();
            foreach ($response['attachments']->get_files() as $file) {
                if (!$filetypesutil->is_allowed_file_type($file->get_filename(), $allowlist)) {
                    $wrongfiles[] = $file->get_filename();
                }
            }
            if ($wrongfiles) { // At least one filetype is wrong.
                return false;
            }
            $attachcount = count($response['attachments']->get_files());
        } else {
            $attachcount = 0;
        }

        // Determine if we have /some/ content to be graded.
        $hascontent = $hasinlinetext || ($attachcount > 0);

        // Determine if we meet the optional requirements.
        $meetsinlinereq = $hasinlinetext || (!$this->responserequired) || ($this->responseformat == 'noinline');
        $meetsattachmentreq = ($attachcount >= $this->attachmentsrequired);

        // The response is complete iff all of our requirements are met.
        return $hascontent && $meetsinlinereq && $meetsattachmentreq;
    }

    public function is_gradable_response(array $response) {
        // Determine if the given response has online text and attachments.
        if (array_key_exists('answer', $response) && ($response['answer'] !== '')) {
            return true;
        } else if (array_key_exists('attachments', $response)
            && $response['attachments'] instanceof question_response_files) {
            return true;
        } else {
            return false;
        }
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        if (array_key_exists('answer', $prevresponse) && $prevresponse['answer'] !== $this->responsetemplate) {
            $value1 = (string) $prevresponse['answer'];
        } else {
            $value1 = '';
        }
        if (array_key_exists('answer', $newresponse) && $newresponse['answer'] !== $this->responsetemplate) {
            $value2 = (string) $newresponse['answer'];
        } else {
            $value2 = '';
        }
        return $value1 === $value2 && ($this->attachments == 0 ||
                question_utils::arrays_same_at_key_missing_is_blank(
                    $prevresponse, $newresponse, 'attachments'));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'response_attachments') {
            // Response attachments visible if the question has them.
            return $this->attachments != 0;

        } else if ($component == 'question' && $filearea == 'response_answer') {
            // Response attachments visible if the question has them.
            return $this->responseformat === 'editorfilepicker';

        } else if ($component == 'qtype_fileresponse' && $filearea == 'graderinfo') {
            return $options->manualcomment && $args[0] == $this->id;

        } else {
            return parent::check_file_access($qa, $options, $component,
                $filearea, $args, $forcedownload);
        }
    }
}
