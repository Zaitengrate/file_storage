<?php
declare(strict_types=1);

class Dir
{

    //Function getting arrays with name of the file and it size
    public function files() :array
    {
        $files = DB::query("SELECT `ft_filename` AS 'filename', `ft_size` AS 'size' FROM `file_table` WHERE `ft_ut_id` = :u_id ORDER BY `ft_filename` ASC", array(':u_id'=>$_SESSION['user_id']));

        $list = [];
        $i = 0;

        foreach($files as $file){
            $list[$i] = array($file['filename'], round($file['size']/1024, 2));
            $i++;
        }
        return $list;
    }

    //Function getting arrays with allowed extensions and it sizes
    public function ext() :array
    {
        $extension = DB::query("SELECT `link_table`.`lt_size` AS 'size', `file_extension`.`fe_extension` AS 'extension' FROM `user_table` JOIN `link_table` ON `user_table`.`ut_id` = `link_table`.`ut_link` JOIN `file_extension` ON `file_extension`.`fe_id` = `link_table`.`fe_link` WHERE `user_table`.`ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $ex = [];
        $i = 0;
        foreach ($extension as $ext) {
            $ex[$i] = array(strtoupper($ext['extension']), round($ext['size']/1048576, 2));
            $i++;
        }
        return $ex;
    }

    function folder_size (string $dir) :int
    {
		/*Function takes a string directory.
		 *Scans directory.
		 *Returns size of all files in directory.
		 */
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->folder_size($each);
        }
        return $size;
    }
}