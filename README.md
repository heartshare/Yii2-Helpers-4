Most Common Helpers
===================
Most Common Helpers for php like ZIP Directory, GeoIP, Fa2En numbers, Copy Directory, Remove Directory funcs

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist royal/yii2-helpers "*"
```

or add

```
"royal/yii2-helpers": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
use \Royal\Library;
```

Available static methods
----
```php
// Copy direcotry recursivly.
\Library\Common::copyDirectory('/var/www/html/', 'var/www/backup', 0755);
// Get ip location [region, city].
\Library\GeoLocation::byIp('192.168.1.1');
```
