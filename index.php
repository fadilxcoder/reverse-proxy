<?php

# CONFIGS
const APP_URL = 'http://dev.api.hfx.local/v1/users';
const AUTH_BEARER = 'Bearer icAESDk4FQYhhubVwCPExcnax7VMUxfHJlbrbGKg3cXBRg2ZqHRf8uXk9hOnSFVlcsuerf1+62RxmZVrXS4n1UqBiv8ruZnj00BMWnOa5u0=';


# Loading vendor
if (file_exists(__DIR__ . '/vendor/autoload.php')) :
	require __DIR__ . '/vendor/autoload.php';
else :
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'Did you install the dependencies ? ☺';
	exit(1);
endif;

# Namespaces
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use \GuzzleHttp\Exception\BadResponseException;

# Create a PSR7 request based on the current browser request.
$request = ServerRequestFactory::fromGlobals();

# Create a guzzle client
$guzzle = new GuzzleHttp\Client();

# Create the proxy instance
$proxy = new Proxy(new GuzzleAdapter($guzzle));

# Add a response filter that removes the encoding headers.
$proxy->filter(new RemoveEncodingFilter());

try 
{
    # Forward the request and get the response.
    $response = $proxy
				->forward($request)
				->filter(function ($request, $response, $next) {

					# Manipulate the request object.
					$request = $request->withHeader('Authorization', AUTH_BEARER);

					# Call the next item in the middleware.
					$response = $next($request, $response);

					# Manipulate the response object.
					$response = $response->withHeader('X-Author', 'fadilxcoder');

					return $response;
				})
				->to(APP_URL);

    # Output response to the browser.
    (new SapiEmitter)->emit($response);
} 
catch(BadResponseException $e) 
{
    # Correct way to handle bad responses
    (new SapiEmitter)->emit($e->getResponse());
}
