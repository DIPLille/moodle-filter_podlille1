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

// Définition des paramètres de configuration généraux du filtre
if ($ADMIN->fulltree) {
	// L'URL de la page contenant la vidéo. Elle sera remplacée par le code <iframe qui convient.
	$settings->add(new admin_setting_configtext('filter_podlille1_url',
				get_string('url', 'filter_podlille1'),
				get_string('url_desc', 'filter_podlille1'), 'http://pod.univ-lille1.fr', PARAM_NOTAGS));
	// Taille de l'iframe contenant la vidéo 
	$settings->add(new admin_setting_configtext('filter_podlille1_size',
				get_string('size', 'filter_podlille1'),
				get_string('size_desc', 'filter_podlille1'), '480', PARAM_NOTAGS));
	// Largeur de la vidéo en px
	$settings->add(new admin_setting_configtext('filter_podlille1_width',
				get_string('width', 'filter_podlille1'),
				get_string('width_desc', 'filter_podlille1'), '854', PARAM_NOTAGS));
	// Hauteur de la vidéo en px
	$settings->add(new admin_setting_configtext('filter_podlille1_height',
				get_string('height', 'filter_podlille1'),
				get_string('height_desc', 'filter_podlille1'), '480', PARAM_NOTAGS));
}

?>
