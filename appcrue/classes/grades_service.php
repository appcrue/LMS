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

namespace local_appcrue;
use grade_item;
use grade_grade;
/**
 * Class grades_service
 *
 * @package    local_appcrue
 * @copyright  2025 Alberto Otero Mato <alberto.otero@altia.es> Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grades_service extends appcrue_service {
    /**
     * Get data response.
     */
    public function get_data_response() {
        // Get the items and count.
        $items = $this->get_items();
        $count = count($items);
        // Return the items and count.
        return [ [ 'grades' => $items ], $count];
    }
    /**
     * Get grades for a user.
     * @return array Array of grade items for the user.
     */
    public function get_items() {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');
        $courses = enrol_get_users_courses($this->user->id, true);
        $grades = [];

        foreach ($courses as $course) {
            $items = grade_item::fetch_all(['courseid' => $course->id]);

            if (!$items) {
                continue;
            }

            foreach ($items as $item) {
                // Only include if the item is visible to the student.
                if ($item->is_hidden()) {
                    continue;
                }

                // Get final grade for this user.
                $grade = new grade_grade([
                    'itemid' => $item->id,
                    'userid' => $this->user->id,
                ]);

                if (is_null($grade->finalgrade)) {
                    continue; // No grade available yet.
                }

                $grades[] = [
                    'courseid' => $course->id,
                    'coursename' => $course->fullname,
                    'itemname' => html_entity_decode(strip_tags($item->get_name()), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'itemtype' => $item->itemtype,
                    'graderaw' => $grade->rawgrade,
                    'finalgrade' => $grade->finalgrade,
                    'gradeformatted' => html_entity_decode(
                        strip_tags(\grade_format_gradevalue($grade->finalgrade, $item)),
                        ENT_QUOTES | ENT_HTML5,
                        'UTF-8'
                    ),
                    'gradeisoverridden' => $grade->overridden ? 'TRUE' : 'FALSE',
                    'gradedategraded' => $grade->timemodified ?? 0,
                    'feedback' => html_entity_decode(strip_tags($grade->feedback ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'userid' => $this->user->id,
                ];
            }
        }
        return $grades;
    }
}
