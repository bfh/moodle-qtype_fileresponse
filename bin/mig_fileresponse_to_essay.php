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
 * @package     qtype
 * @subpackage  fileresponse
 * @copyright   2012 Luca Bösch luca.boesch@bfh.ch
 * @author      Martin Hanusch (martin.hanusch@let.ethz.ch)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/question/type/fileresponse/lib.php');

// Getting parameters.
$courseid = optional_param('courseid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$all = optional_param('all', 0, PARAM_INT);
$dryrun = optional_param('dryrun', 1, PARAM_INT);
$includesubcategories = optional_param('includesubcategories', 0, PARAM_INT);

@set_time_limit(0);
@ini_set('memory_limit', '3072M');

// General Page Setup.
echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' .
    '<style>body{font-family: "Courier New", Courier, monospace; font-size: 12px; background: #ebebeb; color: #5a5a5a;}</style>' .
    '</head>';
echo "=========================================================================================<br/>\n";
echo "M I G R A T I O N :: Fileresponse to Essay<br/>\n";
echo "=========================================================================================<br/>\n";

// Checking for permissions.
require_login();
if (!is_siteadmin()) {
    echo "<br/>[<font color='red'>ERR</font>] You are not a Website Administrator";
    die();
}

$starttime = microtime(1);
$fs = get_file_storage();

$sql = "SELECT q.*
        FROM {question} q
        WHERE q.qtype = 'fileresponse'
          AND q.parent = 0
          AND q.id in (select questionid from {qtype_fileresponse_options})
        ";
$params = array();

// Showing information when either no or too many parameters are selected.
$numparameters = ($all == 0 ? 0 : 1) + ($courseid == 0 ? 0 : 1) + ($categoryid == 0 ? 0 : 1);
if (($all != 1 && $courseid <= 0 && $categoryid <= 0) || $numparameters > 1 ) {
    echo "
    <br/>\nParameters:<br/><br/>\n\n
    =========================================================================================<br/>\n
    You must specify certain parameters for this script to work: <br/><br/>\n\n
    Step 1: <b>NECESSARY </b> - Use ONE of the following three parameters-value pairs:
    <ul>
        <li><b>courseid</b> (values: <i>a valid course ID</i>)</li>
        <li><b>categoryid</b> (values: <i>a valid category ID</i>)</b></li>
        <li><b>all</b> (values: 1)</li>
    </ul>
    This parameter-value pairs define which fileresponse questions will be migrated.<br/><br/>\n\n
    Step 2: <b>IMPORTANT AND STRONGLY RECOMMENDED:</b><br/>\n
    <ul>
        <li><b>dryrun</b> (values: <i>0,1</i>)</li>
        <li><b>includesubcategories</b> (values: <i>0,1</i>)</li>
    </ul>
    The Dryrun Option is enabled (1) by default.<br/>\n
    With Dryrun enabled no changes will be made to the database.<br/>\n
    Use Dryrun to receive information about possible issues before migrating.<br/><br/>\n\n
    The IncludeSubcategories Option also is disabled (0) by default.<br/>\n
    With includesubcategories enabled all subcategories will be included in the migration<br/>\n
    process, if the user chooses to migrate questions by selecting a certain category.<br/><br/>\n\n
    =========================================================================================<br/><br/>\n\n
    Examples:<br/><br/>\n\n
    =========================================================================================<br/>\n
    <ul>
        <li><strong>Migrate fileresponse Questions in a specific course</strong>:<br/>\n
        MOODLE_URL/question/type/fileresponse/bin/mig_fileresponse_to_essay.php?<b>courseid=55</b>
        <li><strong>Migrate fileresponse Questions in a specific category</strong>:<br/>\n
        MOODLE_URL/question/type/fileresponse/bin/mig_fileresponse_to_essay.php?<b>categoryid=1</b>
        <li><strong>Migrate all fileresponse Questions</strong>:<br/>\n
        MOODLE_URL/question/type/fileresponse/bin/mig_fileresponse_to_essay.php?<b>all=1</b>
        <li><strong>Disable Dryrun</strong>:<br/>\n
        MOODLE_URL/question/type/fileresponse/bin/mig_fileresponse_to_essay.php?all=1<b>&dryrun=0</b>
        <li><strong>Enable IncludeSubcategories</strong>:<br/>\n
        MOODLE_URL/question/type/fileresponse/bin/mig_fileresponse_to_essay.php?all=1&dryrun=0<b>&includesubcategories=1</b>
    </ul>
    <br/>\n";
    die();
}

// Parameter Information.
echo "-----------------------------------------------------------------------------------------<br/><br/>\n\n";
echo ($dryrun == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "Dryrun: " . ($dryrun == 1 ? "NO changes to the database will be made!" : "Migration is being processed") . "<br/>\n";
echo ($includesubcategories == 1 ? "[<font style='color:#228d00;'>ON </font>] " : "[<font color='red'>OFF</font>] ") .
    "IncludeSubcategories<br/><br/>\n\n";
echo "-----------------------------------------------------------------------------------------<br/>\n";
echo "=========================================================================================<br/>\n";

// Get the categories : Case 1.
if ($all == 1) {
    if ($categories = $DB->get_records('question_categories', array())) {
        echo "Migration of all Fileresponse Questions<br/>\n";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] Could not get categories<br/>\n";
        die();
    }
}
// Get the categories : Case 2.
if ($courseid > 0) {
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        echo "<br/>[<font color='red'>ERR</font>] Course with ID " . $courseid . " not found<br/>\n";
        die();
    }

    $coursecontext = context_course::instance($courseid);

    $categories = $DB->get_records('question_categories',
            array('contextid' => $coursecontext->id
            ));
    $catids = array_keys($categories);
    if (!empty($catids)) {
        echo "Migration of Filereponse Questions within courseid " . $courseid . " <br/>\n";
        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] No question categories for course found.<br/>\n";
        die();
    }
}

// Get the categories : Case 3.
if ($categoryid > 0) {
    if ($categories[$categoryid] = $DB->get_record('question_categories', array('id' => $categoryid))) {

        $catids = [];

        if ($includesubcategories == 1) {
            $subcategories = get_subcategories($categoryid);
            $catids = array_column($subcategories, 'id');
            $catnames = array_column($subcategories, 'name');
        }

        array_push($catids, $categoryid);

        echo 'Migration of Filereponse questions within category "' . $categories[$categoryid]->name . "\"<br/>\n";

        if ($includesubcategories == 1) {
            echo "Also migrating subcategories:<br>\n";
            echo "- " . implode(",<br>", $catnames) . "<br>\n";
            echo "=========================================================================================<br/>\n";
        }

        list($csql, $params) = $DB->get_in_or_equal($catids);
        $sql .= " AND category $csql ";
    } else {
        echo "<br/>[<font color='red'>ERR</font>] Question category with ID " . $categoryid . " not found<br/>\n";
        die();
    }
}

// Get the questions based on the previous set parameters.
$questions = $DB->get_records_sql($sql, $params);
echo 'Questions found: ' . count($questions) . "<br/>\n";
echo "=========================================================================================<br/><br/>\n\n";
if (count($questions) == 0) {
    echo "<br/>[<font color='red'>ERR</font>] No questions found<br/>\n";
    die();
}

// Processing the single questions.
echo "Migrating questions...<br/>\n";
$nummigrated = 0;
$questionsnotmigrated = [];

foreach ($questions as $oldquestion) {
    set_time_limit(600);

    // Getting related question data.
    $oldoption = $DB->get_record('qtype_fileresponse_options', array('questionid' => $oldquestion->id));

    if ($oldoption == false || !isset($oldoption)) {
        echo "[<font color='red'>ERR</font>] No entry found for " . $oldquestion->id . " in mdl_qtype_fileresponse_options<br/>\n";
        continue;
    }

    // Get contextid from question category.
    $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $oldquestion->category));

    if ($contextid == false || !isset($contextid)) {
        echo "<br/>[<font color='red'>ERR</font>] No context id found for question " . $oldquestion->id;
        continue;
    }

    // Pretesting files

    $success = 1;
    $status = "";

    // Test mdl_question->questiontext media.
    $testresult = test_files(
        $fs,
        $contextid,
        $oldquestion->id,
        $oldquestion->questiontext,
        "questiontext",
        "question");

    $success = $success && $testresult[0];
    $status .= $testresult[1];

    // Test mdl_question->generalfeedback media.
    $testresult = test_files(
        $fs,
        $contextid,
        $oldquestion->id,
        $oldquestion->generalfeedback,
        "generalfeedback",
        "question");

    $success = $success && $testresult[0];
    $status .= $testresult[1];

    // Copy mdl_fileresponse_options->graderinfo media.
    $testresult = test_files(
        $fs,
        $contextid,
        $oldoption->questionid,
        trim($oldoption->graderinfo),
        "graderinfo",
        "qtype_fileresponse");

    $success = $success && $testresult[0];
    $status .= $testresult[1];

    if ($success) {
        $nummigrated++;
    }

    if ($dryrun == 0 && $success) {
        try {

            unset($transaction);
            $transaction = $DB->start_delegated_transaction();

            // Processing mdl_question -> mdl_question.
            unset($newquestion);
            $newquestion = new stdClass();
            $newquestion->category = $oldquestion->category;
            $newquestion->parent = 0;
            $newquestion->name = substr($oldquestion->name . " (Essay " . date("Y-m-d H:i:s") . ")", 0, 255);
            $newquestion->questiontext = trim($oldquestion->questiontext);
            $newquestion->questiontextformat = $oldquestion->questiontextformat;
            $newquestion->generalfeedback = trim($oldquestion->generalfeedback);
            $newquestion->generalfeedbackformat = $oldquestion->generalfeedbackformat;
            $newquestion->defaultmark = $oldquestion->defaultmark;
            $newquestion->penalty = $oldquestion->penalty;
            $newquestion->qtype = "essay";
            $newquestion->length = $oldquestion->length;
            $newquestion->stamp = make_unique_id_code();
            $newquestion->version = make_unique_id_code();
            $newquestion->hidden = $oldquestion->hidden;
            $newquestion->timecreated = time();
            $newquestion->timemodified = time();
            $newquestion->createdby = $USER->id;
            $newquestion->modifiedby = $USER->id;
            $newquestion->idnumber = null;
            $newquestion->id = $DB->insert_record('question', $newquestion);

            // Copy mdl_question->questiontext media.
            copy_files(
                $fs,
                $contextid,
                $oldquestion->id,
                $newquestion->id,
                $newquestion->questiontext,
                "questiontext",
                "question",
                "question",
                "questiontext");

            // Copy mdl_question->generalfeedback media.
            copy_files(
                $fs,
                $contextid,
                $oldquestion->id,
                $newquestion->id,
                $newquestion->generalfeedback,
                "generalfeedback",
                "question",
                "question",
                "generalfeedback");

            // Processing mdl_fileresponse_options -> mdl_essay_options.
            unset($newoption);

            $oldoption->responseformat = isset($oldoption->responseformat ) ? $oldoption->responseformat : 0;
            $oldoption->responsefieldlines = isset($oldoption->responsefieldlines) ? $oldoption->responsefieldlines : 0;
            $oldoption->attachments = isset($oldoption->attachments) ?  $oldoption->attachments : 0;
            $oldoption->graderinfoformat = isset($oldoption->graderinfoformat) ? $oldoption->graderinfoformat : 1;
            $oldoption->responsetemplateformat = isset($oldoption->responsetemplateformat) ? $oldoption->responsetemplateformat : 1;

            $newoption = new stdClass();
            $newoption->questionid = $newquestion->id;
            $newoption->responseformat = $oldoption->responsefieldlines == 0 ? 'noinline' : 'plain';
            $newoption->responserequired = 1;
            $newoption->responsefieldlines = $oldoption->responsefieldlines == 0 ? 5 : $oldoption->responsefieldlines;
            $newoption->attachments = $oldoption->attachments;
            $newoption->attachmentsrequired = $oldoption->attachments == -1 ? 1 : $oldoption->attachments;
            $newoption->graderinfo = trim($oldoption->graderinfo);
            $newoption->graderinfoformat = $oldoption->graderinfoformat;
            $newoption->responsetemplate = $oldoption->responsetemplate;
            $newoption->responsetemplateformat = $oldoption->responsetemplateformat;
            $newoption->filetypeslist = '*';
            $newoption->maxbytes = 0;
            $newoption->id = $DB->insert_record('qtype_essay_options', $newoption);

            // Copy mdl_fileresponse_options->graderinfo media.
            copy_files(
                $fs,
                $contextid,
                $oldoption->questionid,
                $newoption->questionid,
                $newoption->graderinfo,
                "graderinfo",
                "qtype_fileresponse",
                "qtype_essay",
                "graderinfo");

            // Copy tags.
            $tags = $DB->get_records_sql(
                "SELECT * FROM {tag_instance} WHERE itemid = :itemid",
                array('itemid' => $oldquestion->id));

            foreach ($tags as $tag) {
                $entry = new stdClass();
                $entry->tagid = $tag->tagid;
                $entry->component = $tag->component;
                $entry->itemtype = $tag->itemtype;
                $entry->itemid = $newquestion->id;
                $entry->contextid = $tag->contextid;
                $entry->tiuserid = $tag->tiuserid;
                $entry->ordering = $tag->ordering;
                $entry->timecreated = $tag->timecreated;
                $entry->timemodified = $tag->timemodified;
                $DB->insert_record('tag_instance', $entry);
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            $transaction->rollback($e);
        }
    }

    // Output: Question Migration Success.
    echo $success ? '[<font style="color:#228d00;">OK </font>]' : '[<font color="red">ERR</font>]';
    echo ' - question <i>"' . $oldquestion->name . '"</i> ' .
    '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $oldquestion->id .
    '" target="_blank">' . $oldquestion->id . '</a>) ';
    if ($dryrun == 0) {
        echo ($success) ? ' > <i>"' . $newquestion->name . '"</i> ' .
        '(ID: <a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $newquestion->id .
        '" target="_blank">' . $newquestion->id . '</a>)' : '';
    }
    if ($dryrun == 1) {
        echo ($success) ? " is migratable" : " is <u>not</u> migratable";
    }

    echo "<br/>$status\n";

}

// Showing final summary.
echo "<br/>\n";
echo "=========================================================================================<br/>\n";
echo count($questionsnotmigrated) > 0 ? "Not Migrated: <br/>" : null;
foreach ($questionsnotmigrated as $entry) {
    echo '<a href="' . $CFG->wwwroot . '/question/preview.php?id=' . $entry["id"] . '" target="_blank">' .
    $CFG->wwwroot . '/question/preview.php?id=' . $entry["id"] . "</a> - " . $entry["name"] . "<br/>\n";
}
echo "=========================================================================================<br/>\n";
echo "SCRIPT PROCESSED: Time needed: " . round(microtime(1) - $starttime, 4) . " seconds.<br/>\n";
echo $nummigrated . "/" . count($questions) . " question(s) " . ($dryrun == 1 ? "would be " : null) . "migrated.<br/>\n";
echo "=========================================================================================<br/>\n";
die();

// Getting the subcategories of a certain category.
function get_subcategories($categoryid) {
    global $DB;

    $subcategories = $DB->get_records('question_categories', array('parent' => $categoryid), 'id');

    foreach ($subcategories as $subcategory) {
        $subcategories = array_merge($subcategories, get_subcategories($subcategory->id));
    }

    return $subcategories;
}

function get_image_filenames($text) {
    $result = array();
    $strings = preg_split("/<img|<source/i", $text);
    foreach ($strings as $string) {
        $matches = array();
        if (preg_match('!@@PLUGINFILE@@/(.+)!u', $string, $matches) && count($matches) > 0) {
            $filename = mb_substr($matches[1], 0, mb_strpos($matches[1], '"'));
            $filename = urldecode($filename);
            $result[] = $filename;
        }
    }
    return $result;
}

// Copying files from one question to another.
function copy_files($fs, $contextid, $oldid, $newid, $text, $type, $oldcomponent, $newcomponent, $filearea) {
    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {

        $parsed_filename_url = parse_url($filename)["path"];
        if (isset($parsed_filename_url)) {
            $filename = $parsed_filename_url;
        }

        $file = $fs->get_file($contextid, $oldcomponent, $type, $oldid, '/', $filename);

        if ($file) {
            $newfile = new stdClass();
            $newfile->component = $newcomponent;
            $newfile->filearea = $filearea;
            $newfile->itemid = $newid;
            if (!$fs->get_file($contextid, $newfile->component, $newfile->filearea, $newfile->itemid, '/', $filename)) {
                $fs->create_file_from_storedfile($newfile, $file);
            }
        }
    }
}

// Testing files
function test_files($fs, $contextid, $oldid, $text, $type, $olcdomponent) {

    $success = 1;
    $message = "";

    $filenames = get_image_filenames($text);
    foreach ($filenames as $filename) {

        $parsed_filename_url = parse_url($filename)["path"];
        if (isset($parsed_filename_url)) {
            $filename = $parsed_filename_url;
        }

        $file = $fs->get_file($contextid, $olcdomponent, $type, $oldid, '/', $filename);
        if (!$file) {
            $success = 0;
            $message .= "- File <font color='red'>$filename</font> not found in <u>$type</u><br>";
        }
    }

    return ["0" => $success, "1" => $message];
}