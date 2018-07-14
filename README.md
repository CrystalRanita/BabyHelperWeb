# BabyHelperWeb
  With BabyHelper app.
  Upload Video and Video info file.
  Store URL in database and upload file in dashboard/php/uploads/

  Clone BabyHelperWeb to your work folder.
  Modify /opt/lampp/etc/httpd.conf to set following info:

  DocumentRoot "/home/your_user_name/workdir/web/BabyHelper"
  <Directory "/home/your_user_name/workdir/web/BabyHelper">

  Above path is where I store BabyHelperWeb on ubuntu.

  Run XAMPP:
  sudo /opt/lampp/lampp start

# Database
  Import db/BabyHelperDB.sql by phpMyAdmin

# Env
  XAMPP on Ubuntu

# Verify & Debug
  You can use postman to check upload.
  https://blog.bluematador.com/posts/postman-how-to-install-on-ubuntu-1604/

# APP SDK
  https://github.com/gotev/android-upload-service

# Reference
  Android Upload Image using Android Upload Service, author: Belal Khan.
  https://www.simplifiedcoding.net/android-upload-image-to-server/
  This is perfect material to learn how to upload file.
