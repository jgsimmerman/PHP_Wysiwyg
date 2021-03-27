<?php 
//namespace Google;

//use Google_Client;
class Google {

	protected $CI;
	public $client;

	public function __construct($param) {

		//$this->CI =& get_instance();
        //$this->CI->load->library('session');
        //$this->CI->config->load('google_config');
		require_once(__DIR__.'/Google/autoload.php');
       	$this->client = new Google_Client();
	  	// echo "<pre>";print_r($param);
		//echo "ues";exit;
		$this->client->setClientId($param[0]);
		$this->client->setClientSecret($param[1]);
		$redirect_url = url() .'login/google';

		$this->client->setRedirectUri($redirect_url);

		$this->client->setScopes(array(
			"https://www.googleapis.com/auth/plus.login",
			"https://www.googleapis.com/auth/plus.me",
			"https://www.googleapis.com/auth/userinfo.email",
			"https://www.googleapis.com/auth/userinfo.profile"
			)
		);

	}

	public function get_login_url(){

		return  $this->client->createAuthUrl();

	}

	public function validate() {	

		if (isset($_GET['code'])) {
			
			$this->client->authenticate($_GET['code']);
			$_SESSION['access_token'] = $this->client->getAccessToken();
			if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {

				$this->client->setAccessToken($_SESSION['access_token']);
				$google_service = new Google_Service_Oauth2($this->client);
				$gpUserProfile = $google_service -> userinfo -> get();

				$info = array(); 
				$info['id']  = !empty($gpUserProfile['id'])?$gpUserProfile['id']:''; 
				$info['email'] = !empty($gpUserProfile['email'])?$gpUserProfile['email']:''; 
				$info['name'] = !empty($gpUserProfile['given_name'])?$gpUserProfile['given_name']:''; 
				$info['link'] = !empty($gpUserProfile['locale'])?$gpUserProfile['locale']:''; 
				$info['profile_pic'] = !empty($gpUserProfile['picture'])?$gpUserProfile['picture']:''; 

				return  $info;
			}

		}
		
	}

}