<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ImporterSimple
 *
 * @author julianchaos
 */
class ImporterSimple extends Importer{

	function __construct() {
		parent::__construct();

		// actions
		add_action('admin_menu', array($this,'admin_menu'), 11, 0);
	}

	function admin_menu() {
		// add page
		add_submenu_page('edit.php?post_type=veritae-importador', __('Importador Simples','veritae'), __('Importador Simples','veritae'), 'manage_options', 'veritae-simple', array($this,'init'));

	}

	function init() {
		echo 'init importer simple';

		$this->runImportArtigos();
		$this->runImportMaterias();
		$this->runImportNoticias();
	}

	private function runImportArtigos() {
		$artigos = $this->loadArtigos();
		$this->importSimplePost($artigos, 'artigo');
	}
	private function runImportMaterias() {
		$materias = $this->loadMaterias();
		$this->importSimplePost($materias, 'materia');
	}
	private function runImportNoticias() {
		$noticias = $this->loadNoticias();
		$this->importSimplePost($noticias, 'noticia');
	}
	
	private function loadArtigos() {
		$query = "SELECT * FROM artigos";
		return $this->buildPostList($query);
	}
	private function loadMaterias(){
		$query = "SELECT * FROM materias";
		return $this->buildPostList($query);
	}
	private function loadNoticias() {
		$query = "SELECT * FROM noticias";
		return $this->buildPostList($query);
	}
	private function buildPostList($query) {
		$result = mysqli_query($this->_conn, $query);
		$lista = array();
		while($row = mysqli_fetch_assoc($result)) {
			$lista[] = $row;
		}
		return $lista;
	}
	
	private function importSimplePost($lista, $tipo) {
		foreach($lista as $item) {
			$post = array(
				'post_date' => $this->BRDateToSystem($item['data']),
				'post_title' => utf8_encode($item['titulo']),
				'meta_input' => array(
					'nivel_acesso' => $item['acesso'], //Falta definir semelhança entre base antiga e nova
					'tipo_postagem' => $tipo,
					'titulo_alternativo' => utf8_encode($item['titulo']),
					'arquivo' => null, //Deve inserir o arquivo
				)
			);
			
			switch($tipo) {
				case 'artigo':
					$post['autor_artigo'] = $item['autor'];
					break;
				case 'noticia':
					$post['tax_input'] = array($item['area']);
					break;
			}
			$this->insertPost($post);
		}
	}

}
new ImporterSimple();
