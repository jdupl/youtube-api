<?php

/**
 * Classe permettant d'obtenir une liste de vidéos à partir d'un compte youtube.
 * 
 * @version 0.1.1 Première version de l'api maison pour idées de fous ne permettant que d'obtenir les vidéos d'un compte youtube.
 * @author Justin Duplessis <drfoliberg@gmail.com> pour www.ideesdefous.com
 * @todo Version 0.2 Ajouter le type de source 'playlist' afin de pouvoir avoir les vidéos d'un liste de lecture
 * 
 * Copyright (c) 2013 Justin Duplessis pour www.ideesdefous.com sous la license MIT
 */
class YoutubeApi {

    public static $typesSupportes = ['channel'];
    private $infoTypes = ['channel' => ['requete' => 'users']];
    private $erreur;
    private $urlBase = "http://gdata.youtube.com/feeds/api/";
    private $urlCourante;

    /**
     * Constructeur de base qui construit la requête s'il n'y a pas d'erreurs.
     * @param String $id Id du support
     * @param String $type Type de support à aller chercher 
     */
    public function __construct($id, $type) {
        $this->erreur = "";
        $this->urlCourante = "";

        if (!in_array($type, YoutubeApi::$typesSupportes)) {
            $this->erreur = "Mauvais type de support entré. Entré: $type Attendu: " . implode(" ou ", YoutubeApi::$typesSupportes);
        } else {
            $this->urlCourante = $this->contruireRequete($id, $type);
        }
    }

    /**
     * Fonction qui permet de contruire la requête youtube selon le type de source et son id
     * @param string $id Id de la source
     * @param string $type Type de source
     * @return string L'url de la requête à l'api de youtube
     */
    private function contruireRequete($id, $type) {
        $url = $this->urlBase;
        if ($type == 'channel') {
            $url = $url . $this->infoTypes['channel']['requete'] . "/" . $id . "/uploads?orderby=updated&alt=json";
        }
        return $url;
    }

    /**
     * Le retour est composé d'un boolean 'erreur' et un string 'erreurs' ou 'videos' dépendemment du cas.
     * Le tableau 'video' est composé de 'titre', 'description' et 'id'.
     * Si aucun vidéo n'a pu être traité, le champ erreur est déclanché.
     * @return array
     */
    public function getVideos() {
        $idVideos = array();
        if ($this->erreur != "") {
            return ['erreur' => true, 'erreurs' => $this->erreur];
        } else {
            $videos = json_decode(file_get_contents($this->urlCourante), true);
            if (!isset($videos['feed']['entry'])) {
                return ['erreur' => true, 'erreurs' => "Aucun vidéo n'a pu être obtenu de la source."];
            }
            $videos = $videos['feed']['entry'];
            foreach ($videos as $video) {
                $id = parse_url($video['link'][0]['href'])['query'];
                $id = explode('&', $id);
                foreach ($id as $tmp) {
                    $tmp = explode('=', $tmp);
                    if ($tmp[0] == 'v') {
                        $id = $tmp[1];
                    }
                }
                $description = $video['content']['$t'];
                $titre = $video['title']['$t'];
                $indexTableau = ['titre' => $titre, 'description' => $description, 'id' => $id];
                $idVideos[] = $indexTableau;
            }
        }
        return ['erreur' => false, 'videos' => $idVideos];
    }

    /**
     * Méthode qui permet d'avoir un object lecteur youtube flash standard rapidement.
     * 
     * @param type $id Id de la source
     * @param type $taille Tableau contenant 'hauteur' et 'largeur' en pixels. Si le tableau est vide, une taille de 360*640 est rendue.
     * @return string String contenant le code html d'un lecteur flash simple
     */
    public static function getLecteur($id, $taille) {
        $largeur = 640;
        $hauteur = 360;
        if (isset($taille['largeur']) && $taille['hauteur']) {
            $largeur = $taille['largeur'];
            $hauteur = $taille['hauteur'];
        }
        $lecteur = '<object width="' . $largeur . '" height="' . $hauteur . '">
                    <param name="movie" value="https://www.youtube.com/v/' . $id . '"></param>
                    <param name="allowFullScreen" value="true"></param>
                    <param name="allowScriptAccess" value="always"></param>
                    <embed src="https://www.youtube.com/v/' . $id . '" type="application/x-shockwave-flash" allowfullscreen="true" allowScriptAccess="always" width="' . $largeur . '" height="' . $hauteur . '"></embed>
                    </object>';
        return $lecteur;
    }

}
?>
