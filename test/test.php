<?php

include '../src/YoutubeApi.php';

$api = new YoutubeApi("YouTube", "channel");
$res = $api->getVideos();
if ($res['erreur']) {
    echo $res['erreurs'];
} else {
    foreach ($res['videos'] as $video) {
        echo YoutubeApi::getLecteur($video['id'], array());
        echo $video['titre'];
	echo $video['description'];
    }
}
?>
