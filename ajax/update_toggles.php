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
 * Updating the user preferences with the current toggle state of all sections in the course
 *
 * @package    block_assessment_information2
 * @copyright  2022 Matthias Opitz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_login();

/**
 * Update the toggle status for each module type in the Assessment Overview block to the users preferences
 *
 * @param int $courseid
 * @param string $togglestate
 * @return mixed
 * @throws dml_exception
 */
function update_toggle_status($courseid, $togglestate) {
    global $DB, $USER;

    $name = "ao_toggle_state_".$courseid;
    if ($DB->record_exists('user_preferences', array('userid' => $USER->id, 'name' => $name))) {
        $toggleseqrecord = $DB->get_record('user_preferences', array('userid' => $USER->id, 'name' => $name));
        $toggleseqrecord->value = $togglestate;
        $DB->update_record('user_preferences', $toggleseqrecord);
    } else {
        $toggleseqrecord = new \stdClass();
        $toggleseqrecord->userid = $USER->id;
        $toggleseqrecord->name = $name;
        $toggleseqrecord->value = $togglestate;
        $DB->insert_record('user_preferences', $toggleseqrecord);
    }
    return $togglestate;
}

require_sesskey();

$courseid = required_param('courseid', PARAM_INT);
$togglestate = required_param('toggle_state', PARAM_RAW);

if (!isset($togglestate) || count(str_split($togglestate)) === 0) {
    exit;
}

echo update_toggle_status($courseid, $togglestate);
//echo $courseid;
