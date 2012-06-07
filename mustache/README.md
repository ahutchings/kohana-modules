# Kohana-Mustache

This is a [Mustache](http://mustache.github.com/) module for [Kohana](http://kohanaframework.org/). Its particularity is that it works exactly like Kohana regular views, so you don't need to change anything to the way you instantiate views, assign variables to them, etc. You can even mix and match PHP views with Mustache views. If the module doesn't find a mustache view, it will default to a PHP view.

## Usage

- Add the module to Kohana. Copy the `mustache` folder into the `modules` folder of Kohana.

- Enable the module in `bootstrap.php`:

<!-- -->
	Kohana::modules(array(
		'mustache'  => MODPATH.'mustache',       // Mustache support in Kohana
		// 'auth'       => MODPATH.'auth',       // Basic authentication
		// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
		// ...
		));

- Create a Mustache view. For example, add this file to `application/views/example.mustache`:

<!-- -->
	{{#user}}
		Hello {{name}}
		You have just won ${{value}}!
		{{#in_ca}}
		Well, ${{taxed_value}}, after taxes.
		{{/in_ca}}
	{{/user}}

- Finally, to instantiate the view, simply use the usual Kohana syntax:

<!-- -->
	$user = array(
		"name" => "Chris",
		"value" => 10000,
		"taxed_value" => 10000 - (10000 * 0.4),
		"in_ca" => true
	);
	
	$view = View::factory('example');
	$view->user = $user;
	echo $view->render();
	
Which should display:

	Hello Chris You have just won $10000! Well, $6000, after taxes.
	
## Mixing PHP and Mustache view

The module transparently supports mixing PHP and Mustache views. If the module finds a view called `example.mustache`, it's going to use that. However, if this file doesn't exist, it's going to look for `example.php` and passes it to Kohana for rendering.

## License

[LGPL3](http://www.gnu.org/licenses/lgpl.html)