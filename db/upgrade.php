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
 * Fileresponse question type upgrade code.
 *
 * @package    qtype_fileresponse
 * @copyright  2012 Luca Bösch luca.boesch@bfh.ch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the fileresponse question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_fileresponse_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019120200) {

        // Add "filetypeslist" column to the question type options to save the allowed file types.
        $table = new xmldb_table('qtype_fileresponse_options');
        $field = new xmldb_field('filetypeslist', XMLDB_TYPE_TEXT, null, null, null, null, null, 'responsetemplateformat');

        // Conditionally launch add field filetypeslist.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Fileresponse savepoint reached.
        upgrade_plugin_savepoint(true, 2019120200, 'qtype', 'fileresponse');
    }

    return true;
}
