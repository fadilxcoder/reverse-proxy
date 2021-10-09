<?php

# CONFIGS
// const APP_URL = 'http://dev.api.hfx.local/';
// const AUTH_BEARER = 'Bearer icAESDk4FQYhhubVwCPExcnax7VMUxfHJlbrbGKg3cXBRg2ZqHRf8uXk9hOnSFVlcsuerf1+62RxmZVrXS4n1UqBiv8ruZnj00BMWnOa5u0=';


# Loading vendor
if (file_exists(__DIR__ . '/vendor/autoload.php')) :
	require __DIR__ . '/vendor/autoload.php';
else :
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo 'Did you install the dependencies ? ☺';
	exit(1);
endif;

# Namespaces
use Dotenv\Dotenv;
use Proxy\Proxy;
use Proxy\Filter\RemoveEncodingFilter;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Laminas\Diactoros\ServerRequestFactory;
use \GuzzleHttp\Exception\BadResponseException;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

Dotenv::createImmutable(__DIR__ . '/')->load();

# Create a PSR7 request based on the current browser request.
$request = ServerRequestFactory::fromGlobals();

# Create a guzzle client
$guzzle = new GuzzleHttp\Client();

# Create the proxy instance
$proxy = new Proxy(new GuzzleAdapter($guzzle));

# Add a response filter that removes the encoding headers.
$proxy->filter(new RemoveEncodingFilter());

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try 
{
	# Authorization bearer check
	if (!isset($request->getServerParams()['HTTP_AUTHORIZATION'])) {
		throw new Exception('Authorization bearer is missing', 502);
	}

	# Version check
	if (!isset($request->getServerParams()['HTTP_VERSION'])) {
		throw new Exception('Version is missing', 502);
	}

	# Endpoint check
	if (!isset($request->getServerParams()['HTTP_ENDPOINT'])) {
		throw new Exception('Endpoint is missing', 502);
	}

    # Forward the request and get the response.
    $response = $proxy
				->forward($request)
				->filter(function ($request, $response, $next) {

					# Manipulate the request object.
					// $request = $request->withHeader('Authorization', AUTH_BEARER);

					$request = $request->withHeader('Authorization', $request->getServerParams()['HTTP_AUTHORIZATION']);
					$request = $request->withHeader('Access-Control-Allow-Origin', '*');
					$request = $request->withHeader('Access-Control-Allow-Methods', 'OPTIONS,GET,POST,PUT,DELETE');
					$request = $request->withHeader('Access-Control-Max-Age', '3600');
					$request = $request->withHeader('Access-Control-Allow-Headers', 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
					

					# Call the next item in the middleware.
					$response = $next($request, $response);

					# Manipulate the response object.
					$response = $response->withHeader('X-Author', 'fadilxcoder');
					$response = $response->withHeader('Access-Control-Allow-Origin', '*');
					$response = $response->withHeader('Access-Control-Allow-Methods', 'OPTIONS,GET,POST,PUT,DELETE');
					$response = $response->withHeader('Access-Control-Max-Age', '3600');
					$response = $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');


					return $response;
				})
				->to($_ENV['API_SERVER'] . $request->getServerParams()['HTTP_VERSION'] . $request->getServerParams()['HTTP_ENDPOINT']);
				// ->to(APP_URL);

    # Output response to the browser.
    (new SapiEmitter)->emit($response);
} 
catch(BadResponseException $e) 
{
    # Correct way to handle bad responses
    (new SapiEmitter)->emit($e->getResponse());
}
catch(Exception $e) 
{
    # Catch exception
    echo $e->getMessage();
}
