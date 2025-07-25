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
 * Defines the version of cmi5launch
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package mod_cmi5launch
 * @copyright  2024 Megan Bohland
 * @copyright  Based on work by 2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025070116;      // The current module version (Date: YYYYMMDDXX).

$plugin->requires  = 2023100900;      // Requires at least Moodle 4.3 version.
$plugin->cron      = 0;               // Period for cron to check this module (secs).
$plugin->component = 'mod_cmi5launch'; // To check on upgrade, that module sits in correct place.
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '1.1.0 (Build: 2025070116)';
$plugin->php       = '8.0.0'; // Requires PHP 8.0+.
