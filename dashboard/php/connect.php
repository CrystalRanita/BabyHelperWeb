<?php
    define('DB_HOST','localhost'); // hostname
    define('DB_USERNAME','root'); // database username
    define('DB_PASSWORD',''); // database password
    define('DB_NAME','BabyHelperDB'); // Database name
    define('SITE_ROOT', realpath(dirname(__FILE__))); // execure connect.php directory

    $file_upload_path = SITE_ROOT.'/uploads/'; // store path (php/uploads)
    $server_ip = gethostbyname(gethostname());
    $url = 'http://'.$server_ip.$file_upload_path; // http://localhost/dashboard/php/uploads/
    $response = array();
  
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['upload_user_name']) and isset($_FILES['video']['name'])) {
            $con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die('Failed to connect database!');
    
            // upload user
            $upload_user_name = $_POST['upload_user_name'];
            echo "Upload User: $upload_user_name<br>";
    
            // video informations
            $fileinfo = pathinfo($_FILES['video']['name']);
            echo "extension: ".$fileinfo['extension']."<br>";
            echo "basename: ".$fileinfo['basename']."<br>";
            $extension = $fileinfo['extension']; // ex. mp4
    
            // file stored url
            $file_url = $url.getMaxIdFromDB().'.'.$extension;
            echo "file_url: $file_url<br>";

            // video informations
            $info_fileinfo = pathinfo($_FILES['txt']['name']);
            echo "txt extension: ".$info_fileinfo['extension']."<br>";
            echo "txt basename: ".$info_fileinfo['basename']."<br>";
            $info_ext = $info_fileinfo['extension']; // ex. txt

            // txt file for video info
            $info_url = $url.getMaxIdFromDB().'.'.$info_ext;

            // file stored path
            $file_path = $file_upload_path.getMaxIdFromDB().'.'.$extension;
            echo "file_path: $file_path<br>";

            // info file stored path
            $info_file_path = $file_upload_path.getMaxIdFromDB().'.'.$info_ext;
            echo "info_file_path: $info_file_path<br>";

            // Create timestamp for DB
            $timestamp=time();
            echo "Timestamp: ".$timestamp."<br>";
            echo date("Y-m-d H:i:s",$timestamp)."<br>";

            try{
                if (move_uploaded_file($_FILES['video']['tmp_name'], $file_path)) {
                    echo "upload success";
                } else {
                    $response['error']=true;
                    $response['message']='upload failed!';
                    mysqli_close($con);
                    echo json_encode($response);
                    return;
                }

                if (move_uploaded_file($_FILES['txt']['tmp_name'], $info_file_path)) {
                    echo "info upload success";
                } else {
                    $response['error']=true;
                    $response['message']='info upload failed!';
                    mysqli_close($con);
                    echo json_encode($response);
                    return;
                }

                $sql = "INSERT INTO `video_info` (`key_id`, `file_url`, `info_url`, `user_name`, `epoch_time`) VALUES (NULL, '$file_url', '$info_url', '$upload_user_name', '$timestamp');";
                if (mysqli_query($con, $sql)) {
                    $response['error'] = false;
                    $response['file_url'] = $file_url;
                    $response['info_url'] = $info_url;
                    $response['upload_user_name'] = $upload_user_name;
                    echo "write db success";
                } else {
                    $response['error']=true;
                    $response['message']='write db failed!';
                    mysqli_close($con);
                    echo json_encode($response);
                    return;
                }
            }catch(Exception $e){
                $response['error']=true;
                $response['message']=$e->getMessage();
            } 
            mysqli_close($con);
        }else{
            $response['error']=true;
            $response['message']='No file selected!';
        }
        echo json_encode($response);
    }
 
    function getMaxIdFromDB() {
        $con = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME) or die('Failed to connect database!');
        $sql = "SELECT max(key_id) as key_id FROM video_info";
        $result = mysqli_fetch_array(mysqli_query($con, $sql));
    
        mysqli_close($con);
        if ($result['key_id'] == null) {
            return 1;
        } else {
            return ++$result['key_id'];
        }
    }
?>