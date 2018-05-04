<?php
declare(strict_types=1);
class File
{

    public function download(string $file) :string
    {
		/*Function takes a string filename.
		 *Downloads file, if it exists. 
		 *If file does not exists, returns an error message.
		 */
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $file_name = hash('sha1',$file).'.'.$ext;
        $file_path = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $message = "";
        $fullname = $file_path[0]['directory']."\\".$file_name;
        if(!empty($file_name) && file_exists($fullname)){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.$file.'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fullname));
            readfile($fullname);
            $message = "File downloaded";
            exit;
        } else {
            $message =  'The file does not exist.';
        }
        return $message;
    }

    public function upload(array $file, int $folder_size) :string
    {
		/*Function takes an array of filename and temporal filename, and folder size.
		 *Checks if file is exists, valid and right size.
		 *If file is valid and right size, uploads it.
		 *If file does not exists, is not valid, or size is too large, returns an error message.
		 */
        $file = $_FILES['upload_file'];
        $allowed_extensions = DB::query("SELECT `link_table`.`lt_size` AS 'size', `file_extension`.`fe_extension` AS 'extension' FROM `user_table` JOIN `link_table` ON `user_table`.`ut_id` = `link_table`.`ut_link` JOIN `file_extension` ON `file_extension`.`fe_id` = `link_table`.`fe_link` WHERE `user_table`.`ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $file_dest = $file['tmp_name'];
        $file_name = $file['name'];
        $file_path_and_limit = DB::query("SELECT `ut_home_dir` AS 'directory', `ut_dir_limit` AS 'limit' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_size = filesize($file_dest);
        $message = "";
        if(!empty($file_name) && file_exists($file_path_and_limit[0]['directory'])){
            foreach ($allowed_extensions as $extension) {
                if ($ext === $extension['extension']) {
                    if ($file_size <= $extension['size']) {
                        if ($file_size <= ($file_path_and_limit[0]['limit'] - $folder_size)) {
                            move_uploaded_file($file_dest, $file_path_and_limit[0]['directory']."\\".hash("sha1", $file_name).".".$ext);
                            DB::query("INSERT INTO `file_table`(`ft_id`, `ft_ut_id`, `ft_filename`, `ft_size`) VALUES (NULL,:u_id,:filename,:size)", array(':u_id'=>$_SESSION['user_id'], ':filename'=>$file_name, 'size'=>$file_size));
                            $message = "File uploaded";
                            break;
                        } else {
                            $message = "Not enough space on disk";
                            break;
                        }
                    } else {
                        $message = "File is too large";
                        break;
                    }
                } else {
                    $message = "Forbidden extension";
                }
            }
        } else {
            echo 'The file does not exist.';
        }
        return $message;
    }

    public function delete(string $file) :string
    {
		/*Function takes a string filename.
		 *Checks if file is exists.
		 *If file exists, deletes it.
		 *If file does not exists, returns an error message.
		 */
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $file_name = hash('sha1',$file).'.'.$ext;
        $file_path = DB::query("SELECT `ut_home_dir` AS 'directory' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $fullname = $file_path[0]['directory']."\\".$file_name;
        $message = "";
        if (file_exists($fullname) && $file != "" && !is_dir($fullname)) {
            unlink($fullname);
            DB::query("DELETE FROM `file_table` WHERE `ft_ut_id` = :u_id AND `ft_filename` = :filename", array(':u_id'=>$_SESSION['user_id'], ':filename'=>$file));
            $message = "Deleted";
        } else {
            $message = "Invalid filename";
        }

        return $message;
    }

    public function download_from_internet(string $url, string $value, int $folder_size) :string
    {
		/*Function takes a string url of file and string location, and folder size.
		 *Checks if url is valid, file is valid and right size.
		 *If url is valid and file is valid and right size, downloads file.
		 *If url is not valid, or file is not valid or too large, returns an error message.
		 */
        $headers = @get_headers($url, 1);
        $file_size = $headers['Content-Length'];
		$type = $headers['Content-Type'];
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $file_path_and_limit = DB::query("SELECT `ut_home_dir` AS 'directory', `ut_dir_limit` AS 'limit' FROM `user_table` WHERE `ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $allowed_extensions = DB::query("SELECT `link_table`.`lt_size` AS 'size', `file_extension`.`fe_extension` AS 'extension' FROM `user_table` JOIN `link_table` ON `user_table`.`ut_id` = `link_table`.`ut_link` JOIN `file_extension` ON `file_extension`.`fe_id` = `link_table`.`fe_link` WHERE `user_table`.`ut_id` = :u_id", array(':u_id'=>$_SESSION['user_id']));
        $message = "";
        if ($headers) {
            foreach ($allowed_extensions as $extension) {
                if ($ext === $extension['extension']) {
                    if ($file_size <= $extension['size']) {
                        if ($file_size <= ($file_path_and_limit[0]['limit'] - $folder_size)) {
                            if ($value === "home") {
                                file_put_contents($file_path_and_limit[0]['directory']."\\".hash("sha1", basename($url)).".".$ext,file_get_contents($url));
                                DB::query("INSERT INTO `file_table`(`ft_id`, `ft_ut_id`, `ft_filename`, `ft_size`) VALUES (NULL,:u_id,:filename,:size)", array(':u_id'=>$_SESSION['user_id'], ':filename'=>basename($url), 'size'=>$file_size));
                            } else {
                                set_time_limit(0);
                                $process = curl_init();

                                curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE); // FALSE here! Otherwise SSL-error occurs!
                                curl_setopt($process, CURLOPT_FOLLOWLOCATION, TRUE);  // Redirects happen sometimes
                                curl_setopt($process, CURLOPT_TIMEOUT, 0);            // We need hours for big files
                                curl_setopt($process, CURLOPT_CONNECTTIMEOUT, 0);     // Let it wait rather than stop
                                curl_setopt($process, CURLOPT_NOPROGRESS, TRUE);      // We don't need any progress indication
                                curl_setopt($process, CURLOPT_VERBOSE, FALSE);        // Set TRUE for debug
                                curl_setopt($process, CURLOPT_RETURNTRANSFER, FALSE); // We need no returned data in our script memory
                                curl_setopt($process, CURLOPT_FAILONERROR, TRUE);     // Let's stop if something goes wrong
                                curl_setopt($process, CURLOPT_URL, $url);             // Initiate CURL with our URL

                                if (($last_slash = strrpos($url, '/')) !== false) {
                                    $file_name = basename($url);
                                } else {
                                    $file_name = 'data';
                                }

                                header('Content-disposition: attachment; filename='.$file_name);
                                header("Content-type: $type");

                                curl_exec($process);
                               
                            }
                            $message = "File uploaded";
                            break;
                        } else {
                            $message = "Not enough space on disk";
                            break;
                        }
                    } else {
                        $message = "File is too large";
                        break;
                    }
                } else {
                    $message = "Forbidden extension";
                }
            }
        } else {
            $message = "Invalid URL";
        }

        return $message;
    }
}