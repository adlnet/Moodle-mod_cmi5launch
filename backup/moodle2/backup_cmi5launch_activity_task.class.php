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
 * Backup tasks for cmi5 plugin.
 *
 * @package    mod_cmi5launch
 * @copyright 2023 Megan Bohland
 * @copyright  Based on work by 2016 onward Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/cmi5launch/backup/moodle2/backup_cmi5launch_stepslib.php');

/**
 * cmi5launch backup activity task that provides the structure for the backup.
 *
 * @package    mod_cmi5launch
 * */
class backup_cmi5launch_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have.
     *
     * @return void
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have.
     *
     * @return void
     */
    protected function define_my_steps() {
        // Module cmi5launch only has one structure step.
        $this->add_step(new backup_cmi5launch_activity_structure_step('cmi5launch_structure', 'cmi5launch.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links.
     *
     * @param string $content
     * @return string encoded content
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of cmi5launchs.
        $search  = "/($base\/mod\/cmi5launch\/index.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@cmi5launchINDEX*$2@$', $content);

        $search  = "/($base\/mod\/cmi5launch\/view.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@cmi5launchVIEWBYID*$2@$', $content);

        return $content;
    }
}
