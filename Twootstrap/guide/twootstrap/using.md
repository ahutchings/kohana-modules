#Twootstrap Asset Manager
## Using The Asset Manager

###Queuing Assets
To use the asset manager, you must first queue your assets. Queuing assets allows
you to call those queued assets later in your view file. You could for instance
queue assets in the controller and call them in the view/template file.
With the asset manager its really very easy managing all your web sites assets.
Wanna know how easy it is? Look at the query syntax below...

####Queuing Syntax Example:
	  // Plenty Asset Queue Using method chaining
	  Twootstrap::instance('asset')
	 		->queue('bootstrap', 'assets/css/bootstrap.css')
	 		->queue('default', 'assets/css/default.css', array('dependencies' => 'bootstrap'))
	 		->queue('css-again', 'assets/css/css-again.css', array('dependencies' => array('default', 'bootstrap')))
	 		->queue('other-css', 'assets/other.css', array('protocol' => 'https'))
	 		->queue('external-css', 'http://domain.ext/style.css')
	 		->queue('jquery', 'assets/js/jquery.js', array('tag' => 'footer'))
	 		->queue('bootstrap', 'assets/js/bootstrap.js', array('tag' => 'footer', 'dependencies' => 'jquery'))
	 		->queue('one-last', 'assets/style.php', array('asset_type' => 'styles'));

That looks a bit intimidating yeah? Well depending on the size of your project you could be managing that much assets.
The easy to read syntax though is:

	Twootstrap::instance('asset')->queue('jquery', 'js/jquery.js');

As you can see you can queue several assets together.From your template/view file you can call this queue like this:

    // Stylesheets
    echo Twootstrap::instance('asset')->styles();

    // Scripts
    echo Twootstrap::instance('asset')->scripts();

    // Stylesheets with tag 'footer' at the footer of the page
    echo Twootstrap::instance('asset')->styles('footer');

The asset tag is pretty useful if like me you want to load some assets at other parts of the site, for instance you want some js to be loaded after the HTML has rendered, this would prove useful.

## Queue options
The queue method takes three parameters, the:

 - **$id**: A unique name given to the asset, if another with the same $id is queued below it, it will overwrite the one above it.
 - **$src**: The source url to the asset. You can specify external links too.
 - **$options**: An array of configuration for each asset queued. Below are the options, and how to use them.

How to queue with options:

    // Queue with options
    Twootstrap::instance('asset')
			->queue('bootstrap', 'css/bootstrap.css', array(
				// Full list of options, they are all optional
				'asset_type'	=> '', // value:scripts or styles. This is useful for assets with other extensions e.g style.php
				'dependencies'	=> '', // name of dependency (array of dependencies), will not load this asset without the dependency queued
				'protocol'	=> '', // https, ftp etc. useful when you want to link internally with a protocol
				'tag'	=> '', // the asset group option. when an asset is grouped, it will only display when asset group is called
			));

##Removing an asset from a queue
To remove an asset from a queue you need to know the queue id it was queued with, and the asset type. Then do:

    // For instance...this removes the jquery from scripts queue
    Twootstrap::instance('asset')->unqueue('jquery', 'scripts');

	// This removes all the scripts queue...
	Twootstrap::instance('asset')->unqueue(NULL, 'scripts');

##Checking if an asset is already queued
To check if an item is already queued, You can do something like the following...

    if(Twootstrap::instance('asset')->queued('bootstrap') !== FALSE)
	{
		// Do something
	}

Please note that the queued() method will return ONLY **FALSE** when the queue does not exist. So if the queued() returns an integer like **0** please note that the queue indeed exists. Using a code like the one below might give you unexpected results:

    if ( ! Twootstrap::instance('asset')->queued('bootstrap'))
	{
		// This code will always execute because the queued method returns 0 which
		// is a valid queue ID but can also means FALSE in PHP
	}

##Making sure dependencies are loaded
The default behaviour is to not load an asset once the dependency is not found. You can how ever force the queue
to throw an exception if a dependency is not found. Do this by calling require_dependencies() method before queuing the
assets.

    Twootstrap::instance('asset')
		->require_dependencies()
		->queue('bootstrap', 'bootstrap.css', array('dependencies' => 'default-style'));

Note that if you use the instance method to instantiate the asset manager, this trait is will be irreversible,
and will run always on that instance of the asset manager. so its
better you use the factory method to instantiate, that way, it only affects the chain which it belongs to.

You can also look at the API browser, as the methods are well documented to ease use. Forward questions to
jeeniors@gmail.com or send me a message on [the official kohana forum](http://forum.kohanaframework.org) (@catchphraze)
i will be glad to help

[&#171; Back to the Introduction]()