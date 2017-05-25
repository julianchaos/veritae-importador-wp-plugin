<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of importer
 *
 * @author julianchaos
 */
class Importer {
	public $_conn = null;
	
	public function __construct() {
		$this->_conn = mysqli_connect("localhost", "root", "", 'ndrade_veritae_old');
	}
	protected function BRDateToSystem($date) {
		$splitted = explode("/", $date);
		
		$return = "{$splitted[2]}-" . $this->normalizeDateItem($splitted[1]) . "-" . $this->normalizeDateItem($splitted[0]);
		return $return;
	}
	private function normalizeDateItem($item) {
		if(strlen($item) === 1) {
			return ("0" . $item);
		}
		
		return $item;
	}
	
	protected function insertPost($post) {
		$post['post_status'] = 'publish';
		$post['comment_status'] = 'closed';
		
		echo "<pre>";
		$error = wp_insert_post($post, true);
		var_dump($error);
		var_dump($post);
		echo "</pre>";
	}
}
