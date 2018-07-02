<?php
/**
 * Privacy Subsystem implementation for tool_userbulkrolechange.
 *
 * @package    tool_userbulkrolechange
 * @copyright  2018 Raúl Martínez <raul@tresipunt.com>, Mitxel Moriana <mitxel@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_userbulkrolechange\privacy;

\defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for tool_userbulrolechange implementing null_provider.
 *
 * @copyright  2018 Raúl Martínez <raul@tresipunt.com>, Mitxel Moriana <mitxel@tresipunt.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
}
