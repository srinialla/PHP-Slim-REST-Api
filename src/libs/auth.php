<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

/**
 * Class Jwt Authentication
 */
class JWTAuth
{

	/**
	 * This method create a valid token.
	 *
	 * @param	int		$id		The user id
	 * @param	string	$user	Username
	 *
	 * @return	string	JWT		Valid token.
	 */
	public static function getToken($id, $user)
	{
		$secret = SECRET;

		// date: now
		$now = date('Y-m-d H:i:s');
		// date: now +2 hours
		$future = date('Y-m-d H:i:s', mktime(date('H') + 2, date('i'), date('s'), date('m'), date('d'), date('Y')));

		$token = array(
			'header' => [ 			// User Information
				'id' 	=> 	$id, 	// User id
				'user' 	=> 	$user 	// username
			],
			'payload' => [
				'iat'	=>	$now, 	// Start time of the token
				'exp'	=>	$future	// Time the token expires (+2 hours)
			]
		);

		// Encode Jwt Authentication Token
		return JWT::encode($token, $secret, "HS256");
	}

	/**
	 * This method verify a token.
	 *
	 * @param	string	$token	token.
	 *
	 * @return	boolean
	 */
	public static function verifyToken($token)
	{
		$secret = SECRET;
		
		// Decode Jwt Authentication Token
		$obj = JWT::decode($token, new Key($secret, 'HS256'));

		// If payload is defined
		if (isset($obj->payload)) {
		  	// Gets the actual date
			$now = strtotime(date('Y-m-d H:i:s'));
	  		// Gets the expiration date
			$exp = strtotime($obj->payload->exp);
	  		// If token didn't expire
			if (($exp - $now) > 0) {
				return $obj;
			}
		}

		return false;
	}
}

?>
