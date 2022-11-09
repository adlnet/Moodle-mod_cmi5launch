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
 * This file keeps track of upgrades to the cmi5launch module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package mod_cmi5launch
 * @copyright  2013 Andrew Downes
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute cmi5launch upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_cmi5launch_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013083100) {
        // Define field cmi5activityid to be added to cmi5launch.
        $table = new xmldb_table('cmi5launch');
        $field = new xmldb_field('cmi5activityid', XMLDB_TYPE_TEXT, '1333', null, XMLDB_NOTNULL, null, null, 'cmi5launchurl');

        // Add field cmi5activityid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2013083100, 'cmi5launch');
    }

    if ($oldversion < 2013111600) {
        // Define field cmi5verbid to be added to cmi5launch.
        $table = new xmldb_table('cmi5launch');
        $field = new xmldb_field('cmi5verbid', XMLDB_TYPE_TEXT, '1333', null, XMLDB_NOTNULL, null, null, 'cmi5launchurl');

        // Add field cmi5activityid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2013111600, 'cmi5launch');
    }

    if ($oldversion < 2015032500) {

        // Define field overridedefaults to be added to cmi5launch.
        $table = new xmldb_table('cmi5launch');
        $field = new xmldb_field('overridedefaults', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'cmi5verbid');

        // Conditionally launch add field overridedefaults.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table cmi5launch_lrs to be created.
        $table = new xmldb_table('cmi5launch_lrs');

        // Adding fields to table cmi5launch_lrs.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cmi5launchid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lrsendpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lrsauthentication', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lrslogin', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lrspass', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('lrsduration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table cmi5launch_lrs.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table cmi5launch_lrs.
        $table->add_index('cmi5launchid', XMLDB_INDEX_NOTUNIQUE, array('cmi5launchid'));

        // Conditionally launch create table for cmi5launch_lrs.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // cmi5launch savepoint reached.
        upgrade_mod_savepoint(true, 2015032500, 'cmi5launch');
    }

    if ($oldversion < 2015033100) {

        unset_config('cmi5launchlrsversion', 'cmi5launch');
        unset_config('cmi5launchlrauthentication', 'cmi5launch');

        upgrade_mod_savepoint(true, 2015033100, 'cmi5launch');
    }

    if ($oldversion < 2015112702) {
        // Define field cmi5activityid to be added to cmi5launch.
        $table = new xmldb_table('cmi5launch');
        $field = new xmldb_field('cmi5multipleregs', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'cmi5verbid');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('cmi5launch_lrs');
        $field = new xmldb_field('useactoremail', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table->add_field('customacchp', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2015112702, 'cmi5launch');
    }

    if ($oldversion < 2016121200) {
        $table = new xmldb_table('cmi5launch');
        $field = new xmldb_field('cmi5expiry', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 365);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2018103000) {
        $table = new xmldb_table('cmi5launch_credentials');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table, $continue = true, $feedback = true);
        }

        $table = new xmldb_table('cmi5launch_lrs');
        $field = new xmldb_field('watershedlogin', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, $continue = true, $feedback = true);
        }

        $field = new xmldb_field('watershedpass', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, $continue = true, $feedback = true);
        }

        upgrade_mod_savepoint(true, 2018103000, 'cmi5launch');
    }

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}
