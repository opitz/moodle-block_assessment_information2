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
 * Block Assessment Information 2.
 *
 * @package    block_assessment_information2
 * @copyright  2022 Queen Mary University of London (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block Assessment Information 2.
 *
 * @copyright  2022 Queen Mary University of London (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_assessment_information2 extends block_base {

    /**
     * Return the config status
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Initialize this
     *
     * @return void
     * @throws coding_exception
     */
    public function init() {
        global $PAGE;
        $this->title = get_string('pluginname', 'block_assessment_information2');
        $this->description = get_string('description', 'block_assessment_information2');

        $PAGE->requires->js_call_amd('block_assessment_information2/toggle', 'init', array());

    }

    /**
     * Get the content and return it
     *
     * @return stdClass|stdObject|null
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_content() {
        global $COURSE, $DB;
//        $this->page->requires->js_call_amd('block_assessment_information2/toggle', 'init', array());

        $course = $this->page->course;

        $o = '';
        // A hidden dev containing the course ID to be used by jQuery.
        $o .= html_writer::div($COURSE->id, 'hidden', ['id' => 'ao-courseid', 'style' => 'display: none;']);

        // A test area
//        $o .= html_writer::div('test area','testarea', ['id' => 'testarea']);

        // Use a list of all assessment types if no overriding type array is given in the config file.
        $types = $this->get_module_types();

        // Get the toggle state of module types for the current user.
        $this->togglestate = $this->get_togglestate();

        // Loop through the types and list all their module instances related to this course.
        $dbman = $DB->get_manager();
        foreach ($types as $type) {
            if ($dbman->table_exists($type)) {
                $o .= $this->list_modules_by_type($course->id, $type);
            }
        }

        $this->content = new stdClass();
        $this->content->text = $o;
        return $this->content;
    }

    private function get_module_types() {
        if (isset($this->config)) {
            // If there is a saved configuration of types to show use that.
            $result = [];
            $config_types = $this->config;
            foreach ($config_types as $key => $value) {
                if ($value === 1) {
                    $result[] = $key;
                }
            }
        } else {
            $default_types = get_config('block_assessment_information2', 'default_types');
            $result = array_map('trim', explode(',', $default_types));
        }

        return $result;
    }

    /**
     * List the modules of a given course and type
     *
     * @param int $courseid
     * @param string $mtype
     * @return string
     * @throws coding_exception
     */
    private function list_modules_by_type(int $courseid, string $mtype) {
        $o = '';
        // If the course has modules of a given type show them.
        if ($modules = $this->get_modules($courseid, $mtype)) {
            // Get the name of the module type from the pluginname in the string file where present
            // otherwise use the mtype with a capital 1st char.
            $mname = get_string('pluginname', 'mod_'.$mtype) == '[[pluginname]]' ?
                ucfirst($mtype) :
                get_string('pluginname', 'mod_'.$mtype);

            $o .= html_writer::start_div('type-'.$mtype);
            $o .= html_writer::start_div('ai2-header');

/*            if ($this->togglestate[$mname] == 0) {
                $classes = 'icon fa fa-caret-right';
            } else {
                $classes = 'icon fa fa-caret-down';
            }
*/
            $this->togglestate[$mname] == 0 ? $classes = 'icon fa fa-caret-right' : $classes = 'icon fa fa-caret-down';

//            $o .= html_writer::tag('i','', ['class' => 'icon fa fa-caret-down']);
            $o .= html_writer::tag('i','', ['class' => $classes]);
            $o .= html_writer::span($mname, 'mname', ['id' => 'ai2-header-'.$mtype]);
            $o .= html_writer::end_div();

            $dateformat = "%d %B %Y";

            // Make a list.
            $this->togglestate[$mname] == 0 ?
                $o .= html_writer::start_tag('ul',
                    ['class' => 'content ai2-content-'.$mtype, 'style' => 'display: none;']) :
                $o .= html_writer::start_tag('ul',
                    ['class' => 'content ai2-content-'.$mtype])
            ;

            foreach ($modules as $module) {
                $o .= html_writer::start_tag('li');
                $o .= html_writer::tag('a', $module->name,
                    ['href' => '../mod/'.$mtype.'/view.php?id='.$module->cmid, 'target' => '_blank']);

                // Deal with different due dates from types.
                switch ($mtype) {
                    case 'assign':
                        $duedate = $module->duedate;
                        break;
                    case 'lesson':
                        $duedate = $module->deadline;
                        break;
                    default:
                        $duedate = $module->timeclose;
                        break;
                }
                if ($duedate > 0) {
                    $o .= html_writer::div('Due: '. userdate($duedate, $dateformat), 'ao-duedate');
                }
                $o .= html_writer::end_tag('li');
            }
            $o .= html_writer::end_tag('ul');
            $o .= html_writer::end_div();
        }
        return $o;
    }

    /**
     * Get all modules from a course of a given type
     *
     * @param int $courseid
     * @param string $type
     * @return array
     * @throws dml_exception
     */
    private function get_modules(int $courseid, string $mtype) {
        global $DB;
        $sql = "
            select
            cm.id as cmid
            , m.*
            from {course_modules} cm
            join {".$mtype."} m on m.id = cm.instance and m.course = cm.course
            where cm.course = $courseid
        ";
        $result = $DB->get_records_sql($sql);
        return $result;
    }

    /**
     * Get the toggle state of a given course for the current user.
     *
     * @param array|stdClass $course
     * @return array
     * @throws dml_exception
     */
    private function get_togglestate() {
        global $COURSE, $DB, $USER;

        $record = $DB->get_record('user_preferences', array('userid' => $USER->id, 'name' => 'ao_toggle_state_'.$COURSE->id));
        if (!isset($record->value)) {
            return array();
        }
        // Prepare the toggle state.
        $togglestate = (array) json_decode($record->value);

        // Weird rearranging the array due to error with PHP below version 7.2.
        // NO idea why this is needed - but it works.
        if (version_compare(PHP_VERSION, '7.2.0') < 0) {
            $togglestate2 = array();
            foreach ($togglestate as $key => $value) {
                $togglestate2[$key] = $value;
            }
            $togglestate = $togglestate2;
        }
        return $togglestate;
    }

}


