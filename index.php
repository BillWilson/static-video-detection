<?php
require 'vendor/autoload.php';
/*
 * https://github.com/jenssegers/imagehash
 * https://github.com/guzzle/guzzle
 * https://stackoverflow.com/questions/42228473/imagecreatefromstring-data-is-not-in-a-recognized-format-in
 * https://stackoverflow.com/questions/13808268/check-to-see-if-youtube-video-is-static-image
 */

use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;

$imageFolderName = 'images';

$error = 0;

$youtubeUrls = [
    'https://www.youtube.com/watch?v=t3217H8JppI', //static
    'https://www.youtube.com/watch?v=rOjHhS5MtvA', //video
    'https://www.youtube.com/watch?v=W-fFHeTX70Q', //change image
    'https://www.youtube.com/watch?v=Rb0UmrCXxVA', //change image
];

$youtubeGet = htmlspecialchars($_GET["youtube"]);

if (empty($youtubeGet)){

    $max = count($youtubeUrls) - 1;

    $youtubeLink = $youtubeUrls[rand(0, $max)];

}else{

    $youtubeLink = $youtubeGet;
}

$videoId = getVideoId($youtubeLink);

if ($videoId){
    for($i = 1; $i <= 3; $i++) {

        $thumbnailUrl =  "http://i.ytimg.com/vi/" . $videoId . "/" . $i . ".jpg";
        $fileName = $i . ".jpg";
        downloadFile($thumbnailUrl, $fileName);
    }

    $distance = check();

}else{

    $error = 1;

}

function downloadFile($url, $fileName)
{
    global $imageFolderName;

    $filePath = '/' . $imageFolderName . '/' . $fileName;

    $client = new Client();

    $resource = fopen(__DIR__ . $filePath, 'w');

    $streamObj = Psr7\stream_for($resource);

    $res = $client->request('GET', $url, ['sink' => $streamObj]);
    //header('Content-Type: image/jpeg');
    //echo $res->getBody();

    return $res->getBody();
}

function check()
{
    global $imageFolderName;

    $implementation = new DifferenceHash;

    $hasher = new ImageHash;

    $imagePath = __DIR__ . '/' . $imageFolderName . '/';

    $hash = $hasher->hash( __DIR__ . '/images/1.jpg');

    $distance['12'] = $hasher->compare($imagePath . '1.jpg', $imagePath . '2.jpg');

    $distance['13'] = $hasher->compare($imagePath . '1.jpg', $imagePath . '3.jpg');

    $distance['23'] = $hasher->compare($imagePath . '2.jpg', $imagePath . '3.jpg');

    return $distance;
}

function getVideoId($url)
{

    parse_str( parse_url( $url, PHP_URL_QUERY ), $queryParams );

    return $queryParams['v'];
}

function standardDeviation($array)
{
    $mean = mean($array);

    $differenceSum = 0;

    foreach($array as $val) {

        $differenceSum += pow($val - $mean, 2);

    }

    return sqrt($differenceSum / count($array));
}

function mean($array)
{
    return array_sum($array) / count($array);
}

?>

<form action="/" method="get">
    Youtube Video: <input type="text" name="youtube">
    <input type="submit" value="Submit">
</form>

<?php
if ($error){

    echo '<h1>Incorrect youtube link</h1>';

}else{

    for ($i = 1; $i <= 3; $i++){
        echo '<img src="images/'. $i . '.jpg " style="padding-right: 10px;">';
    }

    echo '<br>';
    echo '1->2:　'. $distance['12'] . '<br>';
    echo '1->3:　'. $distance['13'] . '<br>';
    echo '2->3:　'. $distance['23'] . '<br>';
    printf('Standard Deviation: %.2F<br>', standardDeviation($distance));
    printf('Mean:  %.2F', mean($distance));
}

?>