<?php
require('../connect.php');

$query = $conn->query('SELECT COUNT(*) FROM `images`');

if ($query->num_rows > 0) {
    $totalImages = intval($query->fetch_assoc()['COUNT(*)']);
} else {
    // some error i guess
}

$imageUrls = [];
$imageMD5s = [];

if ($totalImages > 0) {
    if (isset($_GET['offset'])) {
        $offset = preg_replace('/[^0-9]/', '', $_GET['offset']);
        $query = $conn->query("SELECT `file_location`, `md5` FROM `images` ORDER BY `upload_date` DESC LIMIT $offset, 10");

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $imageUrls[] = $row['file_location'];
                $imageMD5s[] = $row['md5'];
            }
        }

    } else {
        $query = $conn->query('SELECT `file_location`, `md5` FROM `images` ORDER BY `upload_date` DESC LIMIT 0, 10');

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $imageUrls[] = $row['file_location'];
                $imageMD5s[] = $row['md5'];
            }
        }
    }

}

?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>funkywunks!</title>
    <style>
        img {
            border: black 1px solid;
            max-width: 200px;
            margin: 4px;
            padding: 4px;
        }

        .video {
            border: blue 2px solid;
            max-width: 200px;
            margin: 4px;
            padding: 4px;
        }
    </style>
</head>
<body>
    <a href="index.php">To upload (for funkywunks only!)</a>
    <h1 style="color: red">No pagination yet :D</h1>
    <h1>Total images: <?php echo $totalImages ?? "UNKNOWN"; ?></h1>

    <?php
        $words = ['mp4', 'mov', 'webm'];
        $iter = 0;
        foreach ($imageUrls as $imageUrl) {
            $isVideo = false;

            foreach ($words as $word) {
                if (strpos($imageUrl, $word) !== false) {
                    $isVideo = true;
                }
            }

            if ($isVideo == true) {
                $thumbnailPath = 'thumbnails/' . $imageMD5s[$iter][0] . $imageMD5s[$iter][1] . '/' . $imageMD5s[$iter][2] . $imageMD5s[$iter][3] . '/thumbnail_' . $imageMD5s[$iter] . '.png';
                echo "<a target='_blank' href='$imageUrl'>";
                echo "<img class='video' src='$thumbnailPath' alt='fuck'>";
                echo "</a>";
            } else {
                echo "<a target='_blank' href='$imageUrl'>";
                echo "<img src='$imageUrl' alt='fuck'>";
                echo "</a>";
            }
            $iter++;
        }

    ?>
</body>
</html>