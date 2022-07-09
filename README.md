# funkywunks.moe

Just for me to share images, videos, etc. But, here's the source code for the upload.php.

In `nginx.conf` I set `client_max_body_size` to 100M, same for `upload_max_filesize` in php.ini (If using php-fpm, it'll be the php.ini for that). This is to allow for maximum 100MB file size upload.

I used a PHP file for a config for database connection info because I did not want to install .env loaders.

I did not use something like nextcloud because this is a fun project for myself.

I did not use Laravel or other framework for same reason as above.

## Why md5 file naming
If they do not already exist, the first 4 characters of the image's md5 will be used to generate a directory structure like `images/ab/cd/md5filename.file`

Couple of reasons:
* if you know the md5, you know the direct image URL
* there are 16^4 (over 65000) unique locations for images to rest in.
    * this is to prevent too many images being in 1 directory (unlikely anyway since only I upload to the server)

## Why just upload.php
* It is the only meaningful file to add. The only public accessible locations are images, videos, etc that are uploaded (and a gallery in the near future). 
* While both upload.php and index.php are only accessible by me, index.php only has a form for me to upload the images manually.
* I do plan on adding ShareX integration and a gallery.php, those will be added here.


## Why http basic auth
There's absolutely no user data for me add to a database, so it is unnecessary to create a login system. It is not any more/less secure.