<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>funkywunks!</title>

    <style>
        input {
            margin-top: 12px;
        }

    </style>
</head>
<body>
    <a href="gallery.php">To Gallery</a>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="image-file" id="image-file"><br>
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>