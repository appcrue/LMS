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

/**
 * Class announcements_service
 *
 * @package    local_appcrue
 * @copyright  2025 Alberto Otero Mato <alberto.otero@altia.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class announcements_service extends appcrue_service {
    /**
     * Get data response.
     */
    public function get_data_response() {
        // Get the items and count.
        $items = $this->get_items();
        $count = count($items);
        // Return the items and count.
        return [ [ 'announcements' => $items ], $count];
    }
    /**
     * Get announcements for a user.
     * @return array Array of announcements for the user.
     */
    public function get_items() {
        global $DB, $USER;
    
        // Obtener los cursos donde el usuario está inscrito.
        $courses = enrol_get_users_courses($USER->id, true, '*');
    
        $results = [];
        
        foreach ($courses as $course) {
            // Buscar foros de tipo 'news' (foro de anuncios).
            $newsforums = $DB->get_records('forum', ['course' => $course->id, 'type' => 'news']);

            foreach ($newsforums as $forum) {
                // Obtener las discusiones del foro (cada discusión = un anuncio).
                $discussions = $DB->get_records('forum_discussions', ['forum' => $forum->id], 'timemodified DESC', '*', 0, 10);

                foreach ($discussions as $discussion) {
                    // Obtener el primer post (el anuncio principal).
                    $post = $DB->get_record('forum_posts', ['discussion' => $discussion->id, 'parent' => 0]);

                    if (!$post) continue;

                    $author = \core_user::get_user($post->userid);
                    $results[] = [
                        'courseid' => $course->id,
                        'coursefullname' => $course->fullname,
                        'forumid' => $forum->id,
                        'forumname' => $forum->name,
                        'subject' => format_string($post->subject),
                        'message' => html_entity_decode(strip_tags($post->message), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'author' => fullname($author),
                        'timecreated' => $post->created,
                        'url' => (string) new \moodle_url('/mod/forum/discuss.php', ['d' => $discussion->id]),
                    ];
                }
            }
        }

        return $results;
    }
}
