<?php
require('../connect.php');

/**
 * @var array $config
 * @var $conn
 */

$query = $conn->query('SELECT COUNT(*) FROM `images`');
$imagesPerPage = 16;
$offset = 0;
$totalImages = 0;
$imageUrls = [];
$imageMD5s = [];
$imageThumbnails = [];
$imageIds = [];

if ($query->num_rows > 0) {
    $totalImages = intval($query->fetch_assoc()['COUNT(*)']);

    if ($totalImages > 0) {
        if (isset($_GET['offset'])) {
            $offset = preg_replace('/\D/', '', $_GET['offset']);
        }

        $query = $conn->query("SELECT * FROM `images` ORDER BY `upload_date` DESC LIMIT $offset, $imagesPerPage");

        if ($query->num_rows > 0) {
            while ($row = $query->fetch_assoc()) {
                $imageUrls[] = $row['file_location'];
                $imageMD5s[] = $row['md5'];
                $imageThumbnails[] = $row['thumbnail_location'];
                $imageIds[] = $row['id'];
            }
        }

    }

} else {
    die('f you no images');
}

?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>funkywunks!</title>
    <link rel="stylesheet" href="./css/default.css">
</head>
<body>
    <div class="gallery-div">

        <a href="./index.php">To upload (for funkywunks only!)</a>
        <h1><?php echo 'You are on page ' . (($offset / $imagesPerPage) + 1)  ?></h1>
        <h1>Total images: <?php echo $totalImages ?? "UNKNOWN"; ?></h1>
        <?php
            if ($offset > 0) {
                echo '<a class="pagi-buttons" href="./gallery.php?offset=' . ($offset - $imagesPerPage) . '">PREVIOUS</a>';
            }
            if ($offset + $imagesPerPage < $totalImages) {
                echo '<a class="pagi-buttons" href="./gallery.php?offset=' . ($offset + $imagesPerPage) . '">NEXT</a>';
            }
            if ($offset % $imagesPerPage != 0) {
                $offset -= $offset % $imagesPerPage;
                echo '<a class="pagi-buttons" href="./gallery.php?offset=' . $offset . '">FIX OFFSET</a>';
            }
        ?>
        <br>
        <div class="thumbnails" style="display: flex; flex-wrap: wrap;">
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
                        $thumbnailPath = $imageThumbnails[$iter];
                        if ($isVideo || strpos($thumbnailPath, 'gif')) {
                            echo "<div id='image_$imageIds[$iter]' class='image-containers video'>";
                            echo "<a target='_blank' href='$imageUrl'>";
                            echo "<img width='100%' height='100%' src='$thumbnailPath' alt='No thumbnail exists'>";
                        } else {
                            echo "<div id='image_$imageIds[$iter]' class='image-containers'>";
                            echo "<a target='_blank' href='$imageUrl'>";
                            echo "<img width='100%' height='100%' src='$thumbnailPath' alt='No thumbnail?'>";
                        }
                        echo "</a>";
                        echo "</div>";
                        $iter++;
                    }

                    ?>
        </div>
    </div>
</body>
</html>