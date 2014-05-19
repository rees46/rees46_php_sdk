<?php

define('SHOP_ID', 'e10adc3949ba59abbe56e057f20f883e');

require 'REES46.php';

// Инициализируем модуль
$rees46 = new REES46(SHOP_ID);

// Инициализируем пользователя
$rees46->setUser(33, 'mk@rees46.com', false);



// ** Трекаем несколько событий

// Пользователь посмотрел товар
$rees46->track('view', array(
	item_id => 15,
    price => 2000,
    is_available => 1,
    category_id => 9,
	name => 'iPhone 5S Gold',
	description => 'Самый классный и модный iPhone из всех андроидов',
	url => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
	image_url => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
	tags => 'iphone, phone, smartphone, gold'
));

// Пользователь добавил товар в корзину
$rees46->track('cart', array(
	item_id => 15,
	price => 2000,
	is_available => 1,
	category_id => 9,
	name => 'iPhone 5S Gold',
	description => 'Самый классный и модный iPhone из всех андроидов',
	url => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
	image_url => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
	tags => 'iphone, phone, smartphone, gold'
));

// Пользователь убрал товар из корзины
$rees46->track('remove_from_cart', array(
	item_id => 15,
	price => 2000,
	is_available => 1,
	category_id => 9,
	name => 'iPhone 5S Gold',
	description => 'Самый классный и модный iPhone из всех андроидов',
	url => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
	image_url => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
	tags => 'iphone, phone, smartphone, gold'
));

// Пользователь оформил заказ на один или несколько товаров
$rees46->track('purchase', array(
	array(
		item_id => 15,
		price => 2000,
		is_available => 1,
		category_id => 9,
		name => 'iPhone 5S Gold',
		description => 'Самый классный и модный iPhone из всех андроидов',
		url => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
		image_url => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
		tags => 'iphone, phone, smartphone, gold'
	),
	array(
		item_id => 16,
		price => 3000,
		is_available => 1,
		category_id => 12,
		name => 'MacBook Air',
	),
));

// Пользователь оценил товар
$rees46->track('rate', array(
	rating => 3.5,
	item_id => 15,
	price => 2000,
	is_available => 1,
	category_id => 9,
	name => 'iPhone 5S Gold',
	description => 'Самый классный и модный iPhone из всех андроидов',
	url => 'http://market.yandex.ru/model.xml?text=apple%20iphone%205s%2016gb&srnum=537&modelid=10495456&hid=91491',
	image_url => 'http://mdata.yandex.net/i?path=b0910230234_img_id2130334858748450706.jpg',
	tags => 'iphone, phone, smartphone, gold'
));



// ** Запрос рекомендаций

// Просто популярные товары с учетом интересов пользователя
$ids = $rees46->recommend('popular');

// Популярные товары в конкретной категории с учетом интересов пользователя
$ids = $rees46->recommend('popular', array(
	category_id => 15
));

// Недавно просмотренные пользователем товары
$ids = $rees46->recommend('recently_viewed');

// Товары, которые заинтересуют пользователя, за исключением того, который передан параметром
$ids = $rees46->recommend('interesting', array(
	item => 337
));

// Сопутствующие товары
$ids = $rees46->recommend(
	'also_bought',
	array(
		item => 337
	),
	4
);

// Похожие товары, за исключением тех, которые есть в корзине
$ids = $rees46->recommend('similar', array(
	item => 337,
	cart => array(12, 33984, 353)
));

// "Посмотрите также", за исключением тех, которые есть в корзине
$ids = $rees46->recommend('see_also', array(
	cart => array(12, 33984, 353)
));

// Прямо сейчас люди пкоупают
$ids = $rees46->recommend('buying_now', array(
	item => 337,
	cart => array(12, 33984, 353)
));


