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
 * Newblock block caps.
 *
 * @package    block_assessment_information2
 * @copyright  2022 Queen Mary University of London (m.opitz@qmul.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Redirect Landingpage Block
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
        $this->title = get_string('pluginname', 'block_assessment_information2');
        $this->description = get_string('description', 'block_assessment_information2');
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
        global $CFG;
        $course = $this->page->course;

        $o = '';

        isset($CFG->ai_types) ? $types = $CFG->ai_types : $types = ['assign', 'choice', 'feedback', 'lesson', 'quiz'];

        foreach ($types as $type) {
            $o .= $this->list_modules($course->id, $type);
        }

        $this->content = new stdClass();
        $this->content->text = $o;
        return $this->content;
    }

    /**
     * List the modules of a given course and type
     *
     * @param int $courseid
     * @param string $type
     * @return string
     * @throws coding_exception
     */
    private function list_modules(int $courseid, string $type) {
        $o = '';
        // If the course has modules of a given type show them.
        if ($modules = $this->get_modules($courseid, $type)) {
            $o .= html_writer::div(get_string($type, 'block_assessment_information2'), 'ai2-header');

            // Make a list.
            $o .= "<ul>";
            foreach ($modules as $module) {
                $o .= html_writer::start_tag('li');
                $o .= html_writer::tag('a', $module->name, ['href' => '../mod/'.$type.'/view.php?id='.$module->id, 'target' => '_blank']);
                $o .= html_writer::end_tag('li');
            }
            $o .= "</ul>";
            return $o;
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
    private function get_modules(int $courseid, string $type) {
        global $DB;
        $sql = "
            select
            cm.id
            , m.name
            from {course_modules} cm
            join {".$type."} m on m.id = cm.instance and m.course = cm.course
            where cm.course = $courseid
        ";
        $result = $DB->get_records_sql($sql);
        return $result;
    }

}


