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
 * AppCrue services plugin
 *
 * @package local_appcrue
 * @category admin
 * @author  Juan Pablo de Castro
 * @copyright 2021 onwards juanpablo.decastro@uva.es
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_appcrue\appcrue_service;

// Compatibility class aliases for Moodle 4.1-
if ($CFG->version < 2023042400) {
    if (class_exists(cache_store::class) && !class_exists(core_cache\store::class)) {
    class_alias(cache_store::class, core_cache\store::class);
    }
}

/**
 * Get the user from the request.
 * Supports the following parameters in the request:
 * - token: the token to be used to identify the user.
 * - api_key: the API key to be used to identify the user.
 * - user: the user id string to be used to identify the user using the configured profile field.
 * Returns an array with the user and a diagnostic object.
 * @return [stdClass|null, stdClass, ?string] the user and the diagnostic object.
 */
function appcrue_get_user_from_request(): array {
    $apikey = optional_param('apikey', '', PARAM_ALPHANUM);
    $iduser = optional_param('studentemail', '', PARAM_RAW);
    $token = appcrue_get_token_param();
    $user = null;
    // Reporting object.
    $diag = new stdClass();
    $diag->code = 200;
    $diag->message = 'OK';
    if ($token) {
         // User token mode.
        [$user, $diag] = appcrue_get_user_by_token($token);
    } else if ($apikey != '') {
        // If there is an apikey, we use it to get the user.
        // API Key mode.
        if ($apikey == get_config('local_appcrue', 'api_key')) {
            $fieldname = get_config('local_appcrue', 'lmsappcrue_match_user_by');
            $user = appcrue_find_user($fieldname, $iduser);
        } else {
            $diag->code = 401;
            $diag->message = 'Invalid API Key';
            $user = null;
            throw new Exception('Invalid API Key', appcrue_service::INVALID_API_KEY);
        }
    } else {
        throw new Exception('Missing token or API key', appcrue_service::MISSING_WS_TOKEN);
    }
    if ($user) {
        $diag->code = 200;
        $diag->message = 'User found';
    } else {
        $diag->code = 404;
        $diag->message = 'User not found';
        throw new Exception('User not found', appcrue_service::USER_NOT_ENROLLED);
    }
    return [$user, $diag, $token];
}

/**
 * Checks the token and gets the user associated with it.
 * @param string $token authorization token given to AppCrue by the University IDP. Usually an OAuth2 token.
 * @return list(stdClass|null, stdClass) the user and the result of the check.
 */
function appcrue_get_user_by_token($token) {
    $matchvalue = false;
    $user = false;
    $returnstatus = new stdClass();
    [$matchvalue, $tokenstatus] = appcrue_validate_token($token);
    $returnstatus->code = $tokenstatus->code;
    $returnstatus->result = $tokenstatus->result;
    // Get user.
    if ($returnstatus->code == 401) {
        $user = null;
        $returnstatus->status = 'error';
    } else {
        $returnstatus->status = 'validated';
        // JPC: Refactor this block as function.
        $fieldname = get_config('local_appcrue', 'match_user_by');
        $user = appcrue_find_user($fieldname, $matchvalue);
        if (!$user) {
            $returnstatus->code = 404; // 404 Not found.
        } else {
            $returnstatus->code = 200; // 200 OK.
        }
    }
    return [$user, $returnstatus];
}
/**
 * Get token from the request.
 */
function appcrue_get_token_param($required = false): string {
    $token = optional_param('token', '', PARAM_TEXT);
     // Try to extract a Bearer token.
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $auth = $headers['Authorization'];
        if (preg_match('/^Bearer\s+(.*)$/', $auth, $matches)) {
            $token = $matches[1];
        }
    }
    if ($required && empty($token)) {
        throw new moodle_exception('missingtoken', 'local_appcrue');
    }
    return $token;
}
/**
 * Validate token and return the matchvalue.
 * @return list(string|false, stdClass) the matchvalue or false if the token is not valid and a status object.
 */
function appcrue_validate_token($token) {
    if (empty($token)) {
        return [false, (object)['code' => 401, 'result' => 'Token is empty']];
    }
    $matchvalue = false;
    $returnstatus = new stdClass();
    global $CFG;
    // Load curl class.
    require_once($CFG->dirroot . '/lib/filelib.php');
    // The idp service for checking the token i.e. 'https://idp.uva.es/api/adas/oauth2/tokendata'.
    $idpurl = get_config('local_appcrue', 'idp_token_url');
    $curl = new \curl();
    $options = [
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_CONNECTTIMEOUT' => 5,
        'CURLOPT_HTTPAUTH' => CURLAUTH_ANY,
    ];
    $curl->setHeader(["Authorization: Bearer $token"]);
    $result = $curl->get($idpurl, null, $options);
    $statuscode = $curl->get_info()['http_code'];
    // Debugging info for response.
    $returnstatus->code = $statuscode;
    $returnstatus->result = $result;

    // Extract a matchvalue of the token from the idp.
    if ($statuscode == 200) {
        $jsonpath = get_config('local_appcrue', 'idp_user_json_path');
        $matchvalue = appcrue_get_json_node($result, $jsonpath);
        if ($matchvalue == false) {
            $returnstatus->result = "Path {$jsonpath} not found in: {$result}";
            debugging($returnstatus->result, DEBUG_NORMAL);
        }
    } else if ($statuscode == 401) {
        $returnstatus->result = "Permission denied for the token: {$token}";
        // Do not break the output: debugging($returnstatus->result, DEBUG_NORMAL);.
        $matchvalue = false;
    } else {
        debugging("IDP problem: $statuscode", DEBUG_MINIMAL);
    }
    return [$matchvalue, $returnstatus];
}
/**
 * Search user fields and get the user
 * @param string $fieldname the name of the field to search into
 * @param string $matchvalue the value to search for.
 * @return stdClass|false user structure
 */
function appcrue_find_user($fieldname, $matchvalue) {
    global $DB;
    // First check in standard fieldnames.
    $fields = get_user_fieldnames();
    if (array_search($fieldname, $fields) !== false) {
        $user = $DB->get_record('user', [$fieldname => $matchvalue], '*');
        if ($user == false) {
            throw new Exception("No match with: {$fieldname} => {$matchvalue}", appcrue_service::USER_NOT_ENROLLED);
        }
    } else {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        $customfields = profile_get_custom_fields();
        $fieldname = substr($fieldname, 14); // Trim prefix 'profile_field'.
        $fieldid = null;
        // Find custom field id.
        foreach ($customfields as $field) {
            if ($field->shortname == $fieldname) {
                $fieldid = $field->id;
                break;
            }
        }
        // Query user.
        $sql = 'fieldid = ? AND ' . $DB->sql_compare_text('data') . ' = ?';
        $userid = $DB->get_record_select('user_info_data', $sql, [$fieldid, $matchvalue], 'userid');
        if ($userid) {
            $user = $DB->get_record('user', ['id' => $userid->userid], '*');
        } else {
            $user = false;
            debugging("No match with: fieldid:{$fieldid} and data {$matchvalue}", DEBUG_NORMAL);
        }
    }
    return $user;
}
/**
 * Envelops the url with an token-based url.
 * If token is not provided, the url is labelled depending on $tokenmark:
 * If tokenmark and token are not provided, the url is returned as is.
 * @param string $url the url to be enveloped.
 * @param string|null $token the token to be used.
 * @param string|null $tokenmark the mark to be used in the url if $token is nos provided. Can be 'bearer' or 'token'.
 * @param string $fallback the behaviour desired if token validation fails.
 * @return string the enveloped url.
 */
function appcrue_create_deep_url(string $url, $token, $tokenmark = 'bearer', $fallback = 'continue') {
    $params = [];
    if (!$token && !$tokenmark) {
        return $url; // No token, no mark, return the original URL.
    }
    $params['urltogo'] = $url;
    $params['fallback'] = $fallback;
    $tokenmarksufix = '';
    if ($token) {
        $params['token'] = $token;
    }
    if ($tokenmark == 'bearer') {
        $tokenmarksufix = '&<bearer>';
    } else if ($tokenmark == 'token') {
        $tokenmarksufix = '&token=<token>';
    }

    $deepurl = new moodle_url('/local/appcrue/autologin.php', $params);
    return $deepurl->out(false) . $tokenmarksufix;
}
/**
 * Traverse all nodes and re-encode the urls.
 */
function appcrue_filter_urls($node, $token, $tokenmark) {
    if (isset($node->url)) {
        $node->url = appcrue_create_deep_url($node->url, $token, $tokenmark);
    }
    if (isset($node->navegable)) {
        foreach ($node->navegable as $child) {
            appcrue_filter_urls($child, $token, $tokenmark);
        }
    }
}
/**
 * Simple path traversal. Support only dot separator. If it finds an array takes the first item.
 * @param string text the text to search in
 * @param string jsonpath a list of dot separated terms.
 */
function appcrue_get_json_node($text, $jsonpath) {
    $steps = explode('.', $jsonpath);
    $json = json_decode($text);
    // Traverse the steps.
    $node = $json;
    foreach ($steps as $step) {
        if ($step != '') {
            if (!isset($node->$step)) {
                return null;
            }
            $node = $node->$step;
            if (is_array($node)) {
                $node = $node[0];
            }
        }
    }
    return $node;
}
/**
 * Returns the target URL according to optional_param parameters in @see autologin.php.
 * - urltogo: if present, uses it as relative path.
 * - course, group, year: (not necessarily Moodle's identifiers) search a course with idnumber
 *   matching the course pattern i.e.'%-{$course}-{$group}-%'.
 *   Resolves any metalinking and returns the parent course.
 * - pattern: Selector from the patterns library.
 * - param1, param2: general purpose ALPHANUM arguments for generating redirections.
 * @return \moodle_url
 */
function appcrue_get_target_url($token, $urltogo, $course, $group, $year, $pattern, $param1, $param2, $param3) {
    global $DB;
    if ($urltogo !== null) {
        return new moodle_url($urltogo);
    } else if ($pattern !== null) {
        // Use pattern lib.
        $patterns = get_config('local_appcrue', 'pattern_lib');
        $patternlib = [];
        $parts = explode("\n", $patterns);
        $parts = array_map("trim", $parts);
        foreach ($parts as $currentpart) {
            [$key, $value] = explode("=", $currentpart, 2);
            $patternlib[$key] = $value;
        }
        if (isset($patternlib[$pattern])) {
            $selectedpattern = $patternlib[$pattern];
            $url = str_replace(
                ['{token}', '{course}', '{group}', '{year}', '{param1}', '{param2}', '{param3}'],
                [$token, $course, $group, $year, $param1, $param2, $param3],
                $selectedpattern
            );
            return new moodle_url($url);
        } else {
            throw new moodle_exception('invalidrequest');
        }
    } else if ($course !== null) {
        // Get courserecord.
        $courserecord = appcrue_find_course($course, $group, $year, $param1, $param2, $param3);
        if ($courserecord) {
            // Check if it is metalinked to any parent "META" course.
            $metaid = $DB->get_record('enrol', ['customint1' => $courserecord->id, 'enrol' => 'meta'], 'courseid');
            if ($metaid) {
                return new moodle_url("/course/view.php", ["id" => $metaid->courseid]);
            } else {
                return new moodle_url("/course/view.php", ["id" => $courserecord->id]);
            }
        }
    }
    // Default target.
    return new moodle_url("/my/");
}
/**
 * Search a course that matches its idnumber againts a string pattern using course, group, year, param1, param2, param3.
 * Pattern can have placeholders {course}, {group}, etc.
 * @param string $course course part
 * @param string $group group part
 * @param string $year year part
 * @param string $param1 free to use part
 * @param string $param2 free to use part
 * @param string $param3 free to use part
 * @return bool|stdClass
 */
function appcrue_find_course($course, $group, $year, $param1 = '', $param2 = '', $param3 = '') {
    /** @var \moodle_database $DB */
    global $DB;
    $coursepattern = get_config('local_appcrue', 'course_pattern');
    // Compose the pattern.
    $coursepattern = str_replace(
        ['{course}', '{group}', '{year}', '{param1}', '{param2}', '{param3}'],
        [$course, $group, $year, $param1, $param2, $param3],
        $coursepattern
    );
    // Pattern is scaped to avoid SQL injection risks.
    $courserecord = $DB->get_record_select(
        'course',
        "idnumber LIKE :coursepattern",
        ['coursepattern' => $coursepattern]
    );
    return $courserecord;
}
/**
 * Get a user to be the sender of messages.
 * @param stdClass $course
 * @return stdClass
 */
function appcrue_find_sender($course) {
    // Find a Teacher in the course.
    $select = get_config('local_appcrue', 'notify_grade_sender');
    $teacher = null;
    if ($select == 'anyteacher') {
        $context = context_course::instance($course->id);
        $teachers = get_users_by_capability($context, 'moodle/grade:manage');
        if (count($teachers) > 0) {
            $teacher = array_shift($teachers);
        }
    }
    if (!$teacher) {
        global $USER;
        $teacher = $USER;
    }
    return $teacher;
}
/**
 * Config user context:
 * - Impersonates the user.
 * - Set the preferred language of the user.
 * @param stdClass $user
 * @param bool $impersonate if true a session is created for the user.
 * @param string $lang the language to be forced.
 * @return stdClass previous user.
 */
function appcrue_config_user($user, $impersonate = true, string $lang = ''): stdClass {
    global $USER;
    // Save the current user.
    $previoususer = $USER;
    // Set the user context.
    if ($impersonate) {
        \core\session\manager::set_user($user);
    }
    if ($lang != '') {
        // Set the language for the user.
        force_current_language($lang);
    } else if ($USER->id != $user->id) {
        // Set the language for the user.
        force_current_language($user->lang);
    }
    return $previoususer;
}
/**
 * Get the username of the user.
 * @param int $userid
 * @return string
 */
function appcrue_get_userfullname($userid) {
    global $DB;
    // Cache the known users to save queries.
    static $knownusers;
    if (isset($knownusers[$userid])) {
        return $knownusers[$userid];
    }
    $user = $DB->get_record('user', ['id' => $userid], '*');
    if ($user) {
        $knownusers[$userid] = fullname($user);
        return fullname($user);
    } else {
        return get_string('unknownuser');
    }
}
/**
 * Classify the events into the AppCrue types of events: “EXAMEN”, “HORARIO”, “REVISION_DE_EXAMEN”, “TUTORIA”
 * TODO: Refine events types for more activities.
 * @param stdClass $event
 * @return "EXAMEN"|"HORARIO"
 */
function appcrue_get_event_type($event) {
    $examentype = get_config('local_appcrue', 'calendar_examen_event_type');
    if ($event->modulename != null && strpos($examentype, $event->modulename) !== false) {
        return 'EXAMEN';
    }
    return 'HORARIO';
}

/**
 * Send a message from one user to another user.
 * Based on post_message in message/lib.php to allow set sender and courseid.
 * @param stdClass $course The course object.
 * @param stdClass $userfrom The user sending the message.
 * @param stdClass $userto The user receiving the message.
 * @param string $message The message content.
 * @param int $format The format of the message (FORMAT_HTML or FORMAT_MARKDOWN).
 * @return array An array containing the result log message.
 */
function appcrue_post_message($course, $userfrom, $userto, $message, $format) {
    global $PAGE, $USER, $DB;

    $eventdata = new \core\message\message();
    $eventdata->courseid = $course->id;
    $eventdata->component = 'moodle';
    $eventdata->name = 'instantmessage';
    $eventdata->userfrom = $userfrom;
    $eventdata->userto = $userto;

    $eventdata->subject = get_string_manager()->get_string('unreadnewmessage', 'message', fullname($userfrom), $userto->lang);

    if ($format == FORMAT_HTML) {
        $eventdata->fullmessagehtml = $message;
        $eventdata->fullmessage = html_to_text($eventdata->fullmessagehtml);
    } else {
        $eventdata->fullmessage = $message;
        $eventdata->fullmessagehtml = '';
    }

    $eventdata->fullmessageformat = $format;
    $eventdata->smallmessage = $message;
    $eventdata->timecreated = time();
    $eventdata->notification = 0;

    $userpicture = new user_picture($userfrom);
    $userpicture->size = 1;
    $userpicture->includetoken = $userto->id;
    $eventdata->customdata = [
        'notificationiconurl' => $userpicture->get_url($PAGE)->out(false),
        'actionbuttons' => [
            'send' => get_string_manager()->get_string('send', 'message', null, $eventdata->userto->lang),
        ],
        'placeholders' => [
            'send' => get_string_manager()->get_string('writeamessage', 'message', null, $eventdata->userto->lang),
        ],
    ];

    $success = message_send($eventdata);

    $resultmsg = [];
    if (isset($message['clientmsgid'])) {
        $resultmsg['clientmsgid'] = $message['clientmsgid'];
    }
    $messageids = [];
    if ($success) {
        $resultmsg['msgid'] = $success;
        $resultmsg['timecreated'] = time();
        $resultmsg['candeletemessagesforallusers'] = 0;
        $messageids[] = $success;
    } else {
        $resultmsg['msgid'] = -1;
        if (!isset($errormessage)) {
            $errormessage = get_string('messageundeliveredbynotificationsettings', 'error');
        }
        $resultmsg['errormessage'] = $errormessage;
    }

    $resultmessages = [$resultmsg];

    if (!empty($messageids)) {
        $messagerecords = $DB->get_records_list(
            'messages',
            'id',
            $messageids,
            '',
            'id, conversationid, smallmessage, fullmessageformat, fullmessagetrust'
        );
        $resultmessages = array_map(function ($resultmessage) use ($messagerecords, $userfrom, $userto) {
            $id = $resultmessage['msgid'];
            $resultmessage['conversationid'] = isset($messagerecords[$id]) ? $messagerecords[$id]->conversationid : null;
            $resultmessage['useridfrom'] = $userfrom->id;
            $resultmessage['text'] = message_format_message_text((object) [
                'smallmessage' => $messagerecords[$id]->smallmessage,
                'fullmessageformat' => external_validate_format($messagerecords[$id]->fullmessageformat),
                'fullmessagetrust' => $messagerecords[$id]->fullmessagetrust,
            ]);
            return $resultmessage;
        }, $resultmessages);
    }

    return $resultmessages;
}
