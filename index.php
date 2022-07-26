<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>funkywunks!</title>
    <link rel="stylesheet" href="./css/default.css">

</head>
<body>
    <?php
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            echo 'Your public IP: ' .  $_SERVER['HTTP_X_FORWARDED_FOR'] . '<br>';
        }
        elseif (isset($_SERVER['REMOTE_ADDR'])) {
            echo 'Your public IP: ' . $_SERVER['REMOTE_ADDR'] . '<br>';
        }
    ?>
    <div class="container">
        <div class="upload-form-div">
            <form class="upload-form" action="./upload.php" method="post" enctype="multipart/form-data">
                <label for="image-file" id="image-file-label">
                    select file
                    <input type="file" name="image-file" id="image-file" style="display: none"/>
                </label>
                <div id="file-name" style="text-align: center; display: none; margin: 16px 0;">
                    <span id="file-name-span">filler</span>
                </div>
                <label for="private-key">
                    <input type="text"
                           name="private-key"
                           placeholder="private key for funkywunks"
                           id="private-key"
                           onfocus="this.placeholder=''"
                           onblur="this.placeholder='private key for funkywunks'"
                           required>
                </label>
                <label for="submit">
                    <input type="submit" value="Upload" name="submit" id="submit-form" onclick="rememberToken()">
                </label>
            </form>
        </div>
        <div class="gallery-nav">
            <a href="./gallery.php">To Gallery</a>
        </div>
    </div>

    <script src="./js/fileNameStuff.js"></script>
    <script src="./js/rememberToken.js"></script>
</body>
</html>