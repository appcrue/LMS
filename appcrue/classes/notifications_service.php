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
 * Class notifications_service
 *
 * @package    local_appcrue
 * @copyright  2025 Alberto Otero Mato <alberto.otero@altia.es>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notifications_service extends appcrue_service {
    /**
     * Get data response.
     */
    public function get_data_response() {
        // Get the items and count.
        $items = $this->get_items();
        $count = count($items);
        // Return the items and count.
        return [ [ 'notifications' => $items ], $count];
    }
    /**
     * Get notifications for a user.
     * @return array Array of notifications for the user.
     */
    public function get_items() {
        global $DB, $USER;
    
        // Retrieve unread notifications (you can customize this as needed).
        $notifications = $DB->get_records_select(
            'notifications',
            'useridto = :userid AND timeread IS NULL',
            ['userid' => $this->user->id],
            'timecreated DESC',
            '*',
            0,
            50
        );
    
        $results = [];
        foreach ($notifications as $notification) {
            $userfrom = $notification->useridfrom ? \core_user::get_user($notification->useridfrom) : null;
            
            $results[] = [
                'id' => $notification->id,
                'useridfrom' => $notification->useridfrom,
                'userfromfullname' => $userfrom ? fullname($userfrom) : get_string('system', 'core'),
                'subject' => format_string($notification->subject ?? ''),
                'fullmessage' => (string) html_entity_decode(strip_tags($notification->fullmessage ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'smallmessage' => (string) html_entity_decode(strip_tags($notification->smallmessage ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'timecreated' => $notification->timecreated, // It will always be null because we filter unread
                'timeread' => $notification->timeread,
                'component' => $notification->component,
                'eventtype' => $notification->eventtype,
                'contexturl' => $notification->contexturl ?? '',
                'isread' => ($notification->timeread !== null) // Always false for unread
            ];
        }

        return $results;
    }
}
