<?php defined('SYSPATH') OR die('No direct access.');
/**
 * Honeypot module for Kohana.
 *
 * Creates Honeypot validation which is a replacement for
 * captcha security. While this method is not foolproof, it
 * certainly has its good points. It can also serve as a CSRF
 * security library as it checks for token validity.
 *
 * @author  Neo Ighodaro <jeeniors@gmail.com>
 * @package Honeypot
 */
class Honeypot_Core {

	/**
	 * The unique name of session used to store Honeypot data. 
	 *
	 * @var  string
	 */
	protected const KEY = 'kohana_honeypot_key';

	/**
	 * The name of the hidden form field that should remain empty.
	 *
	 * @var  string
	 */
	protected const FIELD_NAME = '.full_name.';

	/**
	 * The name of the token field. this can protect against CSRF
	 * attacks.
	 *
	 * @var  int
	 */
	protected const TOKEN_FIELD_NAME = '_form_token';

	/**
	 * The time it should take before the form is seen as filled
	 * by a human. Time in minutes.
	 *
	 * @var  int
	 */
	protected static $timeout = 1;


	/**
	 * Creates two hidden form fields. One that is always empty, and
	 * one that could possibly be used for CSRF validation as it contains
	 * a token.
	 *
	 * <code>
	 *    echo Honeypot::make() // use the same token throughout the view
	 *
	 *    // force a new session to be created, invalidates previous
	 *    echo Honeypot::make(true)
	 * </code>
	 *
	 * @param  bool  $force_new
	 * @return string
	 */
	public static function make($force_new = FALSE)
	{
		static $honeypot_data = array();

		if (empty($honeypot_data) OR $force_new === TRUE)
		{
			// Honeypot data
			$honeypot_data = array(
				'key'		=> md5(uniqid(rand(), TRUE)),
				'created'	=> time() + (Honeypot::$timeout * 60),
			);
		}

		// Create Honeypot Session
		Session::instance()->set(Honeypot::KEY, $honeypot_data);

		// return HTML Forms
		return
			Form::hidden(Honeypot::FIELD_NAME, '') . "\n" .
			Form::hidden(Honeypot::TOKEN_FIELD_NAME, $honeypot_data['key']) . "\n";
	}


	/**
	 * Checks if a Honeypot validation failed or passed. Can also
	 * check for CSRF.
	 *
	 * <code>
	 *    // Using in Validation
	 *    ->rule(Honeypot::FIELD_NAME, 'Honeypot::check') // no csrf
	 *    ->rule(Honeypot::FIELD_NAME, 'Honeypot::check', array(':value')) // with csrf
	 * </code>
	 *
	 * @param  bool  $csrf_check
	 * @return bool
	 */
	public static function check($csrf_check = FALSE)
	{
		// Fetch Honeypot data once
		$honeypot_data = Session::instance()->get_once(Honeypot::KEY);

		// If one or none of the expected honeypot data is set then theres no need
		// to go further, its an invalid request. Abort!
		if ( ! isset($honeypot_data['key']) OR ! isset($honeypot_data['created']))
			return FALSE;

		// This field is invisible to users and thus should NOT ever be filled
		// except some over-enthusiastic bot filled it in?
		if (Arr::get($_POST, Honeypot::FIELD_NAME) !== '')
			return FALSE;

		// The token has not expired, this form was filled waay too fast.
		// An over-enthusiastic bot.
		if (time() < $honeypot_data['created'])
			return FALSE;

		// Validate CSRF token. Killing two birds with one stone.
		if ($csrf_check !== FALSE)
		{
			// Posted token
			$posted_token = trim(Arr::get($_POST, Honeypot::TOKEN_FIELD_NAME, NULL));

			// If posted token is empty, or does not match stored key, abort!
			if (empty($posted_token) OR $posted_token !== $honeypot_data['key'])
				return FALSE;
		}

		return TRUE;
	}

} // End Honeypot_Core