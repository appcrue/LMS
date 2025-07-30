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
 * External message API
 *
 * @package    local_appcrue
 * @category   external
 * @copyright  2021 Juan Pablo de Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . "/message/lib.php");
require_once($CFG->dirroot . "/message/externallib.php");
require_once("$CFG->dirroot/local/appcrue/locallib.php");

/**
 * Message external functions
 *
 * @package    local_appcrue
 * @category   external
 * @copyright  2021 Juan Pablo de Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class local_appcrue_external extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function send_instant_messages_parameters() {
        return new external_function_parameters(
            [
                'messages' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'touserkey' => new external_value(PARAM_RAW, 'Match value for finding the user to send the private message to'),
                            'text' => new external_value(PARAM_RAW, 'The text of the message'),
                            'textformat' => new external_format_value('text', VALUE_DEFAULT, FORMAT_MOODLE),
                            'clientmsgid' => new external_value(
                                PARAM_ALPHANUMEXT,
                                'your own client id for the message. If this id is provided, the fail message id will be returned to you',
                                VALUE_DEFAULT,
                                null
                            ),
                        ]
                    )
                ),
                'field' => new external_value(
                    PARAM_RAW,
                    'User field for finding the user. Defaults to setting local_appcrue/match_user_by',
                    VALUE_DEFAULT,
                    null
                ),
            ]
        );
    }

    /**
     * Send private messages from the admin USER to other users
     *
     * @param array $messages An array of message to send.
     * @param string $field User field for finding the user. Defaults to setting 'local_appcrue/matchuserby'.
     * @return array
     * @since Moodle 2.2
     */
    public static function send_instant_messages($messages = [], $field = null) {
        global $CFG, $USER;
        // Check if messaging is enabled.
        if (empty($CFG->messaging)) {
            throw new moodle_exception('disabled', 'message');
        }
        self::validate_parameters(self::send_instant_messages_parameters(), ['messages' => $messages, 'field' => $field]);

        if (!$field) {
            $field = get_config('local_appcrue', 'match_user_by');
        }
        // Remap all tousers of the messages.
        foreach ($messages as $key => $message) {
            if (isset($message['touserkey'])) {
                $receiver = $message['touserkey'];
                $touser = appcrue_find_user($field, $receiver);
                if ($touser) {
                    $messages[$key]['touserid'] = $touser->id;
                    unset($messages[$key]['touserkey']); // Clean the parameters.
                }
            }
        }
        return core_message_external::send_instant_messages($messages);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function send_instant_messages_returns() {
        return core_message_external::send_instant_messages_returns();
    }
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function send_instant_message_parameters() {
        return new external_function_parameters(
            [
                'touserkey' => new external_value(PARAM_RAW, 'Match value for finding the user to send the private message to'),
                'text' => new external_value(PARAM_RAW, 'The text of the message'),
                'textformat' => new external_format_value('text', VALUE_DEFAULT, FORMAT_MOODLE),
                'field' => new external_value(PARAM_RAW, 'User field for finding the user. Defaults to setting local_appcrue/match_user_by', VALUE_DEFAULT, null),
            ]
        );
    }

    /**
     * Send private messages from the admin USER to other users
     *
     * @param string $touserkey Match value for finding the user to send the private message to.
     * @param string $text The text of the message.
     * @param int $textformat The format of the message.
     * @param string $field User field for finding the user. Default is setting local_appcrue/match_user_by.
     * @return array
     * @since Moodle 2.2
     */
    public static function send_instant_message($touserkey, $text, $textformat = FORMAT_MOODLE, $field = null) {
        self::validate_parameters(self::send_instant_message_parameters(), ['touserkey' => $touserkey, 'text' => $text, 'textformat' => $textformat, 'field' => $field]);
        $message = [];
        $message['touserkey'] = $touserkey;
        $message['text'] = $text;
        $message['textformat'] = $textformat;
        if (!$field) {
            $field = get_config('local_appcrue', 'match_user_by');
        }
        return self::send_instant_messages([$message], $field);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function send_instant_message_returns() {
        return core_message_external::send_instant_messages_returns();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function notify_grade_parameters() {
        return new external_function_parameters(
            [
                'idusuario' => new external_value(PARAM_RAW, 'Oficial university id for a student.', VALUE_DEFAULT, null),
                'nip' => new external_value(PARAM_RAW, 'Source system\'s user identification.', VALUE_DEFAULT, null),
                'useremail' => new external_value(PARAM_EMAIL, 'Email of the student.', VALUE_DEFAULT, null),
                'subject' => new external_value(PARAM_RAW, 'Internal subject code.'),
                'group' => new external_value(PARAM_INT, 'Enrollment group.', VALUE_DEFAULT, null),
                'subjectname' => new external_value(PARAM_RAW, 'Oficial subject name.'),
                'course' => new external_value(PARAM_RAW, 'Course year.'),
                'grade' => new external_value(PARAM_RAW, 'Numerical grade.'),
                'call' => new external_value(PARAM_RAW, 'Grading call.', VALUE_DEFAULT, null),
                'gradealpha' => new external_value(PARAM_RAW, 'Grade text equivalence.', VALUE_DEFAULT, null),
                'revdate' => new external_value(PARAM_INT, 'Date of the revision in epoch format.', VALUE_DEFAULT, null),
                'comment' => new external_value(PARAM_RAW, 'Description of the grade publication'),
            ]
        );
    }

    /**
     * Send private messages from the admin USER to other users
     *
     * @param  int $idusuario Oficial university id for a student.
     * @param  string $nip Source system's user identification.
     * @param  string $useremail Email of the student.
     * @param  string $subject Title of the message.
     * @param  int $group Enrollment group.
     * @param  string $subjectname Oficial subject name.
     * @param  string $course Course year.
     * @param  string $grade Numerical grade.
     * @param  string $call Grading call.
     * @param  string $gradealpha Grade text equivalence.
     * @param  int $revdate Date of the revision in epoch format.
     * @param  string $comment Description of the grade publication.
     * @return array
     * @since Moodle 2.2
     */
    public static function notify_grade($idusuario, $nip, $useremail, $subject, $group, $subjectname, $course, $grade, $call, $gradealpha, $revdate, $comment) {
        $params = [
            'idusuario' => $idusuario,
            'nip' => $nip,
            'useremail' => $useremail,
            'subjectname' => $subjectname,
            'subject' => $subject,
            'group' => $group,
            'course' => $course,
            'grade' => $grade,
            'call' => $call,
            'gradealpha' => $gradealpha,
            'revdate' => $revdate,
            'comment' => $comment,
        ];
        $result = self::validate_parameters(self::notify_grade_parameters(), $params);
        // TODO: Find a way to integrate final grades into gradebook.
        // Compose message.
        // Find user.
        if ($idusuario) {
            $fieldname = get_config('local_appcrue', 'match_user_by');
            $userto = appcrue_find_user($fieldname, $idusuario);
        }
        if ($userto == false) {
            $userto = appcrue_find_user('email', $useremail);
        }
        if ($userto == false) {
            return [
                (object)['msgid' => -1,
                    'errormessage' => 'No se encontró el usuario.',
                ],
            ];
        }
        // Find out course from $subject code.
        $course = appcrue_find_course($subject, $group, $course);
        if ($course == false) {
            debugging("Course not found for subject $subject, group $group, course $course", DEBUG_NONE);
        }
        // Force_current_language($userto->lang) post_message supposed to do this.
        // If revdate is null format proper string.
        if ($revdate == null) {
            $params['revdateformat'] = get_string('notify_grade_revdate_null', 'local_appcrue');
        } else {
            $params['revdateformat'] = userdate($revdate, get_string('strftimedatetime', 'core_langconfig'));
            $params['revdateformat'] = get_string('notify_grade_revdate', 'local_appcrue', $params);
        }
        $message = get_string('new_grade_message', 'local_appcrue', $params);
        $format = FORMAT_MARKDOWN;

        // Find a teacher as sender.
        $userfrom = appcrue_find_sender($course);

        return appcrue_post_message($course, $userfrom, $userto, $message, $format);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function notify_grade_returns() {
        // TODO: Customize return type.
        return self::send_instant_message_returns();
    }
}
