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
 * Cache definitions.
 *
 * @package    local_appcrue
 * @copyright  2025 Juan Pblo de Castro
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../locallib.php');

$definitions = [
    'sitemaps' => [
        'mode' => core_cache\store::MODE_APPLICATION,
        'simplekeys' => true,
        'invalidationevents' => [
            '\core\event\course_category_created',
            '\core\event\course_category_deleted',
            '\core\event\course_category_updated',
            '\core\event\course_created',
            '\core\event\course_deleted',
            '\core\event\course_updated',
        ],
    ],
];
