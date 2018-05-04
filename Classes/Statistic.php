<?php
declare(strict_types=1);
class Statistic
{
    public static function get_statistic() :array
    {
        $users = DB::query("SELECT COUNT(`ut_id`) AS 'users' FROM `user_table` ", array());
        $statistic['nmb_of_users'] = $users[0]['users'];

        $files_size = DB::query("SELECT SUM(`ft_size`) AS 'files_size' FROM `file_table`", array());
        $statistic['size_of_files'] = round($files_size[0]['files_size']/1048576, 2);

        $files = DB::query("SELECT count(`ft_id`) AS 'all_files' FROM `file_table`", array());
        $statistic['nmb_of_files'] = $files[0]['all_files'];

        $avg_files_all = DB::query("SELECT (SUM(`ft_size`) / (SELECT COUNT(`ut_id`) FROM `user_table` )) AS 'files_size' FROM `file_table`", array());
        $statistic['avg_size_of_files'] = round($avg_files_all[0]['files_size']/1048576, 2);

        return $statistic;
    }
}