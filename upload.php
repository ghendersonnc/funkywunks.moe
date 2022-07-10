<?php

require('../config.php');

// mysql conect
$conn = new mysqli($config['server'], $config['user'], $config['password'], $config['db']);

if ($_FILES['image-file']['error'] == 1) {
    header('Refresh:2; url=index.php', true, 303);
    die("<p>IMAGE EXCEEDS 100MB LIMIT<p>");
} else if ($_FILES['image-file']['error'] > 1) {
    header('Refresh:2; url=index.php', true, 303);
    die("<p>ERROR UPLOADING IMAGE<p>");
}

$file_md5 = md5_file($_FILES['image-file']['tmp_name']);
$target_dir = 'images/' . $file_md5[0] . $file_md5[1] . '/' . $file_md5[2] . $file_md5[3] . '/';
$target_file = $target_dir . $file_md5 . '.' . pathinfo($_FILES['image-file']['name'], PATHINFO_EXTENSION);

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
    $misc = 'misc';
    if (isset($_POST['image-tag'])) {
        if ($_POST['image-tag']) {
            $tag = $_POST['image-tag'];
            $sm = $conn->prepare("INSERT INTO `images` (`md5`, `file_location`, `upload_date`, `tag`) VALUES (?, ?, ?, ?)");
            $sm->bind_param("ssss", $file_md5, $target_file, $now, $tag);
            $sm->execute();

        } else {

            $sm = $conn->prepare("INSERT INTO `images` (`md5`, `file_location`, `upload_date`, `tag`) VALUES (?, ?, ?, ?)");
            $sm->bind_param("ssss", $file_md5, $target_file, $now, $misc);
            $sm->execute();
        }
    } else {
        $sm = $conn->prepare("INSERT INTO `images` (`md5`, `file_location`, `upload_date`, `tag`) VALUES (?, ?, ?, ?)");
        $sm->bind_param("ssss", $file_md5, $target_file, $now, $misc);
        $sm->execute();
    }

    $conn->close();

    echo '<a target="_blank" href="' . $target_file .'">Go To Image</a><br>';
    echo '<a href="index.php">Go back to upload</a>';

} else {
    $conn->close();
    echo '<p>File not uploaded :( (redirecting back to index.php in 2 seconds)</p>';
    header('Refresh:2; url=index.php', true, 303);

}
