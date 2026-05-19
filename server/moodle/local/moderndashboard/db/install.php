<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Post-install tasks for local_moderndashboard.
 *
 * @package    local_moderndashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Runs after the plugin is installed.
 * Sets sensible default configuration values.
 */
function xmldb_local_moderndashboard_install() {
    set_config('primarycolor',       '#2563eb', 'local_moderndashboard');
    set_config('secondarycolor',     '#1e293b', 'local_moderndashboard');
    set_config('accentcolor',        '#38bdf8', 'local_moderndashboard');
    set_config('courseslimit',       6,         'local_moderndashboard');
    set_config('showwelcome',        1,         'local_moderndashboard');
    set_config('showstats',          1,         'local_moderndashboard');
    set_config('showrecentactivity', 1,         'local_moderndashboard');
    set_config('enabledarkmode',     1,         'local_moderndashboard');
    set_config('welcomemessage',
        get_string('welcomedefault', 'local_moderndashboard'),
        'local_moderndashboard'
    );
    return true;
}
