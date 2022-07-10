<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        input {
            margin-top: 12px;
        }

    </style>
</head>
<body>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input style="margin: 0" type="file" name="image-file" id="image-file"><br>
        <label for="image-tag">Image Tag</label>
        <input type="text" name="image-tag" id="image-tag" placeholder="misc"><br>
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>