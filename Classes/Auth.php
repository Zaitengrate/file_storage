<?php
declare(strict_types=1);
class Auth
{

    public function login(string $login, string $password, string $remember) :string
    {
		/*Function takes a string login and string password.
		 *Checks if login and password is valid.
		 *If login and password is valid, sets parameters from config into session.
		 *If login and password is not valid, returns an error message.
		 */
        $user_information = DB::query("SELECT `ut_id` AS 'user_id', `ut_login` AS 'login', `ut_password` AS 'password' FROM `user_table` WHERE `ut_login` = :login AND `ut_password` =  :password", array(':login'=>$login, ':password'=>hash('sha256', $password)));
		$message = "Invalid username or password";
		if ($user_information) {
		    if ($remember === "no") {
		        $_SESSION['user_id'] = $user_information[0]['user_id'];
		        $message = "Logged in";
		        //break;
            } else {
		        $cstrong = true;
		        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
		        $user_id = $user_information[0]['user_id'];
		        $date_time = date("Y-m-d H:i:s", time() + (60*60*24*14));
		        DB::query("INSERT INTO `auth_token`(`at_id`, `at_token`, `at_ut_id`, `at_expires`) VALUES (NULL,:token,:ut_id,:date_time)", array(':token'=>hash('sha256', $token), ':ut_id'=>$user_id, ':date_time'=>$date_time));
		        setcookie('ID', $token, time() + (60*60*24*14));
		        $_SESSION['user_id'] = $user_id;
		        $message = "Logged in";
		        }
		}


		return $message."<br>";
    }

    public function logout() :void
    {
		//Function deletes parameters from session and destroys session.
        if (isset($_COOKIE['ID'])) {
            setcookie('ID', "null", 1);
            DB::query("DELETE FROM `auth_token` WHERE `at_ut_id` = :user_id", array(':user_id'=>$_SESSION['user_id']));
        }
        unset($_SESSION['user_id']);
        session_destroy();
    }
}
