<?php

class REES46 {

	private $shop_key;
	private $protect_data = false;
	private $user_id;
	private $email;
	private $email_hash;


	/**
	 * Проверить токен в параметре.
	 * Если нет в параметре, посмотреть в куках (если есть массив).
	 * Если нет в куках и нет в параметре, инициализироваться на REES46, если не выключен флаг "инициализироваться автоматически".
	 */

	public function __construct($shop_key, $user_sid, $user_id = null, $email = null, $encode_email = false) {
		$this->shop_key = $shop_key;
	}


	/**
	 * Установить данные пользвателя.
	 * Выполняйте этот метод перед первым обращением пользователя к API.
	 * @param $user_id Идентификатор пользователя в вашей системе для более эффективной персонализации.
	 * @param $email E-mail пользователя для более эффективной персонализации.
	 * @param bool $protect_data Передавать на сервер только MD5-хеш от e-mail
	 */
	public function setUser($user_id, $email, $protect_data = false) {
		if((int)$user_id > 0) {
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
	}


	/**
	 * Отправляет событие о поведении пользователя на API REES46
	 * @param String $event Имя события.
	 * @param Array $params Параметры события.
	 */
	public function track($event, $params) {
		$data = null;
		switch($event) {
			case 'view':
				$data = $this->prepare_item_data_for_track($params);
				break;
			case 'cart':
				$data = $this->prepare_item_data_for_track($params);
				break;
			case 'remove_from_cart':
				$data = $this->prepare_item_data_for_track($params);
				break;
			case 'purchase':
				if(is_array($params) && count($params) > 0) {
					$data = array();
					foreach($params as $element) {
						$data[] = $this->prepare_item_data_for_track($element);
					}
				}
				break;
			case 'rate':
				$data = $this->prepare_item_data_for_track($params);
				break;
		}
		if(is_array($data)) {
			$this->request('event', $event, $data);
		}
	}


	/**
	 * Запрашивает у API REES46 рекомендации для пользователя.
	 * @param String $type Тип рекомендации
	 * @param Array $params Параметры рекомендации
	 * @param Integer $limit Максимальное число рекомендованных товаров
	 * @return Array Массив идентификаторов рекомендованных товаров
	 */
	public function recommend($type, $params = array(), $limit = 10) {

		$ids = array();

		switch($type) {
			case 'popular':
				break;
			case 'recently_viewed':
				break;
			case 'interesting':
				break;
			case 'also_bought':
				break;
			case 'similar':
				break;
			case 'see_also':
				break;
			case 'buying_now':
				break;
		}

		return $ids;
	}



	private function request($type, $name, $data) {

		switch($type) {

			case 'event':

				break;


		}

	}


	/**
	 * Подготавливает конечный массив данных по одному товару для операции трекинга события
	 * @param $source Исходный массив данных, полученный от программы
	 * @return array
	 */
	private function prepare_item_data_for_track($source) {
		$data = array();
		$data['item_id'] = ( !empty($source['item_id']) ? $source['item_id'] : null );
		$data['price'] = ( !empty($source['price']) ? $source['price'] : null );
		$data['is_available'] = ( !empty($source['is_available']) ? $source['is_available'] : null );
		$data['category_id'] = ( !empty($source['category_id']) ? $source['category_id'] : null );
		$data['name'] = ( !empty($source['name']) ? $source['name'] : null );
		$data['description'] = ( !empty($source['description']) ? $source['description'] : null );
		$data['locations'] = ( !empty($source['locations']) ? $source['locations'] : null );
		$data['url'] = ( !empty($source['url']) ? $source['url'] : null );
		$data['image_url'] = ( !empty($source['image_url']) ? $source['image_url'] : null );
		$data['tags'] = ( !empty($source['tags']) ? $source['tags'] : null );
		$data['rating'] = ( !empty($source['rating']) ? $source['rating'] : null );
		return $data;
	}

} 