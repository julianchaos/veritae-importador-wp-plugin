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
class ImporterSimple extends Importer {

	function __construct() {
		parent::__construct();

		// actions
		add_action('admin_menu', array($this,'admin_menu'), 11, 0);

		add_action('wp_ajax_veritae_importador_artigos', array($this, 'post_importArtigos'));
		add_action('wp_ajax_veritae_importador_materias', array($this, 'post_importMaterias'));
		add_action('wp_ajax_veritae_importador_noticias', array($this, 'post_importNoticias'));
	}

	function admin_menu() {
		// add page
		add_submenu_page(
				'edit.php?post_type=veritae-importador',
				__('Importador Artigos','veritae'),
				__('Importador Artigos','veritae'), 
				'manage_options', 
				'veritae-simple-artigos', 
				array($this,'runImportArtigos')
			);
		add_submenu_page(
				'edit.php?post_type=veritae-importador',
				__('Importador Matérias','veritae'),
				__('Importador Matérias','veritae'), 
				'manage_options', 
				'veritae-simple-materias', 
				array($this,'runImportMaterias')
			);
		add_submenu_page(
				'edit.php?post_type=veritae-importador',
				__('Importador Notícias','veritae'),
				__('Importador Notícias','veritae'), 
				'manage_options', 
				'veritae-simple-noticias', 
				array($this,'runImportNoticias')
			);
	}

	public function runImportArtigos() {
				echo <<<EOD
<script>
	jQuery(function () {
		jQuery.veritae_importador.import_artigos();
	});
</script>
EOD;
	}
	public function post_importArtigos() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);
		
		$artigos = $this->loadArtigos($start, $interval);
		$this->importSimplePost($artigos, 'artigo');

		die(json_encode(array('size' => count($artigos), 'error' => $this->_error) ));
	}

	public function runImportMaterias() {
				echo <<<EOD
<script>
	jQuery(function () {
		jQuery.veritae_importador.import_materias();
	});
</script>
EOD;
	}
	public function post_importMaterias() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);
		
		$materias = $this->loadMaterias($start, $interval);
		$this->importSimplePost($materias, 'materia');
		
		die(json_encode(array('size' => count($materias), 'error' => $this->_error) ));
	}
	
	public function runImportNoticias() {
				echo <<<EOD
<script>
	jQuery(function () {
		jQuery.veritae_importador.import_noticias();
	});
</script>
EOD;
	}
	public function post_importNoticias() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);

		$noticias = $this->loadNoticias($start, $interval);
		$this->importSimplePost($noticias, 'noticia');
		
		die(json_encode(array('size' => count($noticias), 'error' => $this->_error) ));
	}
	
	private function loadArtigos($start, $interval) {
		$query = "SELECT * FROM artigos "
				. "ORDER BY id DESC LIMIT $start, $interval";
		return $this->buildPostList($query);
	}
	private function loadMaterias($start, $interval){
		$query = "SELECT * FROM materias "
				. "ORDER BY id DESC LIMIT $start, $interval";
		return $this->buildPostList($query);
	}
	private function loadNoticias($start, $interval) {
		$query = "SELECT * FROM noticias "
				. "ORDER BY id DESC LIMIT $start, $interval";
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
					'tipo_postagem' => $this->getTerm('slug', $tipo, 'tipo_postagem'), //taxonomy
					'area_conhecimento' => array(), //taxonomy
					'titulo_alternativo' => utf8_encode($item['titulo']),
//					'tipo_ato' => array(), //taxonomy
					'numero_ato' => null,
					'informacoes_ato' => null,
					'ementa' => null,
					'tipo_arquivo' => 'remoto',
					'arquivo' => null, //Deve inserir o arquivo
					'arquivo_url' => null, //Deve inserir o arquivo
					'fonte' => null,
					'data_fonte' => null,
					'autor_artigo' => null
				),
				'tax_input' => array(
					'tipo_postagem' => $tipo,
					'area_conhecimento' => array(),
//					'tipo_ato' => array(),
				)
			);
			
			switch($tipo) {
				case 'artigo':
					$post['meta_input']['arquivo_url'] = "http://www.veritae.com.br/artigos/arquivos/{$item['anexo']}";
					$post['meta_input']['autor_artigo'] = $item['autor'];
					break;
				case 'materia': 
					$post['meta_input']['arquivo_url'] = "http://www.veritae.com.br/artigos/arquivos/" . $this->clearMateriaAnexo($item['anexo']);
					break;
				case 'noticia':
					$post['meta_input']['area_conhecimento'][] = $this->getTerm('name', $item['area'], 'area_conhecimento');
					$post['tax_input']['area_conhecimento'][] = $item['area'];
					$post['meta_input']['arquivo_url'] = "http://www.veritae.com.br/noticias/arquivos/{$item['anexo']}";
					break;
			}
			
			$response = $this->insertPost($post);
			if(is_object($response)) {
				$this->_error[] = [
					'error' => $response->get_error_messages(),
					'post' => $item['id'],
				];
			}
		}
	}
	private function clearMateriaAnexo($anexo) {
		$output = str_replace("../materias/arquivos/", '', $anexo);
		return $output;
	}

}
new ImporterSimple();
