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
 * Helper class for creating block instance content.
 *
 * @package    block_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dash\local;

use block_dash\local\configuration\configuration_interface;
use block_dash\local\configuration\configuration;
use block_dash\output\query_debug;
use block_dash\output\renderer;

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for creating block instance content.
 *
 * @package block_dash
 */
class block_builder {

    /**
     * @var configuration_interface
     */
    private $configuration;

    /**
     * @var \block_base
     */
    private $blockinstance;

    /**
     * block_builder constructor.
     *
     * @param \block_base $blockinstance
     * @throws \coding_exception
     */
    protected function __construct(\block_base $blockinstance) {
        $this->blockinstance = $blockinstance;
        $this->configuration = configuration::create_from_instance($blockinstance);
    }

    /**
     * Get configuration.
     *
     * @return configuration_interface
     */
    public function get_configuration() {
        return $this->configuration;
    }

    /**
     * Get content object for block instance.
     *
     * @return \stdClass
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    public function get_block_content() {
        global $OUTPUT, $PAGE;

        /** @var renderer $renderer */
        $renderer = $PAGE->get_renderer('block_dash');

        $text = '';

        if ($this->configuration->is_fully_configured()) {
            $bb = self::create($this->blockinstance);

            $bb->get_configuration()->get_data_source()->get_paginator()->set_current_page(0);

            $data = [
                'preloaded' => $renderer->render_data_source($bb->get_configuration()->get_data_source()),
                'block_instance_id' => $this->blockinstance->instance->id,
                'block_context_id' => $this->blockinstance->context->id,
                'editing' => $PAGE->user_is_editing() &&
                    has_capability('block/dash:addinstance', $this->blockinstance->context)
            ];

            if (isset($this->blockinstance->config->header_content)) {
                $data['header_content'] = format_text($this->blockinstance->config->header_content['text'],
                        $this->blockinstance->config->header_content['format']);
            }

            if (isset($this->blockinstance->config->footer_content)) {
                $data['footer_content'] = format_text($this->blockinstance->config->footer_content['text'],
                    $this->blockinstance->config->footer_content['format']);
            }

            $text .= $OUTPUT->render_from_template('block_dash/block', $data);

            if (is_siteadmin()) {
                [$sql, $params] = $bb->get_configuration()->get_data_source()->get_query()->get_sql_and_params();
                $text .= $renderer->render(new query_debug($sql, $params));
            }
        } else {
            $text .= \html_writer::tag('p', get_string('editthisblock', 'block_dash'));
        }

        $content = new \stdClass();
        $content->text = $text;

        return $content;
    }

    /**
     * Create block builder.
     *
     * @param \block_base $blockinstance
     * @return block_builder
     * @throws \coding_exception
     */
    public static function create(\block_base $blockinstance) {
        return new block_builder($blockinstance);
    }
}
