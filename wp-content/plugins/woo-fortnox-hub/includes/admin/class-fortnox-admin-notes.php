<?php

/**
 * WooCommerce Admin (Dashboard) Giving feedback notes provider.
 *
 * Adds notes to the merchant's inbox about giving feedback.
 *
 * @package WooCommerce Admin
 */

namespace Automattic\WooCommerce\Admin\Notes;

defined('ABSPATH') || exit;

/**
 * WC_Admin_Notes_Giving_Feedback_Notes
 */
class WC_Admin_Notes_Fortnox
{
    /**
     * Note traits.
     */
    use NoteTraits;

    /**
     * Add notes for admin giving feedback.
     */
    public static function add_activity_panel_inbox_note($id, $notice, $type = WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL)
    {
        self::possibly_add_activity_panel_inbox_note($id, $notice, $type);
    }

    /**
     *
     * type: 'error', 'warning', 'update', 'info'
     */
    protected static function possibly_add_activity_panel_inbox_note($id, $notice, $type)
    {

        $data_store = \WC_Data_Store::load('admin-note');

        $note_ids = $data_store->get_notes_with_name('woo-fortnox-hub-notice-' . $id);

        if (!empty($note_ids)) {
            $note = new WC_Admin_Note(reset($note_ids));
        } else {
            $note = new WC_Admin_Note();
        }

        $note->set_title(__('Fortnox Hub', 'woo-fortnox-hub'));
        $note->set_content($notice);
        $note->set_content_data((object) array());
        $note->set_type($type);
        $note->set_layout('banner');
        $note->set_name('woo-fortnox-hub-notice-' . $id);
        $note->set_source('woo-fortnox-hub');
        $note->save();
    }
}
