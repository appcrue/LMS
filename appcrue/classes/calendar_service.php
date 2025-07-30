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

use calendar_event;
use cm_info;
use stdClass;
use moodle_url;
use const bar\foo\baz\DEFINE;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/local/appcrue/locallib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Class lmsappcruelib
 * Services implementation for the endpoints.
 *
 * @package    local_appcrue
 * @copyright  2025 Alberto Otero Mato <alberto.otero@altia.es> Juan Pablo de Castro <juan.pablo.de.castro@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calendar_service extends appcrue_service {
    /**
     * @var int Start time for the events.
     */
    public $timestart;
    /**
     * @var int End time for the events.
     */
    public $timeend;
    /**
     * configure_from_request
     * Read parameters from the request and configure the service.
     * This method is called in the constructor.
     */
    public function configure_from_request() {
        parent::configure_from_request();
        $this->timestart = optional_param('timestart', 0, PARAM_INT);
        $this->timeend = optional_param('timeend', 0, PARAM_INT);
    }
    /**
     * Get data response.
     */
    public function get_data_response() {
        // Get the items and count.
        $items = $this->get_items();
        $count = count($items);
        // Return the items and count.
        return [ [ 'events' => $items ], $count];
    }
    /**
     * Get items for the service.
     * @return mixed JSON structure of calendar events for the user.
     */
    public function get_items() {
        // Get events from Moodle API according to the group, site and user restrictions of the user.
        $events = self::get_events($this->user, $this->timestart, $this->timeend);
        // Format events for the user calendar service.
        $formattedevents = $this->format_events_for_lmsappcrue($events, $this->user);
        return $formattedevents;
    }
    /**
     * Get events from Moodle API according to the group, site and user restrictions of the user.
     * @param \stdClass $user the user object
     * @param int $timestart start time in seconds since epoch
     * @param int $timeend end time in seconds since epoch
     * @param int $limitnum the maximum number of events to return, 0 for no limit
     * @return array the list of events
     * @throws \Exception if the time range is invalid
     */
    public static function get_events(stdClass $user, int $timestart, int $timeend, int $limitnum = 0): array {
        global $DB;
        // Validate time range if both are provided.
        if ($timestart > 0 && $timeend > 0 && $timestart > $timeend) {
            throw new \Exception("Invalid time range Start: {$timestart} > End: {$timeend}", 404);
        }
        // Limit time spans.
        if ($timestart == 0 && $timeend == 0) {
            $timestart = time() - 30 * DAYSECS;
            $timeend = $timestart + 60 * DAYSECS;
        }
        if ($timestart <= 0 && $timeend > 0) {
            $timestart = $timeend - 60 * DAYSECS;
        } else if ($timestart > 0 && $timeend <= 0) {
            $timeend = $timestart + 60 * DAYSECS;
        }

        // Get groups and courses for the user, according to the plugin config.
        if (get_config('local_appcrue', 'calendar_share_course_events')) {
            // All courses.
            $courses = enrol_get_users_courses($user->id, true, 'id, visible, shortname');
            // All groups.
            $groups = [];
            foreach ($courses as $course) {
                $coursegroups = groups_get_all_groups($course->id, $user->id);
                // Concat preserving keys.
                $groups = $groups + $coursegroups;
            }
        } else {
            $courses = [];
            $groups = [];
        }
        // Site events.
        if (get_config('local_appcrue', 'calendar_share_site_events')) {
            $courses[SITEID] = new stdClass();
            $courses[SITEID]->shortname = get_string('siteevents', 'calendar');
        }
        // Personal events.
        if (get_config('local_appcrue', 'share_personal_events')) {
            $users = [$user->id];
        } else {
            $users = [];
        }
        // Map arrays to ids.
        $coursesids = array_keys($courses);
        // Map arrays to ids.
        $groupsids = array_keys($groups);

        // Get events from local calendar api.
        $events = calendar_get_legacy_events(
            tstart: $timestart,
            tend: $timeend,
            users: $users,
            groups: $groupsids,
            courses: $coursesids,
            withduration: false,
            ignorehidden: true,
            categories: [],
            limitnum: $limitnum
        );

        return $events;
    }
    /**
     * Get the URL of the event.
     * @param \stdClass $event the event object
     * @param string $tokenmark the token mark to use in the URL, default is 'bearer'
     * @param string $token the token to use in the urls
     * @return string the URL of the event
     */
    public static function get_event_url(stdClass $event, ?string $token, ?cm_info $cminfo, ?string $tokenmark = 'bearer'): string {
        if ($cminfo) {
            $eventurl = $cminfo->get_url()->out(true);
        } else {
            // The event is a calendar event.
            $params = [
            'view' => 'day',
            'time' => $event->timestart,
            ];
            if (isset($event->courseid) && $event->eventtype != 'user') {
                $params['course'] = $event->courseid;
            }
            $url = new moodle_url("/calendar/view.php", $params);
            $eventurl = $url->out(false);
        }
        // Convert the url to a redirected url with token.
        $eventurl = appcrue_create_deep_url($eventurl, $token, $tokenmark);
        return $eventurl;
    }
    /**
     * Format events for the user calendar service
     * @param array $events the list of events
     * @param string $category the category to filter events by
     * @param string $token the token to use in the urls
     * @return stdClass the formatted events
     */
    public static function format_events_for_usercalendar(
        array $events,
        $user,
        string $category = '',
        ?string $token = ''
    ): stdClass {
        $outputmessage = new stdClass();
        $outputmessage->calendar = [];
        $tokenmark = get_config('local_appcrue', 'deep_url_token_mark');
        // Order events by day.
        $eventsbyday = [];
        foreach ($events as $event) {
            $eventtype = appcrue_get_event_type($event);
            if ($category != '' && $eventtype != $category) {
                continue;
            }
            $day = date('Y-m-d', $event->timesort);
            $eventsbyday[$day][] = $event;
        }
        // Format output.
        foreach ($eventsbyday as $day => $eventlist) {
            $dayitem = new stdClass();
            $dayitem->date = $day;
            $dayitem->events = [];
            foreach ($eventlist as $event) {
                // Get the course module info the fastest way.
                $fastmodinfo = $event->courseid ? get_fast_modinfo($event->courseid, $user->id) : null;
                $instances = $fastmodinfo ? $fastmodinfo->get_instances_of($event->modulename) : [];
                $cminfo = $instances[$event->instance] ?? null;

                // Hide if module is hidden.
                if ($cminfo && !$cminfo->uservisible) {
                    continue;
                }
                $eventitem = new stdClass();
                $eventitem->id = $event->id;
                $eventitem->title = format_text($event->name, FORMAT_HTML);

                $calendarevt = new calendar_event($event); // To use moodle calendar event services.
                // Format the description text.
                $description = format_text($calendarevt->description, $calendarevt->format, ['context' => $calendarevt->context]);
                // Then convert it to plain text, since it's the only format allowed for the event description property.
                // We use html_to_text in order to convert <br> and <p> tags to new line characters for descriptions in HTML format.
                $description = html_to_text($description, 0);
                $eventitem->description = $description;

                // TODO: get author.
                $eventitem->nameAuthor = appcrue_get_userfullname($event->userid);
                $eventitem->type = appcrue_get_event_type($event);
                $eventitem->startsAt = $event->timestart;
                $eventitem->imgDetail = get_config('local_appcrue', 'calendar_event_imgdetail');
                $eventitem->endsAt = $event->timestart + $event->timeduration;
                $eventitem->url = self::get_event_url($event, $token, $cminfo, $tokenmark);
                $dayitem->events[] = $eventitem;
            }
            $outputmessage->calendar[] = $dayitem;
        }
        return $outputmessage;
    }
     /**
      * Format events for the LMS AppCrue service.
      * @param [event] $eventlist
      * @param stdClass $user
      * @param ?string $token the token to use in the urls
      * @return array{events: array|array{events: mixed}}
      */
    public function format_events_for_lmsappcrue(array $eventlist, stdClass $user, ?string $token = ''): array {
        global $DB;
        $events = [];
        foreach ($eventlist as $event) {
            // Get the course module info the fastest way.
            $fastmodinfo = $event->courseid ? get_fast_modinfo($event->courseid, $user->id) : null;
            $instances = $fastmodinfo ? $fastmodinfo->get_instances_of($event->modulename) : [];
            $cminfo = $instances[$event->instance] ?? null;

            $calendarevt = new calendar_event($event); // To use moodle calendar event APIs.
            // Hide if module is hidden.
            if ($cminfo && !$cminfo->uservisible) {
                continue;
            }

            $course = $cminfo ? $cminfo->get_course() : null;
            // Asegura que el evento tenga URL.
            $eventurl = self::get_event_url($event, $token, $cminfo, $this->tokenmark);
            // Obtener el autor si existe.
            $nameauthor = appcrue_get_userfullname($event->userid);
            // Format the description text. It applies filters and formats.
            $description = format_text($calendarevt->description, $calendarevt->format, ['context' => $calendarevt->context]);
            // Then convert it to plain text, since it's the only format allowed for the event description property.
            // We use html_to_text in order to convert <br> and <p> tags to new line characters for descriptions in HTML format.
            $description = html_to_text($description, 0);
            $name = format_text($event->name, FORMAT_HTML);

            $events[] = [
                'name'          => $name,
                'type'          => $event->eventtype ?? '',
                'modulename'    => $event->modulename ?? '',
                'timestart'     => $event->timestart,
                'timesort'      => $event->timestart + ($event->timeduration ?? 0),
                'description'   => $description,
                'fullname'      => $course ? $course->fullname : '',
                'location'      => $event->location ?? '',
                'url'           => $eventurl,
                'nameauthor'    => $nameauthor,
            ];
        }
        return $events;
    }
}
