# requestLogger

A simple Slim Framework middleware for logging client requests and server responses.

It can be useful for a REST API development, especially debugging (originally, I made it for this purpose).

**Requires Slim v4 and (of course) PHP 7.4 minimum**

## Installation

Simply put `RequestLogger`.php in your middleware directory.

Composer package will arrive later... hopefully. 

## Usage example

```php
...
$app->post('/myroute', '\myController:myMethod')
            ->add(new \middleware\RequestLog($this));
...
```

## TODO

- Make a composer package
- ?

## License

MIT

[slim]: https://www.slimframework.com/docs/v4/