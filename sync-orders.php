<?php
/**
 * User: noff
 * Date: 22/07/14
 * Time: 23:22

 * This is an example of mass export orders to REES46 procedure.
 *
 * Assumes you have tables in database:
 * orders:
 *   id:string
 *   status:integer
 *
 */

// Edit these parameters according to your shop credentials
define('SHOP_ID', 'c60f487cca799ab42f1e0127b4f232');
define('SHOP_SECRET', '7f2df453d92cd48c5e52c1aeb9f4c812');

// Here prepare your orders data from your database.
// In this example we just prepare array of data instead of database data.
// So you need to change this code according your shop architecture.
$orders = array(
	array( 'id' => '153404', 'status' => 0 ),
	array( 'id' => 'order4', 'status' => 1 )
);


// ** Prepare CURL object

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_URL, 'http://api.rees46.com/import/sync_orders');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Split orders by 1000 per request
$chunks = array_chunk($orders, 1000);

foreach($chunks as $key => $chunk) {
	$data = array(
		'shop_id' => SHOP_ID,
		'shop_secret' => SHOP_SECRET,
		'orders' => $chunk
	);
	$body = json_encode($data);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

	$response = curl_exec($ch);
	$response_info = curl_getinfo($ch);

	if($response_info['http_code'] < 200 || $response_info['http_code'] >= 300) {
		$data_as_string = var_export($data, true);
		$response_as_string = var_export($response_info, true);
		error_log("Error request \n\nData:\n'{$data_as_string}'\n\nResponse Info:\n{$response_as_string}\n\nResponse:\n'{$response_body->message}'");
		exit(1);
	} else {
		echo 'Chunk ' . ($key+1) . ' done. Response: ' . $response . PHP_EOL;
	}

}





// ** Close all connections and clean

curl_close($ch);

echo 'Done.' . PHP_EOL;
