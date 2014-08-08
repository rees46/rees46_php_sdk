<?php

class REES46 {

	const API_URL = 'http://api.rees46.com/';

	public $supported_events = array(
		'view',
		'cart',
		'remove_from_cart',
		'purchase',
		'rate'
	);

	private $api_methods = array(
		'generate_ssid' => array('method' => 'GET'),
		'push' => array('method' => 'POST'),
		'recommend' => array('method' => 'GET')
	);

	public $supported_recommenders = array(
		'popular',
		'recently_viewed',
		'interesting',
		'also_bought',
		'similar',
		'see_also',
		'buying_now',
	);

	private $shop_key;
	private $ssid;
	private $protect_data = false;
	private $user_id;
	private $email;
	private $email_hash;


	/**
	 * Проверить токен в параметре.
	 * Если нет в параметре, посмотреть в куках (если есть массив).
	 * Если нет в куках и нет в параметре, инициализироваться на REES46, если не выключен флаг "инициализироваться автоматически".
	 */

	public function __construct($shop_key, $ssid = null) {
		$this->shop_key = $shop_key;
		if(empty($ssid)) {
			$this->ssid = $this->generateSSID();
		} else {
			$this->ssid = $ssid;
		}
		assert(!empty($this->shop_key));
		assert(!empty($this->ssid));
	}


	/**
	 * Возвращает токен пользователя
	 * @return mixed
	 */
	public function getSSID() {
		return $this->ssid;
	}

	/**
	 * Сгенерировать SSID для пользователя.
	 */
	private function generateSSID() {
		if(empty($this->ssid)) {
			$response = $this->request('generate_ssid', array('shop_id' => $this->shop_key));
			assert(!empty($response));
			return $response;
		} else {
			return $this->ssid;
		}
	}


	/**
	 * Установить данные пользвателя.
	 * Выполняйте этот метод перед первым обращением пользователя к API.
	 * @param Integer $user_id Идентификатор пользователя в вашей системе для более эффективной персонализации.
	 * @param String $email E-mail пользователя для более эффективной персонализации.
	 * @param bool $protect_data Передавать на сервер только MD5-хеш от e-mail
	 */
	public function setUser($user_id, $email, $protect_data = false) {
		if(!empty($user_id)) {
			$this->user_id = $user_id;
		}
		if(!empty($email)) {
			$this->email = $email;
		}
		if($protect_data) {
			$this->protect_data = true;
			if(!empty($this->email)) {
				$this->email_hash = md5($this->email);
			}
		}

		// Tests
		if(!empty($user_id)) assert($this->user_id == $user_id);
		assert($this->email == $email);
		if($protect_data) assert($this->email_hash == md5($this->email));
	}


	/**
	 * Отправляет событие о поведении пользователя на API REES46.
	 * При добавлении новых событий не забудьте отредактировать $this->supported_events.
	 * @param String $event Имя события.
	 * @param Array $items Массив товаров, для которых сработало событие
	 * @param Integer $order_id Идентификатор заказа для события purchase
	 * @return Boolean
	 */
	public function track($event, $items, $order_id = null) {

		assert(in_array($event, $this->supported_events));
		assert(is_array($items));
		assert(count($items) > 0);

		$data = null;

		if(in_array($event, $this->supported_events)) {

			switch($event) {

				case 'view':
					$data = $this->prepare_item_data_for_track($items);
					$data = $this->append_it_with_service_data($data);
					$data['event'] = $event;
					break;

				case 'cart':
					$data = $this->prepare_item_data_for_track($items);
					$data = $this->append_it_with_service_data($data);
					$data['event'] = $event;
					break;

				case 'remove_from_cart':
					$data = $this->prepare_item_data_for_track($items);
					$data = $this->append_it_with_service_data($data);
					$data['event'] = $event;
					break;

				case 'purchase':
					$data = $this->prepare_item_data_for_track($items);
					$data = $this->append_it_with_service_data($data);
					if($order_id) {
						$data['order_id'] = $order_id;
					}
					$data['event'] = $event;
					break;

				case 'rate':
					$data = $this->prepare_item_data_for_track($items);
					$data = $this->append_it_with_service_data($data);
					$data['event'] = $event;
					break;

			}

			assert(is_array($data));

			$result = $this->request('push', $data);
			return $result !== false;

		}
	}


	/**
	 * Запрашивает у API REES46 рекомендации для пользователя.
	 * При добавлении новых рекоммендеров не забудьте отредактировать $this->supported_recommenders.
	 * @param String $type Тип рекомендации
	 * @param Array $params Параметры рекомендации
	 * @param Integer $limit Максимальное число рекомендованных товаров
	 * @return Array Массив идентификаторов рекомендованных товаров
	 * @todo implement it
	 */
	public function recommend($type, $params = array(), $limit = 10) {

		assert(in_array($type, $this->supported_recommenders));
		assert(is_int($limit));
		assert($limit > 0);
		assert($limit < 200);
		assert(is_array($params));

		$ids = array();

		if(isset($params['item'])) $data['item_id'] = $params['item'];
		if(isset($params['cart'])) {
			assert(is_array($params['cart']));
			if(count($params['cart']) > 0) {
				$data['cart_count'] = count($params['cart']);
				foreach($params['cart'] as $key => $id) {
					$data["cart_item_id[{$key}]"] = $id;
				}
			}
		}
		$data['limit'] = $limit;
		$data['recommender_type'] = $type;
		$data = $this->append_it_with_service_data($data);

		assert(is_array($data));

		$response = $this->request('recommend', $data);

		if($response !== false) {
			assert(is_array($response));
			$ids = $response;
		}

		return $ids;

	}


	/**
	 * Выполняет запрос к API
	 * @param $method
	 * @param $data
	 * @return mixed
	 * @todo implement it
	 */
	private function request($method, $data) {

		$url = self::API_URL . $method;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		switch($this->get_api_request_type($method)) {
			case 'GET':
				if(is_array($data) && count($data) > 0) {
					$url .= '?' . http_build_query($data);
				}
				break;

			case 'POST':
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				break;
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		$response = curl_exec($ch);
		$response_info = curl_getinfo($ch);
		curl_close($ch);

		// Clean response
		$response = trim($response);

		if(strpos($response_info['content_type'], 'application/json') !== false) {
			$response_body = json_decode($response);
		} else {
			$response_body = $response;
		}

		if($response_info['http_code'] < 200 || $response_info['http_code'] >= 300) {

			$data_as_string = var_export($data, true);
			$response_as_string = var_export($response_info, true);
			error_log("Error request method '{$method}'\n\nURL:\n{$url}\n\nData:\n'{$data_as_string}'\n\nResponse Info:\n{$response_as_string}\n\nResponse:\n'{$response_body->message}'");
			return false;
		}

		return $response_body;

	}



	/**
	 * Подготавливает конечный массив данных по одному товару для операции трекинга события
	 * @param $items Array Исходный массив данных, полученный от программы
	 * @return array
	 */
	private function prepare_item_data_for_track($items) {

		assert(is_array($items));
		assert(count($items) > 0);

		$data = array();
		$data['count'] = count($items);
		foreach($items as $key => $item) {
			if(!empty($item['item_id'])) 		$data["item_id[{$key}]"] 		= $item['item_id'];
			if(!empty($item['price'])) 			$data["price[{$key}]"] 		= $item['price'];
			if(!empty($item['is_available'])) 	$data["is_available[{$key}]"] = $item['is_available'];
			if(!empty($item['category'])) 		$data["category[{$key}]"] 	= $item['category'];
			if(!empty($item['categories'])) 		$data["categories[{$key}]"] 	= $item['categories'];
			if(!empty($item['name'])) 			$data["name[{$key}]"] 		= $item['name'];
			if(!empty($item['description'])) 	$data["description[{$key}]"] 	= $item['description'];
			if(!empty($item['locations'])) 		$data["locations[{$key}]"] 	= $item['locations'];
			if(!empty($item['url'])) 			$data["url[{$key}]"] 			= $item['url'];
			if(!empty($item['image_url'])) 		$data["image_url[{$key}]"] 	= $item['image_url'];
			if(!empty($item['tags'])) 			$data["tags[{$key}]"] 		= $item['tags'];
			if(!empty($item['rating'])) 		$data["rating"] 		= $item['rating'];
			if(!empty($item['recommended_by'])) $data["recommended_by[{$key}]"] = $item['recommended_by'];
		}

		assert($data['count'] > 0);
		assert(isset($data['item_id[0]']));
		assert(isset($data['price[0]']));
		if($data['count'] > 1) {
			assert(isset($data['item_id[1]']));
			assert(isset($data['price[1]']));
		}

		return $data;
	}


	/**
	 * Добавляет к массиву данных дополнительные параметры о пользователе и магазине.
	 * @param Array $data
	 * @return array
	 */
	private function append_it_with_service_data($data) {

		if(isset($data['event'])) {
			assert(isset($data['count']));
			assert($data['count'] > 0);
		}

		if(!empty($this->shop_key)) 		$data['shop_id'] = $this->shop_key;
		if(!empty($this->ssid)) 			$data['ssid'] = $this->ssid;
		if(!empty($this->user_id)) 			$data['user_id'] = $this->user_id;
		if(!empty($this->email) && !$this->protect_data) $data['user_email'] = $this->email;
		if(!empty($this->email_hash)) 		$data['email_hash'] = $this->email_hash;

		// Tests
		assert(!empty($data['shop_id']));
		assert(!empty($data['ssid']));
		if(!$this->protect_data) assert(!isset($data['email']));
		if(isset($data['email'])) assert(!$this->protect_data);

		return $data;
	}


	/**
	 * Возвращает тип HTTP запроса для метода API: GET, POST
	 * @param $method Один из методов, перечисленных в $this->api_methods
	 * @return string
	 */
	private function get_api_request_type($method) {
		assert(array_key_exists($method, $this->api_methods));
		return $this->api_methods[$method]['method'];
	}


} 
