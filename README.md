# DotEnvWriter

A PHP library to write values to .env (DotEnv) files

## Installation

DotEnvWriter can be installed with [Composer](https://getcomposer.org/).
```bash
composer require mirazmac/dotenvwriter
```

## Usage

```php
$writer = new \MirazMac\DotEnv\Writer(__DIR__ . '/' . '.env');

$writer
->set('APP_NAME', 'My App')
->set('APP_URL', 'https://laravel.com')
->set('APP_DIR', '${BASE_DIR}/app')
// Third parameter set to TRUE to force quote a single word value
->set('APP_BUCKET', 's3-bucket', true);

// Write the values to file
$writer->write();

```
## API
## Methods

### ``__construct(?string $sourceFile = null)``

Constructs a new instance.

**Arguments**

string|null           ``$sourceFile``  The environment file path to load values from, optional.


### `` set(string $key, string $value, bool $forceQuote = false) : self``
Set the value of an environment variable, updated if exists, added if doesn't. The values must be passed as string, even if you are setting values like ``TRUE`` ``FALSE``.
This method is chainable.

**Arguments**

string  ``$key``        The key

string  ``$value``      The value

bool    ``$forceQuote``  By default the whether the value is wrapped in double quotes is determined automatically. However, you may wish to force quote a value.


### `` delete(string $key) : self``
Delete an environment variable if present. This method is chainable.

**Arguments**

string  ``$key``        The key

### ``hasChanged() : bool``
States if one or more values has changed

### ``getContent() : string``
Returns the current content

### ``write(bool $force = false, ?string $destFile = null) : bool``
Write the contents to the ``.env`` file.

**Arguments**

bool  ``$force``  By default we only write when something has changed, but you can force to write the file by setting this to TRUE.

string|null ``$destFile`` Destionation file. By default it's the same as ``$sourceFile`` is provided



## F.A.Q
### What can this do?
``DotEnvWriter`` is a PHP library that can write (add, update, delete) values to ``.env`` files.
### And how is this done?
Well.. I'm glad you asked.. it uses some bleeding edge AI algorithm.. just kidding, RegEx.. it uses RegEx to replace the values with proper quoting and escaping and writes them back to the filesystem. While RegEx is not the best solution but it gets the job done.

### What if this corrupts my .env file?
Well, it shouldn't. It uses proper quoting and escaping before writing the values. If you want to make sure it generates a valid file, well, that's why tests are for. You can clone the repo and run ``composer install`` and then ``phpunit`` to run the tests.
The goal of this library is to generate a ``.env`` file that can be parsed without any errors using: [vlucas/phpdotenv
](https://github.com/vlucas/phpdotenv)

### How does it handle invalid ``.env`` files?
It doesn't. You see, it doesn't concern itself with validation of existing values. It simply replaces them via RegEx, and makes sure the changes that it has made is valid. But if you have an existing invalid file, it can do nothing to validate or fix that.

### Can I use this to read values from an .env file?
Yes, you can. But you really shouldn't. Thus they aren't documented. This library is for writing to the file. To properly read and load values from ``.env`` files, use something robust like:  [vlucas/phpdotenv
](https://github.com/vlucas/phpdotenv).



## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
The library is inspired by and uses some RegEx from the [msztorc/laravel-env](https://github.com/msztorc/laravel-env) package and is based on the same
[MIT](https://github.com/MirazMac/DotEnvWriter/blob/master/LICENSE) license.
