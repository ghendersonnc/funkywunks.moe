<?php

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

function createThumbnail($destPath, $w=200) {
    // using destPath in here as well because I want to overwrite
    $srcImage = imagecreatefrompng($destPath);
    $originalW = imagesx($srcImage);
    $originalH = imagesy($srcImage);
    $h = floor($originalH * ($w / $originalW));
    $thumbnail = imagecreate($w, $h);
    imagecopyresampled($thumbnail, $srcImage, 0, 0, 0, 0, $w, $h, $originalW, $originalH);
    imagepng($thumbnail, $destPath);
    imagedestroy($srcImage);
    imagedestroy($thumbnail);
}

$videoTypes = [
    'video/webm',
    'video/mp4',
    'video/quicktime'
];

$file_md5 = md5_file($_FILES['image-file']['tmp_name']);
$target_dir = 'images/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/';
$target_file = $target_dir . $file_md5 . '.' . pathinfo($_FILES['image-file']['name'], PATHINFO_EXTENSION);
$pathExt = pathinfo($_FILES['image-file']['name'], PATHINFO_EXTENSION);
$mimeType = mime_content_type($_FILES['image-file']['tmp_name']);

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

if (move_uploaded_file($_FILES['image-file']['tmp_name'], $target_file)) {
    echo '<p>File uploaded :)</p>';

    // Save image info to DB
    $now = gmdate('Y-m-d H:i:s');
    $sm = $conn->prepare("INSERT INTO `images` (`md5`, `file_location`, `upload_date`) VALUES (?, ?, ?)");
    $sm->bind_param("sss", $file_md5, $target_file, $now);
    $sm->execute();

    $conn->close();

    if (in_array($mimeType, $videoTypes) == true) {
        $thumbnail_dir = './thumbnails/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/';
        $thumbnailTarget = $thumbnail_dir . 'thumbnail_' . $file_md5 . '.png';
        $video = $ffmpeg->open("images/$file_md5[0]$file_md5[1]/$file_md5[2]$file_md5[3]/$file_md5.$pathExt");

        if (!mkdir($thumbnail_dir, 0755, true)) {
            echo '<p>THUMBNAIL FOLDER ALREADY EXISTS</p>';
        } else {
            echo '<p>THUMBNAIL FOLDER CREATED</p>';
        }
        $video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(0))->save($thumbnailTarget);
        createThumbnail($thumbnailTarget);
    }

    echo '<a target="_blank" href="' . $target_file .'">Go To Image</a><br>';
    echo '<a href="index.php">Go back to upload</a><br>';
    echo '<a href="gallery.php">Go to gallery</a>';

} else {
    $conn->close();
    echo '<p>File not uploaded :( (redirecting back to index.php in 2 seconds)</p>';
    header('Refresh:2; url=index.php', true, 303);

}

