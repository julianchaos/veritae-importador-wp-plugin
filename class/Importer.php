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
	public $_error = [];
	
	public function __construct() {
		$server = "localhost";
		$user = "pma";
		$pw = "";
		$database = "simulacaovirtual_veritae_old";
		
		include "connection.override";
		
		$this->_conn = mysqli_connect($server, $user, $pw, $database) or die('Erro ao conectar à base de importação');
	}
	protected function BRDateToSystem($date) {
		$splitted = explode("/", $date);

		$return = $this->normalizeYearItem($splitted[2]) . "-" . $this->normalizeDateItem($splitted[1]) . "-" . $this->normalizeDateItem($splitted[0]);
		return $return;
	}
	private function normalizeDateItem($item) {
		if(strlen($item) === 1) {
			return ("0" . $item);
		}
		if(strlen($item) === 3) {
			return substr($item, 1, 2);
		}

		return $item;
	}
	private function normalizeYearItem($item) {
		if(strlen($item) === 2) {
			return ("20" . $item);
		}
		
		return $item;
	}
	
	protected function getTerm($search_by, $name, $tax) {
		$term = get_term_by($search_by, $name, $tax, 'ARRAY_A');
		
		if(!is_array($term)) {
			$term = wp_insert_term($name, $tax);
		}
		
		if(is_array($term)) {
			return $term['term_id'];
		}
		return null;
	}
	
	protected function insertPost($post) {
		$post['post_status'] = 'publish';
		$post['comment_status'] = 'closed';
		
		$error = wp_insert_post($post, true);
		return $error;
	}
}
