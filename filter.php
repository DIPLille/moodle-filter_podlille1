<?php
/**
  * Filtre transformant une url PodLille1 en iframe pour intégrer la vidéo.
  * => équivalent au filtre multimedia pour dailymotion et youtube.
  *
  * @package    filter
  * @subpackage podlille1
  * @copyright  2014-2015 Gaël Mifsud / SEMM Université Lille1
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

		// Si aucun paramètre local ne définit l'url du serveur POD, on cherche un paramètre global, et on finit par une valeur par défaut si besoin.
		if (!isset($this->localconfig['url'])) {
			if (isset($CFG->filter_podlille1_url) && ($CFG->filter_podlille1_url != null) )
				$this->localconfig['url'] =  $CFG->filter_podlille1_url;
	        else
				$this->localconfig['url'] = "//pod.univ-lille1.fr";
        }

		// Vérification rapide si l'url ne se trouve pas dans le texte à filtrer, pour éviter un travail inutile ensuite.
		if (stripos($text, $this->localconfig['url']) === false) {
			return $text;
		}

		// En fontion de l'existence ou non de paramètres locaux et généraux, on définit les valeurs par défaut si besoin.
		// On récupère les paramètres locaux pour commencer, puis les paramètres généraux et enfin on initialise une valeur par défaut.
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
        $word = addslashes($this->localconfig['url']);          // On protège les slash de l'url utilisée ensuite dans la RegExp
        $text = htmlspecialchars_decode($text);                 // On enlève les &amps; et &quote; éventuellement ajoutés par l'éditeur riche
        $iframetagpattern   = '(?P<ifr>iframe\s+src\s*=\s*")?';                                     // Pour capturer une balise iframe
        $podpattern         = '((?:https?:)?('.$word.'\/[a-zA-Z\d\-\/]*video\/[a-zA-Z\d\-]+\/))';   // Pour capturer l'url de la vidéo
        $parampattern       = '(?:([(\?|\&)a-zA-Z_]*=)([a-zA-Z\d]*))?';                             // Pour capturer un paramètre d'url
        //$tousparampattern     = '(\?[a-zA-Z\d_=\&]*)*';                                           // Inutilisé : capturer tous les paramètres d'un coup

        // Il ne peut y avoir que quatre paramètres possibles : is_iframe, start, size et autoplay, donc on ne capture que quatre paramètres
        // Telle qu'est définie l'expression régulière ci-dessous, l'url de la vidéo sera en troisième position de capture, c'est impportant pour la suite !
        $pat = '('.$iframetagpattern.$podpattern.$parampattern.$parampattern.$parampattern.$parampattern.')';

		// On lance le remplacement proprement dit :
		$text       = preg_replace_callback($pat, array(&$this, 'filter_podlille1::filtre_podlille1'), $text, -1, $cpt);

		// On retourne le texte filtré
		return $text;
	}


	/**
	 * Fonction récupérant les résultats de preg_replace
	 * et utilisant la fonction callback pour faire le remplacement.
	 * Elle vérifie si on a déjà affaire à une iframe, auquel cas, on ne remplace rien.
	 */
    function filtre_podlille1($matches) {
        // On ne filtre pas une url pod déjà incluse dans une iframe
        $iframetagpattern   = '(iframe\s+src\s*=\s*")';
        if (preg_match($iframetagpattern, $matches[0])) {
            return $matches[0];
        } else {
			return remplace_url($matches, $this->localconfig);
        }
	}
}


/**
* Fonction renvoyant le texte avec iframe pour remplacer l'original.
* Elle vérifie si un début de lecture est indiqué ou pas.
*/
function remplace_url($matches, $localconfig) {

    // Telle qu'est définie l'expression régulière, l'url de la vidéo se trouve - toujpurs - en troisième position des résultats
    $u = $matches[3];

    // Par défaut, on définit les valeurs en fonction des réglages du filtre dans l'activité
    $size	    = '&size='.$localconfig['size'];
    $width	    = ' width="'.$localconfig['width'].'" ';
    $height     = ' height="'.$localconfig['height'].'" ';
    $autoplay   = '';
    $start      = '';

    // On récupère les éventuels paramètres dans l'url de la vidéo
    while(list(, $m)=each($matches)) {
        switch($m) {
        case "&start=":
        case "?start=":
            $start      = "&start=".current($matches);
            break;
        case "&size=":
        case "?size=":
            $size       = "&size=".current($matches);
            break;
        case "&autoplay=":
        case "?autoplay=":
            $autoplay   = "&autoplay=".current($matches);
            break;
        }
    }

    // On renvoie l'url filtrée en iframe avec tous les paramètres
    return '<iframe src="'.$u.'?is_iframe=true'.$size.$start.$autoplay.'"'.$width.$height.' style="padding: 0; margin: 0; border:0" allowfullscreen></iframe>';
}

?>
