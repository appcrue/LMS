<?php
namespace local_appcrueservices\external;

use core_text;
use context_module;
use moodle_url;
use ReflectionFunction;

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
 * Returns a WS token for a given user, always regenerating a new one.
 *
 * @package   local_appcrueservices
 * @author    Alberto Otero Mato
 * @copyright 2025 alberto.otero@altia.es
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');
require_once($CFG->dirroot . '/mod/forum/locallib.php');

class forums extends \external_api {

    public static function get_forums_parameters() {
        return new \external_function_parameters([
            'studentemail' => new \external_value(PARAM_EMAIL, 'Email del estudiante'),
            'apikey' => new \external_value(PARAM_RAW, 'API Key de autenticación')
        ]);
    }
    
    private static function build_post_tree(array &$postmap, string $parent_id = "0"): array {
        $tree = [];
    
        foreach ($postmap as $post_id => $post) {
            if ($post['parent_id'] === $parent_id) {
                $children = self::build_post_tree($postmap, $post['id']);
                $post['replies'] = $children;
                $tree[] = $post;
            }
        }
    
        return $tree;
    }

    public static function get_forums($studentemail, $apikey) {
        global $DB, $CFG, $USER;

        self::validate_parameters(self::get_forums_parameters(), [
            'studentemail' => $studentemail,
            'apikey' => $apikey
        ]);

        $studentemail = trim(core_text::strtolower($studentemail));

        $stored_apikey = get_config('local_appcrueservices', 'apikey');
        if (empty($stored_apikey) || $apikey !== $stored_apikey) {
            throw new moodle_exception('invalidapikey', 'local_appcrueservices');
        }

        $user = $DB->get_record('user', [
            'email' => $studentemail,
            'deleted' => 0,
            'suspended' => 0,
            'mnethostid' => $CFG->mnet_localhost_id
        ], '*', MUST_EXIST);

        // Validar que el usuario está enrolado en algún curso
        if (!enrol_get_users_courses($user->id, true)) {
            throw new \moodle_exception('usernotenrolled', 'local_appcrueservices');
        }
        
        // Guardar usuario original.
        $originaluser = $USER;

        try {
            \core\session\manager::set_user($user);

            $courses = enrol_get_users_courses($user->id, true);
            $forumoutput = [];

            foreach ($courses as $course) {
                $modinfos = get_fast_modinfo($course);
                foreach ($modinfos->get_instances_of('forum') as $cm) {
                    if (!$cm->uservisible) {
                        continue;
                    }

                    $forum = $DB->get_record('forum', ['id' => $cm->instance], '*', MUST_EXIST);
                    $discussions = forum_get_discussions($cm);

                    foreach ($discussions as $discussion) {
                        $context = \context_module::instance($cm->id);

                        // Compatibilidad con Moodle 3.7, 3.11, 4.0 y posteriores
                        if ((new ReflectionFunction('forum_get_all_discussion_posts'))->getNumberOfParameters() >= 3) {
                            $posts = forum_get_all_discussion_posts($discussion->discussion, 'created ASC', $context);
                        } else {
                            $posts = forum_get_all_discussion_posts($discussion->discussion);
                        }

                        $postmap = [];
                        foreach ($posts as $post) {
                            $postmap[$post->id] = [
                                'id'           => (string)$post->id,
                                'parent_id'    => (string)$post->parent,
                                'display_name' => fullname($DB->get_record('user', ['id' => $post->userid], 'id, firstname, lastname')),
                                'createdAt'    => $post->created,
                                'message'      => html_entity_decode(strip_tags($post->message), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                                'replies'      => []
                            ];
                        }
                        
                        $rootposts = self::build_post_tree($postmap);

                        $discussionurl = new \moodle_url('/mod/forum/discuss.php', ['d' => $discussion->id]);

                        $forumoutput[] = [
                            'course_title' => (string) ($course->fullname ?? ''),
                            'forum_name'   => (string) ($forum->name ?? ''),
                            'description'  => (string) html_entity_decode(strip_tags($forum->intro ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'lock_at'      => '',
                            'todo_date'    => '',
                            'html_url'     => (string) ($discussionurl->out(false) ?? ''),
                            'topic_title'  => (string) ($discussion->name ?? ''),
                            'posted_at'    => isset($discussion->created) ? (int)$discussion->created : time(),
                            'unread_count' => isset($discussion->replies) ? (string)$discussion->replies : '0',
                            'replies'      => json_encode($rootposts)
                        ];
                    }
                }
            }

        } finally {
            \core\session\manager::set_user($originaluser);
        }

        return $forumoutput;
    }

    public static function get_forums_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'course_title' => new \external_value(PARAM_TEXT, 'Nombre del curso'),
                'forum_name'   => new \external_value(PARAM_TEXT, 'Nombre del foro'),
                'description'  => new \external_value(PARAM_RAW, 'Descripción del foro'),
                'lock_at'      => new \external_value(PARAM_TEXT, 'Fecha de bloqueo'),
                'todo_date'    => new \external_value(PARAM_TEXT, 'Fecha límite'),
                'html_url'     => new \external_value(PARAM_TEXT, 'URL del foro'),
                'topic_title'  => new \external_value(PARAM_TEXT, 'Título del tema de discusión'),
                'posted_at'    => new \external_value(PARAM_INT, 'Fecha de publicación del tema'),
                'unread_count' => new \external_value(PARAM_TEXT, 'Número de respuestas no leídas'),
                'replies'      => new \external_value(PARAM_RAW, 'Respuestas en JSON anidado')
            ])
        );
    }
}
