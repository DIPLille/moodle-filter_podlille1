<?php
/**
 * Filtre transformant une url PodLille1 en iframe pour intégrer la vidéo.
 * => équivalent au filtre multimedia pour dailymotion et youtube.
 *
 * @package    filter
 * @subpackage podlille1
 * @copyright  2014 Gaël Mifsud / SEMM Université Lille1
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
  * Cette classe définit les éléments du formulaire pour les paramètres locaux aux activités.
  */
class podlille1_filter_local_settings_form extends filter_local_settings_form {
    protected function definition_inner($mform) {
        $mform->addElement('text', 'url', get_string('url', 'filter_podlille1'), array('url' => 32));
        $mform->setType('url', PARAM_NOTAGS);
        $mform->addHelpButton('url', 'url', 'filter_podlille1');
        $mform->addElement('text', 'size', get_string('size', 'filter_podlille1'), array('size' => 32));
        $mform->setType('size', PARAM_NOTAGS);
        $mform->addHelpButton('size', 'size', 'filter_podlille1');
        $mform->addElement('text', 'width', get_string('width', 'filter_podlille1'), array('size' => 32));
        $mform->setType('width', PARAM_NOTAGS);
        $mform->addHelpButton('width', 'width', 'filter_podlille1');
        $mform->addElement('text', 'height', get_string('height', 'filter_podlille1'), array('size' => 32));
        $mform->setType('height', PARAM_NOTAGS);
        $mform->addHelpButton('height', 'height', 'filter_podlille1');
    }
}

?>
