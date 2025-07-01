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
 * Page to hold constants for cmi5launch.
 *
 * @copyright  2025 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_cmi5launch
 */


define('CMI5LAUNCH_PLAYER_V1', '/api/v1/');
define( 'CMI5LAUNCH_PLAYER_TENANT_URL', '/api/v1/tenant');
define('CMI5LAUNCH_PLAYER_COURSE_URL', '/api/v1/course');
define('CMI5LAUNCH_PLAYER_REGISTRATION_URL', '/api/v1/registration');
define('CMI5LAUNCH_PLAYER_AUTH_URL', '/api/v1/auth');
define('CMI5LAUNCH_LAUNCH_URL', '/launch-url/');
define('CMI5LAUNCH_PLAYER_SESSION_URL', '/api/v1/session/');

// Define constants for player connections.
// Grade stuff.
define('MOD_CMI5LAUNCH_GRADE_AUS', '0');
define('MOD_CMI5LAUNCH_GRADE_HIGHEST', '1');
define('MOD_CMI5LAUNCH_GRADE_AVERAGE', '2');
define('MOD_CMI5LAUNCH_GRADE_SUM', '3');

define('MOD_CMI5LAUNCH_HIGHEST_ATTEMPT', '0');
define('MOD_CMI5LAUNCH_AVERAGE_ATTEMPT', '1');
define('MOD_CMI5LAUNCH_FIRST_ATTEMPT', '2');
define('MOD_CMI5LAUNCH_LAST_ATTEMPT', '3');

define('CMI5_FORCEATTEMPT_NO', 0);
define('CMI5_FORCEATTEMPT_ONCOMPLETE', 1);
define('CMI5_FORCEATTEMPT_ALWAYS', 2);

define('CMI5_UPDATE_NEVER', '0');
define('CMI5_UPDATE_EVERYDAY', '2');
define('CMI5_UPDATE_EVERYTIME', '3');








