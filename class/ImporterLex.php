<?php

class ImporterLex extends Importer {
	private $_areaconhecimento = array(
		'previdencia' => array(),
		'sst' => array(),
		'trabalho' => array(),
		'outros' => array(),
	);

	function __construct() {
		parent::__construct();

		// actions
		add_action('admin_menu', array($this,'admin_menu'), 11, 0);
	}
	
	function admin_menu() {
		// add page
		add_submenu_page('edit.php?post_type=veritae-importador', __('Importador Lex','veritae'), __('Importador Lex','veritae'), 'manage_options', 'veritae-lex', array($this,'init'));

	}
	
	function init() {
		echo 'init importer lex';
		
		$this->loadAreaConhecimento();
	}
	
	private function loadAreaConhecimento() {
		$this->loadAreaConhecimentoPrevidencia();
		$this->loadAreaConhecimentoSST();
		$this->loadAreaConhecimentoTrabalho()
	}
	private function loadAreaConhecimentoPrevidencia() {
		$query = "SELECT l.id 
			FROM lex l JOIN previdencia p ON p.anexo_lex LIKE CONCAT('%', l.anexo) 
			WHERE p.anexo_lex != '' AND l.anexo != ''";
		$this->buildAreaConhecimentoList($query, 'previdencia');
	}
	private function loadAreaConhecimentoSST(){
		$query = "SELECT l.id 
			FROM lex l JOIN sst s ON s.anexo_lex LIKE CONCAT('%', l.anexo) 
			WHERE s.anexo_lex != '' AND l.anexo != ''";
		$this->buildAreaConhecimentoList($query, 'sst');
	}
	private function loadAreaConhecimentoTrabalho(){
		$query = "SELECT l.id 
			FROM lex l JOIN trabalho t ON t.anexo_lex LIKE CONCAT('%', l.anexo) 
			WHERE t.anexo_lex != '' AND l.anexo != ''";
		$this->buildAreaConhecimentoList($query, 'trabalho');
	}
	private function loadAreaConhecimentoOutros(){
		$query = "SELECT l.id "
				. "FROM lex l JOIN outros o ON o.anexo_lex LIKE CONCAT('%', l.anexo) "
				. "WHERE o.anexo_lex != '' AND l.anexo != ''";
		$this->buildAreaConhecimentoList($query, 'outros');
	}
	private function buildAreaConhecimentoList($query, $area) {
		$result = mysqli_query($this->_conn, $query);
		$lista = array();
		while($row = mysqli_fetch_assoc($result)) {
			$lista[] = $row['id'];
		}

		$this->_areaconhecimento[$area] = $lista;
	}
}

new ImporterLex();
