##Honeypot Security Module For Kohana Framework

###Introduction
This is a Security Module for the [Kohana Framework](http://kohanaframework.org). Its a good replacement for Captcha.
To install this module, download the zip, and then unzip into the Kohana modules directory.

Activate the module by adding it to your <code>application/bootstrap.php</code> file.

To use it, add it to a form in your views with:

	echo Honeypot::make()

Then in your controller you can use this with the Validation library, or as a standalone.

	// Stand alone without CSRF check
	if ( ! Honeypot::check())
	{
		// not valid
	}

	// Stand alone with CSRF check
	if ( ! Honeypot::check(true))
	{
		// not valid
	}

	// Validation
	$post = Validation($_POST)
			 ->rule(Honeypot::FIELD_NAME, 'Honeypot::check') // without CSRF check
			->rule(Honeypot::FIELD_NAME, 'Honeypot::check', array('true')); //  with CSRF check


Notice an error, please use the issue tracker, or fork, fix and submit a pull request. 