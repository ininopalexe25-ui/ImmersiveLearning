<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Renderable for the modern dashboard page.
 *
 * @package    local_moderndashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moderndashboard\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use stdClass;
use moodle_url;

/**
 * Class dashboard_page
 *
 * Collects all data needed to render the modern dashboard and exports
 * it to a Mustache template via export_for_template().
 */
class dashboard_page implements renderable, templatable {

    /** @var int User ID */
    private int $userid;

    /**
     * Constructor.
     *
     * @param int $userid The user to build the dashboard for.
     */
    public function __construct(int $userid) {
        $this->userid = $userid;
    }

    /**
     * Export all template data.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass Template context.
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $USER, $DB, $CFG, $PAGE;

        $config = get_config('local_moderndashboard');
        $data = new stdClass();

        // ---- User greeting ----
        $data->firstname     = clean_param($USER->firstname, PARAM_TEXT);
        $data->fullname      = fullname($USER);
        $data->userpictureurl = \local_moderndashboard_get_user_picture_url($USER);
        $data->greeting      = $this->get_greeting();
        $data->welcomemsg    = str_replace(
            '{firstname}',
            $data->firstname,
            $config->welcomemessage ?? get_string('welcomedefault', 'local_moderndashboard')
        );

        // ---- Feature flags ----
        $data->showwelcome        = !empty($config->showwelcome);
        $data->showstats          = !empty($config->showstats);
        $data->showrecentactivity = !empty($config->showrecentactivity);
        $data->enabledarkmode     = !empty($config->enabledarkmode);

        // ---- Statistics ----
        if ($data->showstats) {
            $stats = \local_moderndashboard_get_user_stats($this->userid);
            $data->totalcourses     = $stats['totalcourses'];
            $data->completedcourses = $stats['completedcourses'];
            $data->inprogress       = $stats['inprogress'];
            $data->badges           = $stats['badges'];
            $data->activities       = $stats['activities'];
            $data->daysonline       = $stats['daysonline'];
        }

        // ---- Enrolled courses ----
        $limit          = (int)($config->courseslimit ?? 6);
        $courses        = \local_moderndashboard_get_user_courses($this->userid, $limit);
        $data->courses  = array_values($courses);
        $data->hascourses = !empty($data->courses);
        $data->coursescount = count($data->courses);

        // ---- Recent activity ----
        if ($data->showrecentactivity) {
            $activity            = \local_moderndashboard_get_recent_activity($this->userid, 5);
            $data->recentactivity = array_values($activity);
            $data->hasrecentactivity = !empty($data->recentactivity);
        }

        // ---- Quick actions ----
        $data->quickactions = $this->get_quick_actions();

        // ---- CSS custom properties from settings ----
        $data->primarycolor   = clean_param($config->primarycolor   ?? '#2563eb', PARAM_TEXT);
        $data->secondarycolor = clean_param($config->secondarycolor ?? '#1e293b', PARAM_TEXT);
        $data->accentcolor    = clean_param($config->accentcolor    ?? '#38bdf8', PARAM_TEXT);

        // ---- URLs ----
        $data->wwwroot         = (new moodle_url('/'))->out(false);
        $data->coursesurl      = (new moodle_url('/my/courses.php'))->out(false);
        $data->profileurl      = (new moodle_url('/user/profile.php'))->out(false);
        $data->gradesurl       = (new moodle_url('/grade/report/overview/index.php'))->out(false);
        $data->calendarurl     = (new moodle_url('/calendar/view.php'))->out(false);
        $data->messagesurl     = (new moodle_url('/message/index.php'))->out(false);

        return $data;
    }

    /**
     * Return a time-of-day greeting string.
     *
     * @return string
     */
    private function get_greeting(): string {
        $hour = (int)date('G');
        if ($hour < 12) {
            return get_string('goodmorning', 'local_moderndashboard');
        } elseif ($hour < 17) {
            return get_string('goodafternoon', 'local_moderndashboard');
        } else {
            return get_string('goodevening', 'local_moderndashboard');
        }
    }

    /**
     * Build quick action items for the dashboard.
     *
     * @return array
     */
    private function get_quick_actions(): array {
        return [
            [
                'label' => get_string('profile', 'local_moderndashboard'),
                'url'   => (new moodle_url('/user/profile.php'))->out(false),
                'icon'  => 'user',
                'color' => 'blue',
            ],
            [
                'label' => get_string('grades', 'local_moderndashboard'),
                'url'   => (new moodle_url('/grade/report/overview/index.php'))->out(false),
                'icon'  => 'chart-bar',
                'color' => 'green',
            ],
            [
                'label' => get_string('calendar', 'local_moderndashboard'),
                'url'   => (new moodle_url('/calendar/view.php'))->out(false),
                'icon'  => 'calendar',
                'color' => 'purple',
            ],
            [
                'label' => get_string('messages', 'local_moderndashboard'),
                'url'   => (new moodle_url('/message/index.php'))->out(false),
                'icon'  => 'chat',
                'color' => 'orange',
            ],
        ];
    }
}
