<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Language strings for local_moderndashboard.
 *
 * @package    local_moderndashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name.
$string['pluginname'] = 'Modern Dashboard';

// Settings.
$string['primarycolor']            = 'Primary Color';
$string['primarycolor_desc']       = 'Main brand color used for buttons, links, and highlights.';
$string['secondarycolor']          = 'Secondary Color';
$string['secondarycolor_desc']     = 'Used for backgrounds and secondary UI elements.';
$string['accentcolor']             = 'Accent Color';
$string['accentcolor_desc']        = 'Used for hover states and badges.';
$string['courseslimit']            = 'Courses to Display';
$string['courseslimit_desc']       = 'Number of enrolled courses to show on the dashboard.';
$string['showwelcome']             = 'Show Welcome Banner';
$string['showwelcome_desc']        = 'Display a personalized welcome banner at the top of the dashboard.';
$string['showstats']               = 'Show Statistics';
$string['showstats_desc']          = 'Display user learning statistics cards.';
$string['showrecentactivity']      = 'Show Recent Activity';
$string['showrecentactivity_desc'] = 'Display a recent activity timeline on the dashboard.';
$string['enabledarkmode']          = 'Enable Dark Mode Toggle';
$string['enabledarkmode_desc']     = 'Allow users to toggle between light and dark mode.';
$string['welcomemessage']          = 'Welcome Message';
$string['welcomemessage_desc']     = 'Personalized welcome message shown in the banner. Use {firstname} as a placeholder.';
$string['welcomedefault']          = 'Welcome back, {firstname}! Ready to continue learning?';

// Dashboard UI strings.
$string['goodmorning']         = 'Good morning';
$string['goodafternoon']       = 'Good afternoon';
$string['goodevening']         = 'Good evening';
$string['mylearning']          = 'My Learning';
$string['continuecourse']      = 'Continue';
$string['viewallcourses']      = 'View All Courses';
$string['recentactivity']      = 'Recent Activity';
$string['noactivity']          = 'No recent activity found.';
$string['nocourses']           = 'You are not enrolled in any courses yet.';
$string['explorecourses']      = 'Explore Courses';
$string['totalcourses']        = 'Total Courses';
$string['completedcourses']    = 'Completed';
$string['inprogress']          = 'In Progress';
$string['badgesearned']        = 'Badges Earned';
$string['activitiesfinished']  = 'Activities Done';
$string['daysonline']          = 'Days Active';
$string['progress']            = 'Progress';
$string['teacher']             = 'Instructor';
$string['startdate']           = 'Started';
$string['unknowncourse']       = 'Unknown course';
$string['darkmode']            = 'Dark Mode';
$string['lightmode']           = 'Light Mode';
$string['quickactions']        = 'Quick Actions';
$string['profile']             = 'My Profile';
$string['grades']              = 'My Grades';
$string['calendar']            = 'Calendar';
$string['messages']            = 'Messages';

// Time strings.
$string['justnow']    = 'just now';
$string['minutesago'] = '{$a} min ago';
$string['hoursago']   = '{$a}h ago';
$string['daysago']    = '{$a}d ago';

// Action labels.
$string['action_viewed']    = 'Viewed';
$string['action_submitted'] = 'Submitted';
$string['action_graded']    = 'Graded';
$string['action_attempted'] = 'Attempted';

// Privacy.
$string['privacy:metadata'] = 'The Modern Dashboard plugin stores user dark mode preference in the browser\'s localStorage and does not transmit data to external services.';
