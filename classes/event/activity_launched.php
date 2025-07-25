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
 * The mod_cmi5launch activity launched event.
 * @copyright  2013 Andrew Downes
 * @package    mod_cmi5launch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\event;

/**
 * The mod_cmi5launch activity launched event class.
 *
 * @property-read array $other {
 *      Extra information about event properties.
 *
 *      - string loadedcontent: A reference to the content loaded.
 *      - int instanceid: (optional) Instance id of the cmi5 activity.
 * }
 *
 * @package    mod_cmi5launch
 * @since      Moodle 2.7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_launched extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'cmi5launch';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' launched the activity with id '$this->objectid' for the cmi5 with " .
            "course module id '$this->contextinstanceid'.";
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventactivitylaunched', 'mod_cmi5launch');
    }

    /**
     * Get URL related to the action
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url(
            '/mod/cmi5launch/launch.php',
            ['id' => $this->contextinstanceid, 'activityid' => $this->objectid]
        );
    }

    /**
     * Replace add_to_log() statement.
     *
     * @return array of parameters to be passed to legacy add_to_log() function.
     */
    protected function get_legacy_logdata() {
        return [$this->courseid, 'cmi5launch', 'launch', 'launch.php?id=' . $this->contextinstanceid,
                '', $this->contextinstanceid];
    }

}
