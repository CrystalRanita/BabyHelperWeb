<?php
    define('DB_HOST','localhost'); // hostname
    define('DB_USERNAME','root'); // database username
    define('DB_PASSWORD',''); // database password
    define('DB_NAME','BabyHelperDB'); // Database name
    define('SITE_ROOT', realpath(dirname(__FILE__))); // execure connect.php directory
    define('CONFIG_FILE','rect.cfg'); // config file with X, Y, W, H information
    define('EXE_FILE','measure'); // config file with X, Y, W, H information


    $file_upload_path = SITE_ROOT.'/uploads/'; // store path (php/uploads)
    $run_files_path = SITE_ROOT.'/run/'; // store path (php/run)
    $server_ip = gethostbyname(gethostname());
    $url = 'http://'.$server_ip.$file_upload_path; // http://localhost/dashboard/php/uploads/
    $response = array();
    $exe_report_path = $run_files_path.'report.txt';

    // measure
    $exe_files_path = $run_files_path.EXE_FILE;
    $config_files_path = $run_files_path.CONFIG_FILE;
  
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['upload_user_name']) and isset($_FILES['video']['name']) and isset($_FILES['txt']['name'])) {
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
                    echo "upload success<br>";
                } else {
                    $response['error']=true;
                    $response['message']='upload failed!';
                    echo json_encode($response);
                    return;
                }

                if (move_uploaded_file($_FILES['txt']['tmp_name'], $info_file_path)) {
                    echo "info upload success<br>";
                } else {
                    $response['error']=true;
                    $response['message']='info upload failed!';
                    echo json_encode($response);
                    return;
                }

                $con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die('Failed to connect database!');
                $sql = "INSERT INTO `video_info` (`key_id`, `file_url`, `info_url`, `user_name`, `epoch_time`) VALUES (NULL, '$file_url', '$info_url', '$upload_user_name', '$timestamp');";
                if (mysqli_query($con, $sql)) {
                    $response['error'] = false;
                    $response['file_url'] = $file_url;
                    $response['info_url'] = $info_url;
                    $response['upload_user_name'] = $upload_user_name;
                    echo "write db success<br>";
                    mysqli_close($con);

                    if (file_exists($config_files_path)){
                        if (!unlink($config_files_path)) {
                            $response['error']=true;
                            $response['message']='Remove previous config file failed.';
                            echo json_encode($response);
                            return;
                        }
                    }
                    // move config file
                    if (copy($info_file_path, $config_files_path)) {
                        echo "copy success<br>";

                        if (!file_exists($config_files_path)) {
                            $response['error']=true;
                            $response['message']='config file not found.';
                            echo json_encode($response);
                            return;
                        }

                        if (!file_exists($exe_files_path)) {
                            $response['error']=true;
                            $response['message']='exe file not found.';
                            echo json_encode($response);
                            return;
                        }
                        // Run measure, run in auto mode 1 to read config file.
                        echo "run ".$exe_files_path." ".$file_path." ".$config_files_path." 1 ".$exe_report_path."<br>";
                        // exec("$exe_files_path", $output, $return);

                        exec("$exe_files_path $file_path $config_files_path 1 $exe_report_path 2>&1", $output, $return);
                        //exec("/home/crystal/sdk/android_sdk/platform-tools/adb devices", $output, $return);
                        foreach($output as $val) {
                            echo "output: ".$val."<br>";
                        }
                        echo "result: ".$return."<br>";
                        if ($return !== 0) {
                            $response['error']=true;
                            $response['message']='run failed!';
                            echo json_encode($response);
                            return;
                        } else {
                            echo "run file success<br>";
                        }
                    } else {
                        $response['error']=true;
                        $response['message']='cp config failed!';
                        echo json_encode($response);
                        return;
                    }
                } else {
                    $response['error']=true;
                    $response['message']='write db failed!';
                    mysqli_close($con);
                    echo json_encode($response);
                    return;
                }
            } catch(Exception $e) {
                $response['error']=true;
                $response['message']=$e->getMessage();
                mysqli_close($con);
            } 
        } else {
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
