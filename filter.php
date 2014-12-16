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
  * Cette classe permet de filtrer les url podlille1 pour les remplacer par une iframe intégrant la vidéo
  */
class filter_podlille1 extends moodle_text_filter {
	// Filtre PodLille pour Moodle
	public function filter($text, array $options = array()) {
		global $CFG;

		// En fontion de l'existence ou non de paramètres locaux et généraux, on définit les valeurs par défaut si besoin.
		// On récupère les paramètres locaux pour commencer, puis les paramètres généraux et enfin on initialise une valeur par défaut.
		if (!isset($this->localconfig['url'])) {
			if (isset($CFG->filter_podlille1_url) && ($CFG->filter_podlille1_url != null) )
				$this->localconfig['url'] =  $CFG->filter_podlille1_url;
	        else
				$this->localconfig['url'] = "http://pod.univ-lille1.fr";
        }

		// Vérification rapide si l'url se trouve dans le texte à filtrer, pour éviter un travail inutile plus tard.
		if (strpos($text, $this->localconfig['url']) === false) {
			return $text;
		}

		if (!isset($this->localconfig['size'])) {
			if (isset($CFG->filter_podlille1_size) && ($CFG->filter_podlille1_size != null) )
				$this->localconfig['size'] =  $CFG->filter_podlille1_size;
	        else
				$this->localconfig['size'] = "480";
        }
		if (!isset($this->localconfig['width'])) {
			if (isset($CFG->filter_podlille1_width) && ($CFG->filter_podlille1_width != null) )
	            $this->localconfig['width'] = $CFG->filter_podlille1_width;
    	    else
        	    $this->localconfig['width'] = "854";
		}
		if (!isset($this->localconfig['height'])) {
			if (isset($CFG->filter_podlille1_height) && ($CFG->filter_podlille1_height != null) )
            	$this->localconfig['height'] = $CFG->filter_podlille1_height;
	        else
    	        $this->localconfig['height'] = "480";
		}
		$matches = array();
		
		/// Expression régulière pour définir une url podlille1 standard et éviter celles déjà contenues dans une iframe
		// 	$pattern = '((iframe\s+src\s*=\s*")?(http://pod.univ-lille1.fr/video/[a-zA-Z\d\-]+\/(\?start=\d+)?))';
		$word = addslashes($this->localconfig['url']);
//		$pattern = '((iframe\s+src\s*=\s*")?('.$word.'[a-zA-Z\d\-]+\/(\?start=\d+)?))';
		$pattern = '((iframe\s+src\s*=\s*")?('.$word.'\/[a-zA-Z\d\-\/]*video\/[a-zA-Z\d\-]+\/(\?start=\d+)?))';
		// On lance le remplacement proprement dit :
		$text = preg_replace_callback($pattern, array(&$this, 'filtre_podlille1'), $text);
		
		// On retourne le texte filtré
		return $text;
	}

	/**
	 * Fonction récupérant les résultats de preg_replace
	 * et utilisant la fonction callback pour faire le remplacement.
	 * Elle vérifie si on a déjà affaire à une iframe, auquel cas, on ne remplace rien.
	 */
	function filtre_podlille1($matches) {
		if (strpos($matches[0], 'iframe') === false) 
			return remplace_url($matches[0], $this->localconfig);
		else 
			return $matches[0];
	}
}

/**
* Fonction renvoyant le texte avec iframe pour remplacer l'original.
* Elle vérifie si un début de lecture est indiqué ou pas.
*/
function remplace_url($u, $localconfig) {
    $size	= $localconfig['size'];
    $width	= $localconfig['width']."px";
    $height = $localconfig['height']."px";

	// Si un départ de vidéo est indiqué, on l'intègre dans l'iframe. [NE SERT À RIEN POUR LE MOMENT !]
	if (strpos($u, '?start=') === false) 
		return '<iframe src="'.$u.'?is_iframe=true&size='.$size.'" width="'.$width.'" height="'.$height.'" style="padding: 0; margin: 0; border:0" allowfullscreen ></iframe>';
	else
		return '<iframe src="'.$u.'&is_iframe=true&size='.$size.'" width="'.$width.'" height="'.$height.'" style="padding: 0; margin: 0; border:0" allowfullscreen ></iframe>';
}

?>
