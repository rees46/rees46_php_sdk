<?php

require 'REES46.php';

define('SHOP_ID', 'b6ef5e0003904ba8245eb7aac0c286');
define('SHOP_NUMBER', 159);

// Test parts flags
define('TEST_GENERATE_SSID', false);
define('TEST_PUSH', true);
define('TEST_RECOMMENDATIONS', true);

if(TEST_GENERATE_SSID) {

	// Если пользователь совсем новый, то его необходимо инициализировать на стороне REES46 API - просто получите у него уникальный идентификатор
	$rees46 = new REES46(SHOP_ID);

	// Затем сохраняем куда-нибудь токен пользователя, чтобы при следующем запросе не генерировать новый
	// [... save $rees46->getSSID() to database or cookie]

} else {

	// Если у нас в базе есть токен пользователя, получаем его и устанавливаем.
	$ssid_user_ssid = 'b023e34f-8f76-403b-8772-b14408ad72e8';

	// Если пользователь уже был инициализирован на стороне REES46 API и вы сохранили его токен в базе, то просто инициализируйте модуль REES46 без лишних запросов к серверу
	$rees46 = new REES46(SHOP_ID, $current_user_ssid);
}

$ssid = $rees46->getSSID();
assert(!empty($ssid));

$rees46->setUser(33, 'sam@mkechinov.ru', false);

if(TEST_PUSH) {

	// Test view event
	$rees46->track('view', array(
		array(
			'item_id' => 15,
			'price' => 2000,
			'is_available' => 1,
			'categories' => array(5, 7),
			'name' => 'iPhone 5S Gold',
			'description' => 'Самый классный и модный iPhone из всех андроидов',
			'url' => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
			'image_url' => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
			'tags' => 'iphone, phone, smartphone, gold',
			'recommended_by' => 'interesting'
		)
	));

	// Test cart event
	$rees46->track('cart', array(
		array(
			'item_id' => 15,
			'price' => 2000,
			'is_available' => 1,
			'categories' => array(5, 7),
			'name' => 'iPhone 5S Gold',
			'description' => 'Самый классный и модный iPhone из всех андроидов',
			'url' => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
			'image_url' => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
			'tags' => 'iphone, phone, smartphone, gold'
		)
	));

	// Test remove from cart event
	$rees46->track('remove_from_cart', array(
		array(
			'item_id' => 15,
			'price' => 2000,
			'is_available' => 1,
			'categories' => array(5, 7),
			'name' => 'iPhone 5S Gold',
			'description' => 'Самый классный и модный iPhone из всех андроидов',
			'url' => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
			'image_url' => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
			'tags' => 'iphone, phone, smartphone, gold'
		)
	));

	// Test purchase event
	$rees46->track('purchase', array(
			array(
				'item_id' => 15,
				'price' => 2000,
				'is_available' => 1,
				'categories' => array(5, 7),
				'name' => 'iPhone 5S Gold',
				'image_url' => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
				'tags' => 'iphone, phone, smartphone, gold',
				'rating' => 4,
//				'recommended_by' => 'popular'
			),
			array(
				'item_id' => 11,
				'price' => 3000,
				'name' => 'iPhone 5S Gray',
				'is_available' => 1,
				'category' => 12,
//				'image_url' => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
				'tags' => 'iphone, phone, smartphone, gold'
			)
		),
		33
	);

}

if(TEST_RECOMMENDATIONS) {

	// Popular
	$ids = $rees46->recommend('popular');
	assert(is_array($ids));

	// Popular in category
	$ids = $rees46->recommend(
		'popular',
		array(
			'category' => 99
		),
		20
	);
	assert(is_array($ids));

	// Interesting
	$ids = $rees46->recommend('interesting');
	assert(is_array($ids));

	// Interesting except current item
	$ids = $rees46->recommend(
		'interesting',
		array(
			'item' => 15
		),
		20
	);
	assert(is_array($ids));

	// Also bought
	$ids = $rees46->recommend(
		'also_bought',
		array(
			'item' => 15
		)
	);
	assert(is_array($ids));

	// Similar
	$ids = $rees46->recommend(
		'similar',
		array(
			'item' => 15
		)
	);
	assert(is_array($ids));


	// Similar except cart
	$ids = $rees46->recommend(
		'similar',
		array(
			'item' => 15,
			'cart' => array(17,87)
		)
	);
	assert(is_array($ids));

	// See also
	$ids = $rees46->recommend(
		'see_also',
		array(
			'cart' => array(17,87)
		)
	);
	assert(is_array($ids));

	// Recently viewed
	$ids = $rees46->recommend('recently_viewed');
	assert(is_array($ids));

	// Buying now except current and cart
	$ids = $rees46->recommend(
		'buying_now',
		array(
			'item' => 15,
			'cart' => array(17,87)
		)
	);
	assert(is_array($ids));

}