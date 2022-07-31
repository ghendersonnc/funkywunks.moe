<?php
require('../connect.php');

/**
 * @var array $config
 * @var $conn
 */

$imageUrl = '';
if (isset($_GET['post_id'])) {
    $postid = $_GET['post_id'];
    if (!is_numeric($postid)) {
        die('<h1>Post ID must be a number</h1>');
    }

    $query = $conn->query("SELECT * FROM `images` WHERE `id` = $postid");

    if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $imageUrl = $row['file_location'];
    } else {
        die('<h1>Post ID does not exist</h1>');

    }

}

if (empty($imageUrl)) {
    die('<h1>Post ID does not exist</h1>');
}

$videoTypes = [
    'webm',
    'mp4',
    'mov'
];

$isVideo = false;
foreach ($videoTypes as $type) {
    if (strpos($imageUrl, $type)) {
        $isVideo = true;
    }
}

$imgWidth = 0;
$imgHeight = 0;
if (!$isVideo) {
    list($imgWidth, $imgHeight) = getimagesize($imageUrl);
}

?>
<?php
$tmp = explode('/', $imageUrl);
$fileName = end($tmp);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>funkywunks!</title>
    <link rel="stylesheet" href="./css/default.css">
</head>
<body>
    <div class="post">
        <div class="gallery-nav">
            <a href="./gallery.php">To Gallery</a>
        </div>
        <button type="button" onclick="downloadMedia()" style="margin: 4px 0">Download</button>

        <div class="post-media-container">
            <?php
                if (!$isVideo) {
            ?>
                    <img class="post-image post-media"  src="<?php echo $imageUrl; ?>" alt="">
            <?php
                } else {
            ?>
                    <video loop class="post-video post-media" controls onloadstart="this.volume=0.2">
                        <source src="./<?php echo $imageUrl ?>">
                    </video>
            <?php
                }
            ?>

        </div>
    </div>

    <script>
        function downloadMedia() {
            var a = document.createElement('a');
            a.href = "<?php echo $imageUrl; ?>";

            a.download = "<?php echo $fileName; ?>";
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
</body>
</html>
