This is a module for Kohana Framework (http://kohanaframework.org/) version 3.x.
It will get the reputation score from SenderScore (http://www.senderscore.org) for an ip address or a cidr range.

Extension Requirements:
    curl
    dom

Exceptions Throw:
    DomainException
    LogicException    


TODO:
    Accept an array of ip addresses or cidr ranges to check.

Examples:

    Example 1 (single ip address set in Check()):

    $ss			= new Senderscore();
    $ss->cookieFile	= '/tmp/ss.cookie';	// required or exception is thrown
    $score		= $ss->Login('username@gmail.com', 'some password')
			    ->Check('127.0.0.1');

    foreach ($score as $ip => $score)
    {
	print 'IP Address: ' . $ip . ' [] Score: ' . $score . '<br />';
    }

    output:
	IP Address: 127.0.0.1 [] Score: 95

    <=== BREAK ===>

    Example 2 (single ip address set in the constructor):

    $ss			= new Senderscore('127.0.0.1');
    $ss->cookieFile	= '/tmp/ss.cookie';	// required or exception is thrown
    $score		= $ss->Login('username@gmail.com', 'some password')
			    ->Check();

    foreach ($score as $ip => $score)
    {
	print 'IP Address: ' . $ip . ' [] Score: ' . $score . '<br />';
    }

    output:
	IP Address: 127.0.0.1 [] Score: 95

    <=== BREAK ===>

    Example 3 (IP Address with cidr):

    $ss			= new Senderscore();
    $ss->cookieFile	= '/tmp/ss.cookie'; //required or exception is thrown
    $score		= $ss->Login('username@gmail.com', 'password')
			    ->Check('127.0.0.1/29');

    foreach ($score as $ip => $score)
    {
	print 'IP Address: ' . $ip . ' [] Score: ' . $score . '<br />';
    }

    output:
	IP Address: 127.0.0.1 [] Score: 95
	IP Address: 127.0.0.2 [] Score: 80
	IP Address: 127.0.0.3 [] Score: 95
	IP Address: 127.0.0.4 [] Score: 92
	IP Address: 127.0.0.5 [] Score: 99
	IP Address: 127.0.0.6 [] Score: 10
	IP Address: 127.0.0.7 [] Score: 30

