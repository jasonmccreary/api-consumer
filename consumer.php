<?php
require 'vendor/autoload.php';

$faker = Faker\Factory::create();
$client = new GuzzleHttp\Client();

$base_url = 'http://laravel-test.dev/api';
$api_key = 'testapikeyforconsumer';
$private_key = md5($api_key);
$nonce = time() - 30;

// list
$request = $base_url . '/restaurants';
$data = [
    'api_key' => $api_key,
    'nonce' => $nonce
];

$data['signature'] = generate_hmac('get', $request, $data, $private_key);
output_response($client->get($request, ['query' => $data]));

// create
$request = $base_url . '/restaurants';
$data = [
    'api_key' => $api_key,
    'nonce' => $nonce,
    'name' => 'The Office',
    'address' => '118 E Main St',
    'city' => 'Louisville',
    'state' => 'KY',
    'postal_code' => '40202',
    'price_id' => 2,
    'style_id' => 1
];

$data['signature'] = generate_hmac('post', $request, $data, $private_key);
$response = $client->post($request, ['body' => $data]);
output_response($response);
$json = $response->json();
$restaurant = $json['data'];

// read
$request = $base_url . '/restaurants/' . $restaurant['id'];
$data = [
    'api_key' => $api_key,
    'nonce' => $nonce
];

$data['signature'] = generate_hmac('get', $request, $data, $private_key);
output_response($client->get($request, ['query' => $data]));

// update
$request = $base_url . '/restaurants/' . $restaurant['id'];
$data = [
    'api_key' => $api_key,
    'nonce' => $nonce,
    'name' => 'Indatus'
];

$data['signature'] = generate_hmac('put', $request, $data, $private_key);
echo $data['signature'], PHP_EOL;
output_response($client->put($request, ['body' => $data]));
exit;
// delete
$request = $base_url . '/restaurants/' . $restaurant['id'];
$data = [
    'api_key' => $api_key,
    'nonce' => $nonce
];

$data['signature'] = generate_hmac('delete', $request, $data, $private_key);
output_response($client->delete($request, ['body' => $data]));

function output_response($response) {
	echo PHP_EOL;
    echo '* ' . $response->getEffectiveUrl() . PHP_EOL;
	echo '* ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . PHP_EOL . PHP_EOL;
	echo $response->getBody(), PHP_EOL, PHP_EOL;
}

function generate_hmac($method, $request, $data, $private_key) {
	ksort($data);

	$payload = strtolower($method . $request . http_build_query($data));

	return hash_hmac('sha256', $payload, $private_key);
}