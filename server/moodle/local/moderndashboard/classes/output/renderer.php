<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Renderer for local_moderndashboard.
 *
 * @package    local_moderndashboard
 * @copyright  2024 Modern Moodle
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_moderndashboard\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Class renderer
 *
 * Renders the modern dashboard using Mustache templates.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the full modern dashboard page.
     *
     * @param dashboard_page $page The renderable dashboard page object.
     * @return string HTML output.
     */
    public function render_dashboard_page(dashboard_page $page): string {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_moderndashboard/dashboard', $data);
    }
}
