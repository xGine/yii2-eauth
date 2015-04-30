<?php
namespace nodge\eauth\services;

use nodge\eauth\oauth2\Service;

/**
 * Instagram provider class.
 *
 * @package application.extensions.eauth.services
 */
class InstagramOAuth2Service extends Service
{
	/**
	 * Full list of scopes may be found here:
	 * http://instagram.com/developer/authentication/#scope
	 */
	const SCOPE_BASIC = 'basic';
	const SCOPE_COMMENTS = 'comments';
	const SCOPE_RELATIONSHIPS = 'relationships';
	const SCOPE_LIKES = 'likes';
	protected $name = 'instagram';
	protected $title = 'Instagram';
	protected $type = 'OAuth2';
	protected $jsArguments = array();
	protected $scopes = array(self::SCOPE_BASIC);
	protected $providerOptions = array(
		'authorize' => 'https://api.instagram.com/oauth/authorize',
		'access_token' => 'https://api.instagram.com/oauth/access_token'
	);
	protected $baseApiUrl = 'https://api.instagram.com/v1/';
	protected $errorParam = 'error_code';
	protected $errorDescriptionParam = 'error_message';
	protected function fetchAttributes()
	{
		$proxy = $this->getProxy();
		if (!$proxy) {
			return false;
		}
		$token = $proxy->getAccessToken();
		if (!$token) {
			return false;
		}
		$user = $token->getExtraParams();
		if (!array_key_exists('user', $user) || !is_array($user['user'])) {
			return false;
		}

		$this->attributes['id'] = $user['user']['id'];
		$names = explode(' ', $user['user']['full_name']);
		if (count($names) == 2) {
			$this->attributes['first_name'] = $names[0];
			$this->attributes['last_name'] = $names[1];
		} else {
			$this->attributes['first_name'] = $user['user']['full_name'];
			$this->attributes['last_name'] = '';
		}
		//$this->attributes['birthdate'] = '';
		$this->attributes['gender'] = '';
		//$this->attributes['email'] = '';
		return true;
	}
	/**
	 * @return array
	 */
	public function getAccessTokenArgumentNames()
	{
		$names = parent::getAccessTokenArgumentNames();
		$names['expires_in'] = 'expired';
		return $names;
	}
	/**
	 * @param string $response
	 * @return array
	 */
	public function parseAccessTokenResponse($response)
	{
		// Facebook gives us a query string or json
		if ($response[0] === '{') {
			$data = json_decode($response, true);
			$data['expired'] = 86400;
			return $data;
		}
		else {
			parse_str($response, $data);
			$data['expired'] = 86400;
			return $data;
		}
	}
	/**
	 * Returns the error array.
	 *
	 * @param array $response
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchResponseError($response)
	{
		if (isset($response['error'])) {
			return array(
				'code' => $response['error']['code'],
				'message' => $response['error']['message'],
			);
		} else {
			return null;
		}
	}
	/**
	 * @param array $data
	 * @return string|null
	 */
	public function getAccessTokenResponseError($data)
	{
		$error = $this->fetchResponseError($data);
		if (!$error) {
			return null;
		}
		return $error['code'].': '.$error['message'];
	}
}