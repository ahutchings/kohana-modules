# PHPRedis for Kohana

[Kohana](http://kohanaframework.org/) is an elegant, open source, and object oriented HMVC framework built using PHP5, by a team of volunteers. It aims to be swift, secure, and small.

[PHPRedis](https://github.com/phpredis/phpredis) is an extension providing an API for communicating with the Redis key-value store. It is compiled in C.

[PHPRedis for Kohana](https://github.com/Xackery/kohana-phpredis) combines PHPRedis into Kohana modeled after the Database module design.

## Documentation
Kohana's documentation can be found at <http://kohanaframework.org/documentation> which also contains an API browser.

Installing PHPRedis can be found at <https://github.com/phpredis/phpredis>.

-----

# Installing/Configuring
-----
First, ensure you [install PHPRedis](https://github.com/phpredis/phpredis#installingconfiguring) and have a [working Kohana environment](http://kohanaframework.org/3.3/guide/kohana/install).

Inside /application/bootstrap.php:
Add this line in the modules listing (There will be similar entries near it):
~~~
'redis'		=> MODPATH . 'redis',
~~~

Add this line around where the Cookie::salt area is:
~~~
Session::$default = 'redis';
~~~

Much like Kohana's database configuration, create a file inside /application/config/ called redis.php
Change this file to reflect the settings your redis is using (by default, it connects locally).

# Setup Verification
-----
Assuming you have a local redis instance going for development, in a command line, type in:
~~~
redis-cli
~~~

This should occur in a prompt like so:
~~~
127.0.0.1:6379>
~~~

Inside this prompt, you can type:
~~~
127.0.0.1:6379> keys *
(empty list or set)
~~~
To see all keys currently stored inside redis. It should be empty. If, inside a controller action you do a call to:
~~~
Session::instance()->set('test', 'key');
~~~
You should now get result data.
~~~
127.0.0.1:6379> keys *
1) "554d4651b9dba6-79472033"
~~~
This means a key exists with session data as intended.

You can confirm the expiration using
~~~
127.0.0.1:6379> ttl 554d4651b9dba6-79472033
(integer) 1765
~~~
This shows how many seconds until the session expires.