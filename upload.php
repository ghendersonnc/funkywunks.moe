<?php

/**
 * @var array $config
 */

require('../connect.php');
require('../config.php');
require('../vendor/autoload.php');

if (isset($_POST['private-key'])) {
    if ($_POST['private-key'] != $config['fw_private_key']) {
        die("not for u");
    }
} else {
    die("nice try");
}

$ffmpeg = \FFMpeg\FFMpeg::create([
    'ffmpeg.binaries'  => $config['ffmpeg_bin'],
    'ffprobe.binaries' => $config['ffprobe_bin'],
]);

if ($_FILES['image-file']['error'] == 1) {
    header('Refresh:2; url=index.php', true, 303);
    die("<p>IMAGE EXCEEDS 100MB LIMIT<p>");
} else if ($_FILES['image-file']['error'] > 1) {
    header('Refresh:2; url=index.php', true, 303);
    die("<p>ERROR UPLOADING IMAGE<p>");
}

function createThumbnailFromVideo($destPath) {
    // using destPath in here as well because I want to overwrite
    $srcImage = imagecreatefrompng($destPath);
    $originalW = imagesx($srcImage);
    $originalH = imagesy($srcImage);
    $thumbnailHeight = 0;
    $thumbnailWidth = 0;

    if ($originalH > $originalW) {
        $thumbnailHeight = 200;
        $thumbnailWidth = floor($originalW * ($thumbnailHeight / $originalH));
    } else {
        $thumbnailWidth = 200;
        $thumbnailHeight = floor($originalH * ($thumbnailWidth / $originalW));
    }

    $thumbnail = imagecreate($thumbnailWidth, $thumbnailHeight);
    imagecopyresampled($thumbnail, $srcImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $originalW, $originalH);
    imagepng($thumbnail, $destPath);
    imagedestroy($srcImage);
    imagedestroy($thumbnail);
}

function createThumbnailFromImage($src, $destPath, $mimeType) {

    switch ($mimeType) {
        case 'image/gif':
            $srcImage = imagecreatefromgif($src);
            break;
        case 'image/jpeg':
            $srcImage = imagecreatefromjpeg($src);
            break;
        case 'image/png':
            $srcImage = imagecreatefrompng($src);
            break;
    }

    $originalWidth = imagesx($srcImage);
    $originalHeight = imagesy($srcImage);

    if ($originalHeight > $originalWidth) {
        $thumbnailHeight = 200;
        $thumbnailWidth = floor($originalWidth * ($thumbnailHeight / $originalHeight));
    } else {
        $thumbnailWidth = 200;
        $thumbnailHeight = floor($originalHeight * ($thumbnailWidth / $originalWidth));
    }

    $destImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);
    imagecopyresampled($destImage, $srcImage, 0,0,0,0, $thumbnailWidth, $thumbnailHeight, $originalWidth, $originalHeight);
    imagejpeg($destImage, $destPath);
    imagedestroy($srcImage);
    imagedestroy($destImage);
}

$videoTypes = [
    'video/webm',
    'video/mp4',
    'video/quicktime'
];

$imageTypes = [
    'image/gif',
    'image/jpeg',
    'image/png'
];

$file_md5 = md5_file($_FILES['image-file']['tmp_name']);
$target_dir = 'images/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/';
$target_file = $target_dir . $file_md5 . '.' . pathinfo($_FILES['image-file']['name'], PATHINFO_EXTENSION);
$pathExt = pathinfo($_FILES['image-file']['name'], PATHINFO_EXTENSION);
$mimeType = mime_content_type($_FILES['image-file']['tmp_name']);

if (in_array($mimeType, $videoTypes)) {
    $thumbnail_target = 'thumbnails/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/thumbnail_' . $file_md5 . '.png';

} else {
    $thumbnail_target = 'thumbnails/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/thumbnail_' . $file_md5 . '.' . pathinfo($_FILES['image-file']['name'], PATHINFO_EXTENSION);

}


// Check if md5 exists in DB, if so, display link to that image.
// It is highly unlikely 2 distinctly different images will contain the same md5
$res = $conn->query("SELECT `md5` FROM `images` WHERE `md5`='" . $file_md5 . "'");

if ($res->num_rows > 0) {

    echo '<a target="_blank" href="' . $target_file .'">Go To Image</a>';

    die("<p>File md5 already exists.<p>");
}

// make dir if not exist
// 755 permissions should be good for public access
if (!mkdir($target_dir, 0755, true)) {
    echo '<p>FOLDER ALREADY EXISTS</p>';
} else {
    echo '<p>FOLDER CREATED</p>';
}
$imageTmp = $_FILES['image-file']['tmp_name'];
if (move_uploaded_file($imageTmp, $target_file)) {
    echo '<p>File uploaded :)</p>';

    // Save image info to DB
    $now = gmdate('Y-m-d H:i:s');
    $sm = $conn->prepare("INSERT INTO `images` (`md5`, `file_location`, `thumbnail_location`, `upload_date`) VALUES (?, ?, ?, ?)");
    $sm->bind_param("ssss", $file_md5, $target_file, $thumbnail_target, $now);
    $sm->execute();

    $conn->close();
    $thumbnail_dir = './thumbnails/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/';
    $thumbnailTarget = $thumbnail_dir . 'thumbnail_' . $file_md5 . '.png';

    if (in_array($mimeType, $videoTypes)) {
        $video = $ffmpeg->open("images/$file_md5[0]$file_md5[1]/$file_md5[2]$file_md5[3]/$file_md5.$pathExt");

        if (!mkdir($thumbnail_dir, 0755, true)) {
            echo '<p>THUMBNAIL FOLDER ALREADY EXISTS</p>';
        } else {
            echo '<p>THUMBNAIL FOLDER CREATED</p>';
        }

        $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))->save($thumbnailTarget);
        createThumbnailFromVideo($thumbnailTarget);
    } elseif (in_array($mimeType, $imageTypes)) {
        if (!mkdir($thumbnail_dir, 0755, true)) {
            echo '<p>THUMBNAIL FOLDER ALREADY EXISTS</p>';
        } else {
            echo '<p>THUMBNAIL FOLDER CREATED</p>';
        }
        createThumbnailFromImage($target_file, $thumbnail_target, $mimeType);
    }

    echo '<a target="_blank" href="' . $target_file .'">Go To Image</a><br>';
    echo '<a href="./index.php">Go back to upload</a><br>';
    echo '<a href="./gallery.php">Go to gallery</a>';

} else {
    $conn->close();
    echo '<p>File not uploaded :( (redirecting back to index.php in 2 seconds)</p>';
    header('Refresh:2; url=index.php', true, 303);

}

