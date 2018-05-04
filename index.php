<?php
declare(strict_types=1);

session_start();

require "Classes/Auth.php";
require "Classes/Dir.php";
require "Classes/File.php";
require "Classes/DB.php";
require "Classes/Statistic.php";
require "Classes/Template.php";
require "config.php";
require "db_config.php";


$authentication = new Auth();
$files = new File();
$directory = new Dir();
$tpl = new Template('en', $config, $grammar, $label);


if(isset($_POST['username']) && isset($_POST['password'])) {
    $remember = "yes";
    if (!isset($_POST['remember'])) {
        $remember = "no";
    }
    $message = $authentication->login(htmlentities($_POST['username']), htmlentities($_POST['password']), $remember);
    $tpl->set("message", $message);
    //echo $authentication->login(htmlentities($_POST['username']), htmlentities($_POST['password']), $remember);
}

if ((isset($_GET['logout']))&&($_GET['logout']=='go')) {
    $authentication->logout();
}
if ((isset($_GET['statistic']))&&($_GET['statistic']=='get')) {
    echo Statistic::get_statistic();
}

if (isset($_GET['delete']) && isset($_GET['file']) && ($_GET['delete'] == 'true')) {
    //echo $files->delete(htmlentities($_GET['file']));
    $message = $files->delete(htmlentities($_GET['file']));
    $tpl->set("message", $message);
}

if (isset($_FILES['upload_file'])) {
    $dir = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $folder_size = $directory->folder_size($dir[0]['directory']);
    //echo $files->upload($_FILES['upload_file'], $folder_size);
    $message = $files->upload($_FILES['upload_file'], $folder_size);
    $tpl->set("message", $message);
}

if (isset($_GET['download']) && isset($_GET['file']) && ($_GET['download'] == 'true')) {
    //echo $files->download(htmlentities($_GET['file']));
    $message = $files->download(htmlentities($_GET['file']));
    $tpl->set("message", $message);
}

if (isset($_POST['file_to_download']) && isset($_POST['location'])) {
    $dir = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $folder_size = $directory->folder_size($dir[0]['directory']);
    $url = htmlentities($_POST['file_to_download']);
    $value = $_POST['location'];
    //echo $files->download_from_internet($url, $value, $folder_size);
    $message = $files->download_from_internet($url, $value, $folder_size);
    $tpl->set("message", $message);
}

if (isset($_SESSION['user_id'])) {
    //Getting all dynamic values
    $user_name = DB::query("SELECT `ut_name` AS 'name' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $dir = DB::query("SELECT `ut_home_dir` AS 'directory', `ut_dir_limit` AS 'limit' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $stat = Statistic::get_statistic();
    $ext = $directory->ext();
    $files = $directory->files();
    $dir_size = round($directory->folder_size($dir[0]['directory'])/1048576, 2);
    $all_size = round($dir[0]['limit']/1048576, 2);

    //Setting all dynamic values
    if (!isset($message)){$tpl->set("message", "");}
    $tpl->set("log_info", "You are logged in as ".$user_name[0]['name']);
    $tpl->set("nmb_of_users", $stat['nmb_of_users']);
    $tpl->set("size_of_files", $stat['size_of_files']);
    $tpl->set("nmb_of_files", $stat['nmb_of_files']);
    $tpl->set("avg_size_of_files", $stat['avg_size_of_files']);
    $tpl->set("page_title", "Login");
    $tpl->set("ext", $ext);
    $tpl->set("used_space", $dir_size);
    $tpl->set("all_size", $all_size);
    $tpl->set("table", $files);

    //Showing our page
    echo $tpl->main('Templates/index.tpl');

} else if (isset($_COOKIE['ID']) && isset($_SESSION['user_id'])) {
    $user_info = DB::query("SELECT `at_ut_id` AS 'user_id', `at_expires` AS 'expires' FROM `auth_token` WHERE `at_token` = :token", array(':token'=>hash('sha256', $_COOKIE['ID'])));
    if ($user_info) {
        $time = $user_info[0]['expires'];
        $user_id = $user_info[0]['user_id'];
        if ($time > date("Y-m-d H:i:s")) {
            $_SESSION['user_id'] = $user_id;
        }
    } else {
        //Getting all dynamic values
        $stat = Statistic::get_statistic();

        //Setting all dynamic values
        if (!isset($message)){$tpl->set("message", "");}
        $tpl->set("log_info", "Not Logged in");
        $tpl->set("nmb_of_users", $stat['nmb_of_users']);
        $tpl->set("size_of_files", $stat['size_of_files']);
        $tpl->set("nmb_of_files", $stat['nmb_of_files']);
        $tpl->set("avg_size_of_files", $stat['avg_size_of_files']);
        $tpl->set("page_title", "Login");

        //Showing our page
        echo $tpl->login('Templates/login.tpl');
    }
} else {
    //Getting all dynamic values
    $stat = Statistic::get_statistic();

    //Setting all dynamic values
    if (!isset($message)){$tpl->set("message", "");}
    $tpl->set("log_info", "Not Logged in");
    $tpl->set("nmb_of_users", $stat['nmb_of_users']);
    $tpl->set("size_of_files", $stat['size_of_files']);
    $tpl->set("nmb_of_files", $stat['nmb_of_files']);
    $tpl->set("avg_size_of_files", $stat['avg_size_of_files']);
    $tpl->set("page_title", "Login");

    //Showing our page
    echo $tpl->login('Templates/login.tpl');
}




