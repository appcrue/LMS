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

// phpcs:disable moodle.Files.RequireLogin.Missing

/**
 * Give the calendar events to the app.
 *
 * @package    local_appcrue
 * @copyright  2021 University of Valladoild, Spain
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once('locallib.php');

/** @var moodle_database $DB */
global $DB;

if (!get_config('local_appcrue', 'enable_sitemap')) {
    header('HTTP/1.1 405 Method Not Allowed');
    die();
    // Better act as a service don't throw new moodle_exception('servicedonotexist', 'error').
}
$token = optional_param('token', '', PARAM_RAW);
$category = optional_param('category', 0, PARAM_INT);
$includecourses = optional_param('courses', false, PARAM_BOOL);
$hiddencats = optional_param_array('hidden', [], PARAM_INT);
$urlsonlyonends = optional_param('endurls', true, PARAM_BOOL);

$PAGE->set_context(null);
header('Content-Type: text/json; charset=utf-8');

$key = false; // Disable cache.
$sitemap = false; // Force calculation on map.

if (get_config('local_appcrue', 'cache_sitemap')) {
    $cache = cache::make('local_appcrue', 'sitemaps');
    $key = "local_appcrue{$category}_{$includecourses}_{$urlsonlyonends}H" . join('_', $hiddencats);
    $timecache = $cache->get($key . '_created');
    if ($timecache) {
        $timecache = (int) $timecache;
        $timetolive = $timecache + (int) get_config('local_appcrue', 'cache_sitemap_ttl');
        if ($timetolive > time()) {
            $sitemap = $cache->get($key);
        } else {
            // Expired.
            $cache->delete($key);
            $cache->delete($key . '_created');
        }
    }
}

if ($sitemap == false) {
    $categories = $DB->get_records_select(
        'course_categories',
        'TRUE',
        ['id', 'name', 'description', 'parent', 'coursecount', 'visible']
    );
    $catindex = [];
    $navegableroot = new stdClass();
    $catindex[0] = $navegableroot;
    $errors = []; // List of problems detected.
    // Build index.
    foreach ($categories as $id => $cat) {
        if ($cat->visible == '0') {
            continue;
        }
        $navegable = new stdClass();
        $navegable->name = $cat->name;
        $navegable->description = format_text($cat->description, FORMAT_HTML, ['nocache' => true]);
        $navegable->id = $cat->id;
        // URL.
        $url = new moodle_url('/course/index.php', ['categoryid' => $cat->id]);
        $navegable->url = $url->out();
        $catindex[$cat->id] = $navegable;
        if ($cat->id == $category) {
            $navegableroot = $navegable;
        }
    }
    // Build tree.
    foreach ($catindex as $catid => $navegable) {
        if ($catid != 0) {
            // Get navegable parent.
            $category = $categories[$catid];
            $parentid = $category->parent;

            $parent = $catindex[$parentid] ?? null;
            $ishidden = array_search($catid, $hiddencats) !== false;
            if ($ishidden) {
                continue;
            }
            // Add child.
            if ($parent) {
                if ($urlsonlyonends) {
                    unset($parent->url);
                }
                $parent->navegable[] = $navegable;
            } else {
                // Parent category missing. Do something.
                $errors[] = "Missing parent {$parentid} for category {$category->name}.";
            }
        }
    }
    // Add courses.
    // This implementatios uses one query but no caching.
    if ($includecourses) {
        $courses = $DB->get_records_select('course', 'TRUE', ['fullname', 'summary', 'id', 'category']);
        foreach ($courses as $course) {
            // Find navegable.
            if ($course->id != SITEID && isset($catindex[$course->category])) {
                $nav = $catindex[$course->category];
                $coursenav = new stdClass();
                $coursenav->name = $course->fullname;
                $coursenav->description = content_to_text($course->summary, false);
                $url = new moodle_url('/course/view.php', ['id' => $course->id]);
                $coursenav->url = $url->out();
                $nav->navegable[] = $coursenav;
                if ($urlsonlyonends) {
                    unset($nav->url);
                }
            }
        }
    }

    if (debugging()) {
        $navegableroot->debug = new stdClass();
        $navegableroot->debug->token = $token;
        $navegableroot->debug->errors = $errors;
        $navegableroot->updated = userdate(time());
    }
    $sitemap = json_encode($navegableroot, JSON_HEX_QUOT | JSON_PRETTY_PRINT);
    if ($key) {
        $cache->set($key, $sitemap);
        $cache->set($key . '_created', time());
    }
}
// TODO: substitute current token with bearer mark if caching at server is a problem.
// Change simple URLs by DeepURLs.
$tokenmark = get_config('local_appcrue', 'deep_url_token_mark');
$navegableroot = json_decode($sitemap);
appcrue_filter_urls($navegableroot, $token, $tokenmark);
$sitemap = json_encode($navegableroot, JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

echo $sitemap;
