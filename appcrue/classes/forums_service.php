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
 * Forums service implementation for the AppCrue.
 *
 * @package    local_appcrue
 * @copyright  2025 Alberto Otero Mato <alberto.otero@altia.es> Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_appcrue;
use context_module;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/forum/lib.php');

/**
 * Class forums_service
 */
class forums_service extends appcrue_service {
    /**
     * timestart for filtering forums. No post older than this.
     * @var int|null
     */
    public ?int $timestart = null;
    /**
     * configure_from_request
     * This method is used to get the forums data for the user.
     */
    public function configure_from_request() {
         // Process the request parameters.
        $timestart = optional_param('timestart', null, PARAM_INT);
        $timewindow = time() - get_config('local_appcrue', 'lmsappcrue_forums_timewindow') ?? 0;
        $this->timestart = max($timestart, $timewindow);
    }
    /**
     * Get data response.
     */
    public function get_data_response() {
        // Get the items and count.
        [$items, $count] = $this->get_items();
        // Return the items and count.
        return [ [ 'forums' => $items ], $count];
    }
    /**
     * Build a tree of posts from a flat array.
     * @param array $postmap Array of posts indexed by post ID.
     * @param string $parentid Parent ID to start building the tree from.
     * @return array Tree structure of posts.
     */
    public static function build_post_tree(array &$postmap, string $parentid = "0"): array {
        $tree = [];

        foreach ($postmap as $postid => $post) {
            if ($post['parent_id'] === $parentid) {
                $children = self::build_post_tree($postmap, $post['id']);
                $post['replies'] = $children;
                $tree[] = $post;
            }
        }

        return $tree;
    }
    /**
     * Get forums data for the user.
     * @return [array, int] JSON structure with forum data, count of posts.
     */
    public function get_items() {
        // TODO: Show only tracking forums??
        $tracking = false;
        $numposts = 0;
        $forumoutput = [];
        // Get forums accessible to the user.
        $forums = self::get_readable_forums($this->user->id);

        foreach ($forums as $forum) {
            $courseid = $forum->course;
            $modinfos = get_fast_modinfo($courseid);
            $cm = $modinfos->instances['forum'][$forum->id] ?? null;
            if (!$cm) {
                continue;
            }
            if (!$cm->uservisible) {
                continue;
            }
            $context = context_module::instance($cm->id);
            $course = $cm->get_course();
            // Process files.
            $forumdescription = file_rewrite_pluginfile_urls(
                $forum->intro,
                'pluginfile.php',
                $context->id,
                'mod_forum',
                'intro',
                0
            );
            $forumdescription = format_text(
                $forumdescription,
                $forum->introformat,
                ['context' => $context],
            );
            $forumdescription = html_entity_decode(strip_tags($forumdescription ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Get all the recent discussions we're allowed to see
            // get the most recent posts in a forum in descending order.
            // The call to default sort order here will use
            // that unless the discussion that post is in has a timestart set
            // in the future.
            // This sort will ignore pinned posts as we want the most recent.
            $sort = forum_get_default_sort_order(true, 'p.modified', 'd', false);
            $discussions = forum_get_discussions(
                $cm,
                $sort,
                false,
                -1,
                100, // Limit to 100 discussions.
                false,
                -1,
                0,
                FORUM_POSTS_ALL_USER_GROUPS
            );

            foreach ($discussions as $discussion) {
                // Skip discussions last modified before the specified time.
                if ($this->timestart && $discussion->modified < $this->timestart) {
                    continue;
                }
                $posts = forum_get_all_discussion_posts($discussion->discussion, 'created ASC', $tracking);
                $postmap = [];
                foreach ($posts as $post) {
                    $message = $post->message;
                    $message = file_rewrite_pluginfile_urls(
                        $message,
                        'pluginfile.php',
                        $context->id,
                        'mod_forum',
                        'post',
                        $post->id
                    );
                    $message = format_text(
                        $message,
                        $post->messageformat,
                        ['context' => $context, 'para' => false, 'trusted' => true]
                    );
                    // Get a permalink to post.
                    $permalink = new \moodle_url('/mod/forum/discuss.php', ['d' => $discussion->id]);
                    $permalink->set_anchor('p' . $post->id);
                    // Deep link.
                    $permalink = appcrue_create_deep_url($permalink->out(), $this->token, $this->tokenmark);
                    // Get Reply to link.
                    $replylink = new \moodle_url('/mod/forum/post.php', ['reply' => $post->id]);
                    $replylink = appcrue_create_deep_url($replylink->out(), $this->token, $this->tokenmark);

                    $postmap[$post->id] = [
                        'id'           => (string)$post->id,
                        'parent_id'    => (string)$post->parent,
                        'display_name' => appcrue_get_userfullname($post->userid),
                        'createdAt'    => $post->created,
                        'message'      => html_entity_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'replies'      => [],
                        'permalink'    => $permalink,
                        'replylink'    => $replylink,
                    ];
                    $numposts++;
                }

                $rootposts = self::build_post_tree($postmap);

                $discussionurl = new \moodle_url('/mod/forum/discuss.php', ['d' => $discussion->id]);

                // JPC ¿¿¿¿¿ Se mezcla foro y discussion ????
                $forumoutput[] = [
                    'course_title' => (string) ($course->fullname ?? ''),
                    'forum_name'   => (string) ($forum->name ?? ''),
                    'description'  => (string) html_entity_decode(strip_tags($forumdescription ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'lock_at'      => '',
                    'todo_date'    => '',
                    'html_url'     => (string) ($discussionurl->out(false) ?? ''),
                    'topic_title'  => (string) ($discussion->name ?? ''),
                    'posted_at'    => isset($discussion->created) ? (int)$discussion->created : time(),
                    'unread_count' => isset($discussion->replies) ? (string)$discussion->replies : '0',
                    'replies'      => $rootposts,
                ];
            }
        }

        return [$forumoutput, $numposts];
    }

    /**
     * An array of forum objects that the user is allowed to read/search through.
     * NOTE: This is copypasted from Moodle core function forum_get_readable_forums()
     * because:
     * - it had a bug that breaks in Moodle 4.5, at least.
     * - need to filter by group mode and hidden discussions.
     * @param int $userid
     * @param int $courseid if 0, we look for forums throughout the whole site.
     * @return array of forum objects, or false if no matches
     *         Forum objects have the following attributes:
     *         id, type, course, cmid, cmvisible, cmgroupmode, accessallgroups,
     *         viewhiddentimedposts
     */
    public static function get_readable_forums($userid, $courseid = 0) {

        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        if (!$forummod = $DB->get_record('modules', ['name' => 'forum'])) {
            throw new \moodle_exception('notinstalled', 'forum');
        }

        if ($courseid) {
            $courses = $DB->get_records('course', ['id' => $courseid]);
        } else {
            // If no course is specified, then the user can see SITE + his courses.
            $courses1 = $DB->get_records('course', ['id' => SITEID]);
            $courses2 = enrol_get_users_courses($userid, true);
            $courses = array_merge($courses1, $courses2);
        }
        if (!$courses) {
            return [];
        }

        $readableforums = [];

        foreach ($courses as $course) {
            $modinfo = get_fast_modinfo($course);

            if (empty($modinfo->instances['forum'])) {
                // Hmm, no forums?
                continue;
            }

            $courseforums = $DB->get_records('forum', ['course' => $course->id]);

            foreach ($modinfo->instances['forum'] as $forumid => $cm) {
                if (!$cm->uservisible || !isset($courseforums[$forumid])) {
                    continue;
                }
                $context = context_module::instance($cm->id);
                $forum = $courseforums[$forumid];
                $forum->context = $context;
                $forum->cm = $cm;

                if (!has_capability('mod/forum:viewdiscussion', $context)) {
                    continue;
                }

                // Group access.
                if (
                    groups_get_activity_groupmode($cm, $course) == SEPARATEGROUPS
                    && !has_capability('moodle/site:accessallgroups', $context)
                ) {
                    $groups = $modinfo->get_groups($cm->groupingid);
                    if (empty($groups)) {
                        // No groups, so no access.
                        continue;
                    }
                    // If the forum is in a grouping, we need to get the groups in that grouping
                    $forum->onlygroups = $groups;
                    $forum->onlygroups[] = -1;
                }

                // Hidden timed discussions.
                $forum->viewhiddentimedposts = true;
                if (!empty($CFG->forum_enabletimedposts)) {
                    if (!has_capability('mod/forum:viewhiddentimedposts', $context)) {
                        $forum->viewhiddentimedposts = false;
                    }
                }

                // Question and answers access.
                if (
                    $forum->type == 'qanda'
                    && !has_capability('mod/forum:viewqandawithoutposting', $context)
                ) {
                    // We need to check whether the user has posted in the qanda forum.
                    $forum->onlydiscussions = [];  // Holds discussion ids for the discussions
                                                    // the user is allowed to see in this forum.
                    if ($discussionspostedin = forum_discussions_user_has_posted_in($forum->id, $USER->id)) {
                        foreach ($discussionspostedin as $d) {
                            $forum->onlydiscussions[] = $d->id;
                        }
                    }
                }

                $readableforums[$forum->id] = $forum;
            }

            unset($modinfo);
        } // End foreach $courses

        return $readableforums;
    }
}
