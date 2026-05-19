<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Admin settings for local_moderndashboard.
 *
 * @package    local_moderndashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_moderndashboard',
        get_string('pluginname', 'local_moderndashboard')
    );

    $ADMIN->add('localplugins', $settings);

    // Primary color.
    $settings->add(new admin_setting_configcolourpicker(
        'local_moderndashboard/primarycolor',
        get_string('primarycolor', 'local_moderndashboard'),
        get_string('primarycolor_desc', 'local_moderndashboard'),
        '#2563eb'
    ));

    // Secondary color.
    $settings->add(new admin_setting_configcolourpicker(
        'local_moderndashboard/secondarycolor',
        get_string('secondarycolor', 'local_moderndashboard'),
        get_string('secondarycolor_desc', 'local_moderndashboard'),
        '#1e293b'
    ));

    // Accent color.
    $settings->add(new admin_setting_configcolourpicker(
        'local_moderndashboard/accentcolor',
        get_string('accentcolor', 'local_moderndashboard'),
        get_string('accentcolor_desc', 'local_moderndashboard'),
        '#38bdf8'
    ));

    // Number of courses to show on dashboard.
    $settings->add(new admin_setting_configselect(
        'local_moderndashboard/courseslimit',
        get_string('courseslimit', 'local_moderndashboard'),
        get_string('courseslimit_desc', 'local_moderndashboard'),
        6,
        [3 => '3', 4 => '4', 6 => '6', 8 => '8', 12 => '12']
    ));

    // Enable welcome banner.
    $settings->add(new admin_setting_configcheckbox(
        'local_moderndashboard/showwelcome',
        get_string('showwelcome', 'local_moderndashboard'),
        get_string('showwelcome_desc', 'local_moderndashboard'),
        1
    ));

    // Enable stats widget.
    $settings->add(new admin_setting_configcheckbox(
        'local_moderndashboard/showstats',
        get_string('showstats', 'local_moderndashboard'),
        get_string('showstats_desc', 'local_moderndashboard'),
        1
    ));

    // Enable recent activity.
    $settings->add(new admin_setting_configcheckbox(
        'local_moderndashboard/showrecentactivity',
        get_string('showrecentactivity', 'local_moderndashboard'),
        get_string('showrecentactivity_desc', 'local_moderndashboard'),
        1
    ));

    // Enable dark mode toggle.
    $settings->add(new admin_setting_configcheckbox(
        'local_moderndashboard/enabledarkmode',
        get_string('enabledarkmode', 'local_moderndashboard'),
        get_string('enabledarkmode_desc', 'local_moderndashboard'),
        1
    ));

    // Custom welcome message.
    $settings->add(new admin_setting_configtext(
        'local_moderndashboard/welcomemessage',
        get_string('welcomemessage', 'local_moderndashboard'),
        get_string('welcomemessage_desc', 'local_moderndashboard'),
        get_string('welcomedefault', 'local_moderndashboard'),
        PARAM_TEXT
    ));
}
