<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Library functions for local_moderndashboard.
 *
 * @package    local_moderndashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Hook into Moodle page rendering to inject dashboard enhancements.
 * Called automatically by Moodle on every page load.
 */
function local_moderndashboard_before_http_headers() {
    global $PAGE, $USER;

    // Only apply on the main dashboard page.
    if ($PAGE->pagetype !== 'my-index' && $PAGE->pagetype !== 'site-index') {
        return;
    }

    if (!isloggedin() || isguestuser()) {
        return;
    }

    // Load our AMD module to enhance the dashboard.
    $PAGE->requires->js_call_amd('local_moderndashboard/dashboard', 'init', [
        'userid' => $USER->id,
        'wwwroot' => (new moodle_url('/'))->out(false),
    ]);
}

/**
 * Inject CSS on the dashboard page.
 * Called automatically by Moodle theme rendering.
 */
function local_moderndashboard_before_footer() {
    global $PAGE;

    if ($PAGE->pagetype !== 'my-index' && $PAGE->pagetype !== 'site-index') {
        return;
    }

    if (!isloggedin() || isguestuser()) {
        return;
    }

    // Inject dashboard wrapper class for CSS targeting.
    $PAGE->requires->js_init_code("
        document.body.classList.add('moderndashboard-active');
        document.documentElement.style.setProperty('--md-primary', '#2563eb');
        document.documentElement.style.setProperty('--md-secondary', '#1e293b');
        document.documentElement.style.setProperty('--md-accent', '#38bdf8');
        document.documentElement.style.setProperty('--md-background', '#f8fafc');
    ");
}

/**
 * Get enrolled courses with additional metadata for dashboard display.
 *
 * @param int $userid The user ID.
 * @param int $limit Maximum number of courses to return.
 * @return array Array of course data objects.
 */
function local_moderndashboard_get_user_courses(int $userid, int $limit = 6): array {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');

    $courses = enrol_get_users_courses($userid, true, [
        'id', 'fullname', 'shortname', 'summary', 'category',
        'startdate', 'enddate', 'visible', 'timecreated',
    ]);

    if (empty($courses)) {
        return [];
    }

    $result = [];
    $count = 0;

    foreach ($courses as $course) {
        if ($count >= $limit) {
            break;
        }

        $context = context_course::instance($course->id);
        $coursedata = new stdClass();
        $coursedata->id = $course->id;
        $coursedata->fullname = format_string($course->fullname, true, ['context' => $context]);
        $coursedata->shortname = format_string($course->shortname, true, ['context' => $context]);
        $coursedata->url = (new moodle_url('/course/view.php', ['id' => $course->id]))->out(false);

        // Get course image.
        $coursedata->imageurl = local_moderndashboard_get_course_image($course);

        // Get completion progress.
        $coursedata->progress = local_moderndashboard_get_course_progress($userid, $course->id);
        $coursedata->progressint = (int) round($coursedata->progress);
        $coursedata->hasprogress = $coursedata->progress > 0;

        // Get category name.
        $category = $DB->get_record('course_categories', ['id' => $course->category], 'name');
        $coursedata->categoryname = $category ? format_string($category->name) : '';

        // Get teacher.
        $teachers = get_role_users(3, $context, false, 'u.id, u.firstname, u.lastname, u.picture', null, false, '', 0, 1);
        if (!empty($teachers)) {
            $teacher = reset($teachers);
            $coursedata->teachername = fullname($teacher);
            $coursedata->teacherpicture = local_moderndashboard_get_user_picture_url($teacher);
        } else {
            $coursedata->teachername = '';
            $coursedata->teacherpicture = '';
        }

        $coursedata->startdate = $course->startdate > 0
            ? userdate($course->startdate, get_string('strftimedatefullshort', 'langconfig'))
            : '';

        $result[] = $coursedata;
        $count++;
    }

    return $result;
}

/**
 * Get course thumbnail image URL.
 *
 * @param stdClass $course The course record.
 * @return string The image URL or default placeholder.
 */
function local_moderndashboard_get_course_image(stdClass $course): string {
    global $CFG;
    require_once($CFG->libdir . '/filelib.php');

    $context = context_course::instance($course->id);
    $fs = get_file_storage();

    // Try course overview files first.
    $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', 0, 'filename', false);
    foreach ($files as $file) {
        if ($file->is_valid_image()) {
            return moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                null,
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
        }
    }

    // Generate a gradient placeholder based on course id.
    $colors = [
        '#2563eb', '#7c3aed', '#db2777', '#dc2626',
        '#d97706', '#059669', '#0284c7', '#4f46e5'
    ];
    $color = $colors[$course->id % count($colors)];

    // Return a data URL for placeholder — in production, use a real placeholder image from pix/.
    return (new moodle_url('/local/moderndashboard/pix/course_placeholder.svg',
        ['color' => ltrim($color, '#')]))->out(false);
}

/**
 * Get course completion progress percentage for a user.
 *
 * @param int $userid The user ID.
 * @param int $courseid The course ID.
 * @return float Progress percentage (0-100).
 */
function local_moderndashboard_get_course_progress(int $userid, int $courseid): float {
    global $CFG;
    require_once($CFG->libdir . '/completionlib.php');

    $course = get_course($courseid);
    $completion = new completion_info($course);

    if (!$completion->is_enabled()) {
        return 0.0;
    }

    $percentage = \core_completion\progress::get_course_progress_percentage($course, $userid);
    return $percentage !== null ? (float) $percentage : 0.0;
}

/**
 * Get a user's profile picture URL.
 *
 * @param stdClass $user User record with id, picture, etc.
 * @return string The picture URL.
 */
function local_moderndashboard_get_user_picture_url(stdClass $user): string {
    global $PAGE;
    $userpicture = new user_picture($user);
    $userpicture->size = 50;
    return $userpicture->get_url($PAGE)->out(false);
}

/**
 * Get dashboard statistics for the current user.
 *
 * @param int $userid The user ID.
 * @return array Associative array of stats.
 */
function local_moderndashboard_get_user_stats(int $userid): array {
    global $DB;

    // Total enrolled courses.
    $totalcourses = count(enrol_get_users_courses($userid, true));

    // Completed courses.
    $completedcourses = $DB->count_records_sql(
        "SELECT COUNT(DISTINCT cc.course)
           FROM {course_completions} cc
          WHERE cc.userid = :userid
            AND cc.timecompleted IS NOT NULL",
        ['userid' => $userid]
    );

    // In-progress courses.
    $inprogress = max(0, $totalcourses - $completedcourses);

    // Badges earned.
    $badges = $DB->count_records_sql(
        "SELECT COUNT(bi.id)
           FROM {badge_issued} bi
          WHERE bi.userid = :userid
            AND bi.visible = 1",
        ['userid' => $userid]
    );

    // Total activity completions.
    $activities = $DB->count_records_sql(
        "SELECT COUNT(cmc.id)
           FROM {course_modules_completion} cmc
          WHERE cmc.userid = :userid
            AND cmc.completionstate >= 1",
        ['userid' => $userid]
    );

    // Days streak (last login activity count — simplified).
    $daysonline = $DB->count_records_sql(
        "SELECT COUNT(DISTINCT DATE(FROM_UNIXTIME(timecreated))) as days
           FROM {logstore_standard_log}
          WHERE userid = :userid
            AND timecreated > :since",
        [
            'userid' => $userid,
            'since' => strtotime('-30 days'),
        ]
    );

    return [
        'totalcourses'    => (int) $totalcourses,
        'completedcourses'=> (int) $completedcourses,
        'inprogress'      => (int) $inprogress,
        'badges'          => (int) $badges,
        'activities'      => (int) $activities,
        'daysonline'      => (int) $daysonline,
    ];
}

/**
 * Get recent activity for the current user.
 *
 * @param int $userid The user ID.
 * @param int $limit Maximum number of items.
 * @return array Array of recent activity items.
 */
function local_moderndashboard_get_recent_activity(int $userid, int $limit = 5): array {
    global $DB;

    $sql = "SELECT l.id, l.timecreated, l.action, l.component, l.contextinstanceid,
                   l.courseid, c.fullname as coursefullname
              FROM {logstore_standard_log} l
         LEFT JOIN {course} c ON c.id = l.courseid
             WHERE l.userid = :userid
               AND l.courseid > 0
               AND l.action IN ('viewed', 'submitted', 'graded', 'attempted')
          ORDER BY l.timecreated DESC";

    $records = $DB->get_records_sql($sql, ['userid' => $userid], 0, $limit);

    $result = [];
    foreach ($records as $record) {
        $item = new stdClass();
        $item->action = clean_param($record->action, PARAM_TEXT);
        $item->component = clean_param($record->component, PARAM_COMPONENT);
        $item->coursename = $record->coursefullname
            ? format_string($record->coursefullname)
            : get_string('unknowncourse', 'local_moderndashboard');
        $item->timeago = local_moderndashboard_time_ago($record->timecreated);
        $item->icon = local_moderndashboard_action_icon($record->action, $record->component);
        $item->courseurl = $record->courseid
            ? (new moodle_url('/course/view.php', ['id' => $record->courseid]))->out(false)
            : '#';
        $result[] = $item;
    }

    return $result;
}

/**
 * Return a human-readable "time ago" string.
 *
 * @param int $timestamp Unix timestamp.
 * @return string Human-readable time difference.
 */
function local_moderndashboard_time_ago(int $timestamp): string {
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return get_string('justnow', 'local_moderndashboard');
    } elseif ($diff < 3600) {
        $mins = (int)($diff / 60);
        return get_string('minutesago', 'local_moderndashboard', $mins);
    } elseif ($diff < 86400) {
        $hours = (int)($diff / 3600);
        return get_string('hoursago', 'local_moderndashboard', $hours);
    } else {
        $days = (int)($diff / 86400);
        return get_string('daysago', 'local_moderndashboard', $days);
    }
}

/**
 * Map an action/component to a Material-style icon name.
 *
 * @param string $action The log action.
 * @param string $component The component name.
 * @return string Icon identifier.
 */
function local_moderndashboard_action_icon(string $action, string $component): string {
    $icons = [
        'viewed'    => 'eye',
        'submitted' => 'check-circle',
        'graded'    => 'star',
        'attempted' => 'lightning-bolt',
    ];
    return $icons[$action] ?? 'activity';
}
