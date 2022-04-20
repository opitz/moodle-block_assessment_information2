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
 * Edit Form details
 *
 * @package    block_assessment_information2
 * @copyright  2022 Queen Mary University of London (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_assessment_information2_edit_form extends block_edit_form {

    /**
     * Set the specific definition for the given $mform
     *
     * @param {object} $mform
     * @return void
     * @throws coding_exception
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        /*
        // A sample string variable with a default value.
        $mform->addElement('text', 'config_text', get_string('blockstring', 'block_assessment_information2'));
        $mform->setDefault('config_text', 'default value');
        $mform->setType('config_text', PARAM_TEXT);

        // Define module types to include by default if no overriding type array is given in the config file.
        isset($CFG->ai_default_types) ? $default = $CFG->ai_default_types : $default = [
            'assign',
            'book',
            'chat',
            'choice',
            'feedback',
            'forum',
            'lesson',
            'quiz',
        ];
*/
        $mtypes = $this->get_module_types();
        $default_types = get_config('block_assessment_information2', 'default_types');
        $default = array_map('trim', explode(',', $default_types));

        if($mtypes) foreach ($mtypes as $mtype) {
            $mname = get_string('pluginname', 'mod_'.$mtype) == '[[pluginname]]' ?
                ucfirst($mtype) :
                get_string('pluginname', 'mod_'.$mtype) . "($mtype)";

            $mform->addElement('advcheckbox',
                'config_'.$mtype,
                $mname,
                null,
                null,
                array(0, 1));
            if (in_array($mtype, $default)) {
                $mform->setDefault('config_'.$mtype, 1);
            } else {
                $mform->setDefault('config_'.$mtype, 0);
            }
            $mform->setType('config_'.$mtype, PARAM_INT);
        }

    }

    private function get_module_types() {
        global $DB;

        $result = array();
        $records = $DB->get_records('modules', null, 'name');
        if ($records) foreach ($records as $record) {
            $result[] = $record->name;
        }
        return $result;
    }

}
