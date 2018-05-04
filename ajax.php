<?php
declare(strict_types=1);

session_start();

require "Classes/Dir.php";
require "Classes/File.php";
require "Classes/DB.php";
require "Classes/Statistic.php";
require "db_config.php";

$files = new File();
$directory = new Dir();


if (isset($_GET['delete']) && isset($_GET['file']) && ($_GET['delete'] == 'true')) {
    echo $files->delete(htmlentities($_GET['file']));
}

if (isset($_FILES['upload_file'])) {
    $dir = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $folder_size = $directory->folder_size($dir[0]['directory']);
    echo $files->upload($_FILES['upload_file'], $folder_size);
}

if (isset($_POST['file_to_download']) && isset($_POST['location'])) {
    $dir = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $folder_size = $directory->folder_size($dir[0]['directory']);
    $url = htmlentities($_POST['file_to_download']);
    $value = $_POST['location'];
    echo $files->download_from_internet($url, $value, $folder_size);
}

if (isset($_GET['statistic']) && ($_GET['statistic'] == 'true')) {
    $statistic['files'] = Statistic::get_statistic();
    $dir = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
    $statistic['folder'] = round($directory->folder_size($dir[0]['directory'])/1048576, 2);
    echo json_encode($statistic);
}
