<?php
/**
  * Filtre transformant une url PodLille1 en iframe pour intégrer la vidéo,
  * à la manière du filtre multimedia pour dailymotion et youtube.
  *
  * @package    filter
  * @subpackage podlille1
  * @copyright  2014-2016 Gaël Mifsud / SEMM Université Lille1
  * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
  */

defined('MOODLE_INTERNAL') || die();

/**
 * Cette classe permet de filtrer les url podlille1 pour les remplacer par une iframe intégrant la vidéo dans une activité Moodle
 */
class filter_podlille1 extends moodle_text_filter {
    
    /**
     * Filtre PodLille pour Moodle
     *
     * @param string $text le texte contenant éventuellement une url pod à filtrer
     * @return string le texte dans lequel les url pod sont remplacées par un code iframe
     */
    public function filter($text, array $options = array()) {
        global $CFG, $COURSE, $PAGE;
        
        // Initialisation des valeurs par défauts au cas où l'administrateur n'aurait pas correctement renseignée la configuration globale du filtre
        $config['url']      = "pod.univ-lille1.fr";
        $config['size']     = 480;
        $config['width']    = 854;
        $config['height']   = 480;
        $courseconfig = array();
        
        // On récupère l'ID du cours actuel pour ensuite récupérer le context dans lequel s'exécute le filtre
        $courseid       = (isset($COURSE->id)) ? $COURSE->id : null;
        $coursecontext  = context_course::instance($courseid);
        
        // On récupère les paramètres du filtre dans le contexte d'exécution actuel
        $courseconfig = get_active_filters($coursecontext->id);
        
        // Si aucun paramètre local ne définit l'url du serveur pod, on cherche d'abord dans le contexte, puis enfin une valeur globale en dernier recours.
        if (isset($this->localconfig['url']))
            $config['url'] = $this->localconfig['url'];
        elseif (isset($courseconfig['url']))
            $config['url'] = $courseconfig['url'];
        elseif (isset($CFG->filter_podlille1_url) && ($CFG->filter_podlille1_url != null) )
            $config['url'] =  $CFG->filter_podlille1_url;
        
        
        // Vérification rapide si l'url ne se trouve pas dans le texte à filtrer, pour éviter un travail inutile ensuite.
        if (stripos($text, $config['url']) === false) {
            return $text;
        }
        
        // En fontion de l'existence ou non de paramètres locaux, contextuels et enfin généraux, on définit les valeurs des paramètres de l'url.
        if (isset($this->localconfig['size']))
            $config['size'] = $this->localconfig['size'];
        elseif (isset($courseconfig['size']))
            $config['size'] = $courseconfig['size'];
        elseif (isset($CFG->filter_podlille1_size) && ($CFG->filter_podlille1_size != null) )
            $config['size'] =  $CFG->filter_podlille1_size;
        
        if (isset($this->localconfig['width']))
            $config['width'] = $this->localconfig['width'];
        elseif (isset($courseconfig['width']))
            $config['width'] = $courseconfig['width'];
        elseif (isset($CFG->filter_podlille1_width) && ($CFG->filter_podlille1_width != null) )
            $config['width'] =  $CFG->filter_podlille1_width;
        
        if (isset($this->localconfig['height']))
            $config['height'] = $this->localconfig['height'];
        elseif (isset($courseconfig['height']))
            $config['height'] = $courseconfig['height'];
        elseif (isset($CFG->filter_podlille1_height) && ($CFG->filter_podlille1_height != null) )
            $config['height'] =  $CFG->filter_podlille1_height;
        
        // On stocke les valeurs dans la variable localconfig pour pouvoir les récupérer dans la fonction de callback plus tard.
        $this->localconfig['config'] = $config;
        
        $matches = array();
        
        /// Expression régulière pour définir une url podlille1 standard et éviter celles déjà contenues dans une iframe
        $word = addslashes($config['url']);          // On protège les slash de l'url utilisée ensuite dans la RegExp
        $text = htmlspecialchars_decode($text);      // On enlève les &amps; et &quote; éventuellement ajoutés par l'éditeur riche
        $iframetagpattern   = '(?P<ifr>iframe\s+src\s*=\s*")?';                                         // Pour capturer une balise iframe avec la clé "ifr"
        $podpattern         = '((?:https?\:)?(?:\/\/)?(?P<pod>'.$word.'\/[a-zA-Z\d\-\/_]*video\/[a-zA-Z\d\-_]+\/))';   // Pour capturer l'url de la vidéo
        $parampattern       = '(?:([(\?|\&)a-zA-Z_]*=)([a-zA-Z\d]*))?';                                 // Pour capturer un paramètre d'url
        //$tousparampattern     = '(\?[a-zA-Z\d_=\&]*)*';                                               // Inutilisé : capturer tous les paramètres d'un coup
        
        // Il ne peut y avoir que quatre paramètres possibles : is_iframe, start, size et autoplay, donc on ne capture que quatre paramètres
        // Telle qu'est définie l'expression régulière ci-dessous, l'url de la vidéo sera capturée avec la clé "pod", c'est important pour la suite !
        $pat = '('.$iframetagpattern.$podpattern.$parampattern.$parampattern.$parampattern.$parampattern.')';
        // On lance le remplacement proprement dit :
        $text       = preg_replace_callback($pat, array(&$this, 'filter_podlille1::filtre_podlille1'), $text, -1, $cpt);
        
        // On retourne le texte filtré
        return $text;
    }
    
    
    /**
     * Fonction récupérant les résultats de preg_replace et
     * utilisant la fonction callback pour faire le remplacement.
     * Elle vérifie si on a déjà affaire à une iframe, auquel cas, on ne remplace rien.
     *
     * @param array $matches un tableau contenant toutes les captures de l'expression régulière.
     * @return string le texte de l'iframe remplaçant l'url de la vidéo
     */
    function filtre_podlille1($matches) {
        // On ne filtre pas une url pod déjà incluse dans une iframe
        if ($matches["ifr"]) {
            return $matches[0];
        } else {
            // On filtre chaque url pod trouvée par l'expression régulière
            return remplace_url($matches, $this->localconfig['config']);
        }
    }
}


/**
 * Fonction renvoyant le texte avec iframe pour remplacer l'original.
 *
 * @param array $matches un tableau contenant toutes les captures de l'expression régulière.
 * @param array $config le tableau contenant les paramètres par défaut pour l'url
 * @return string le texte de l'iframe remplaçant l'url de la vidéo
*/
function remplace_url($matches, $config) {
    
    // Telle qu'est définie l'expression régulière, l'url de la vidéo se trouve - toujours - en troisième position des résultats
    $u = $matches['pod'];
    
    // Par défaut, on définit les valeurs en fonction des réglages du filtre dans l'activité
    $width      = ' width="'.$config['width'].'" ';
    $height     = ' height="'.$config['height'].'" ';
    $size       = '&size='.$config['size'];
    $autoplay   = '';
    $start      = '';
    
    // On récupère les éventuels paramètres pour l'url de la vidéo
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
    return '<iframe src="//'.$u.'?is_iframe=true'.$size.$start.$autoplay.'"'.$width.$height.' style="padding: 0; margin: 0; border:0" allowfullscreen></iframe>';
}


/**
 * https://docs.moodle.org/dev/Filter_enable/disable_by_context#Getting_filter_configuration
 *
 * Get the list of active filters, in the order that they should be used
 * for a particular context.
 *
 * @param object $context a context
 * @return array an array where the keys are the filter names and the values are any local
 *      configuration for that filter, as an array of name => value pairs
 *      from the filter_config table. In a lot of cases, this will be an
 *      empty array.
 */
function get_active_filters($contextid) {
    global $DB;
    
    $sql = "SELECT fc.id, active.FILTER, fc.name, fc.VALUE
            FROM (SELECT f.FILTER
            FROM {filter_active} f
            JOIN {context} ctx ON f.contextid = ctx.id
            WHERE ctx.id IN ($contextid) AND f.FILTER LIKE 'podlille1'
            GROUP BY FILTER
            HAVING MAX(f.active * ctx.depth) > -MIN(f.active * ctx.depth)
            ORDER BY MAX(f.sortorder)) active
            LEFT JOIN {filter_config} fc ON fc.FILTER = active.FILTER AND fc.contextid = $contextid";
    
    $courseconfig = array();
    
    if ($results = $DB->get_records_sql($sql, null)) {
        // On récupère les paramètres du filtre, locaux au contexte dont l'ID a été passé en paramètre
        foreach ($results as $res) {
            if ($res->filter=="podlille1") {
                switch($res->name) {
                case "url":
                    $courseconfig['url']   = $res->value;
                    break;
                case "size":
                    $courseconfig['size']  = $res->value;
                    break;
                case "height":
                    $courseconfig['height']= $res->value;
                    break;
                case "width":
                    $courseconfig['width'] = $res->value;
                    break;
                }
            }
        }
    }
    
    return $courseconfig;
}

?>
