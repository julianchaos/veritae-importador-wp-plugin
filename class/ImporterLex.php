<?php

class ImporterLex extends Importer {
	function __construct() {
		parent::__construct();

		// actions
		add_action('admin_menu', array($this,'admin_menu'), 11, 0);

		add_action('wp_ajax_veritae_importador_lex_previdencia', array($this, 'post_importLexPrevidencia'));
		add_action('wp_ajax_veritae_importador_lex_sst', array($this, 'post_importLexSST'));
		add_action('wp_ajax_veritae_importador_lex_trabalho', array($this, 'post_importLexTrabalho'));
		add_action('wp_ajax_veritae_importador_lex_outros', array($this, 'post_importLexOutros'));
	}
	
	function admin_menu() {
		// add page
		add_submenu_page('edit.php?post_type=veritae-importador', __('Importador Lex','veritae'), __('Importador Lex','veritae'), 'manage_options', 'veritae-lex', array($this,'init'));
	}
	
	function init() {
		echo <<<EOD
<script>
	jQuery(function () {
		jQuery.veritae_importador.import_lex();
	});
</script>
EOD;
	}
	
	public function post_importLexPrevidencia() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);
		
		$previdencia = $this->loadAreaConhecimentoPrevidencia($start, $interval);
		$this->importLexPost($previdencia, 'Previdência Social');
		
		die(json_encode(array('size' => count($previdencia), 'error' => $this->_error) ));
	}
	public function post_importLexSST() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);
		
		$sst = $this->loadAreaConhecimentoSST($start, $interval);
		$this->importLexPost($sst, 'Segurança e Saúde no Trabalho');
		
		die(json_encode(array('size' => count($sst), 'error' => $this->_error) ));
	}
	public function post_importLexTrabalho() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);
		
		$trabalho = $this->loadAreaConhecimentoTrabalho($start, $interval);
		$this->importLexPost($trabalho, 'Trabalho');
		
		die(json_encode(array('size' => count($trabalho), 'error' => $this->_error) ));
	}
	public function post_importLexOutros() {
		$start = filter_input(INPUT_POST, 'start', FILTER_SANITIZE_NUMBER_INT);
		$interval = filter_input(INPUT_POST, 'interval', FILTER_SANITIZE_NUMBER_INT);
		
		$outros = $this->loadAreaConhecimentoOutros($start, $interval);
		$this->importLexPost($outros, 'Outros');
		
		die(json_encode(array('size' => count($outros), 'error' => $this->_error) ));
	}
	
	private function loadAreaConhecimentoPrevidencia($start, $interval) {
		$query = "SELECT l.id, CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, 
				l.descricao, lt.tipo, p.anexo_lex as arquivo
			FROM lex l 
				JOIN previdencia p ON p.anexo_lex LIKE CONCAT('%', l.anexo) 
				LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id
			WHERE p.anexo_lex != '' AND l.anexo != '' 
			ORDER BY l.id DESC LIMIT $start, $interval";
		
		return $this->buildAreaConhecimentoList($query);
	}
	private function loadAreaConhecimentoSST($start, $interval){
		$query = "SELECT l.id, CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, 
				l.descricao, lt.tipo, p.anexo_lex as arquivo
			FROM lex l 
				JOIN sst p ON p.anexo_lex LIKE CONCAT('%', l.anexo)
				LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id
			WHERE p.anexo_lex != '' AND l.anexo != '' 
			ORDER BY l.id DESC LIMIT $start, $interval";

		return $this->buildAreaConhecimentoList($query);
	}
	private function loadAreaConhecimentoTrabalho($start, $interval){
		$query = "SELECT l.id, CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, 
				l.descricao, lt.tipo, p.anexo_lex as arquivo 
			FROM lex l 
				JOIN trabalho p ON p.anexo_lex LIKE CONCAT('%', l.anexo)
				LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id
			WHERE p.anexo_lex != '' AND l.anexo != '' 
			ORDER BY l.id DESC LIMIT $start, $interval";
		return $this->buildAreaConhecimentoList($query);
	}
	private function loadAreaConhecimentoOutros($start, $interval){
		$query = "SELECT l.id, CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, "
					. "l.descricao, lt.tipo, p.anexo_lex as arquivo "
				. "FROM lex l "
					. "JOIN outros p ON p.anexo_lex LIKE CONCAT('%', l.anexo) "
					. "LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id "
				. "WHERE p.anexo_lex != '' AND l.anexo != '' "
				. "ORDER BY l.id DESC LIMIT $start, $interval";
		return $this->buildAreaConhecimentoList($query);
	}
	private function buildAreaConhecimentoList($query) {
		$result = mysqli_query($this->_conn, $query);
		$lista = array();
		while($row = mysqli_fetch_assoc($result)) {
			$lista[] = $row;
		}
		
		return $lista;
	}
	
	private function importLexPost($lista, $area_conhecimento) {
		$lex_term_id = $this->getTerm('name', 'Lex', 'tipo_postagem');
		$area_conhecimento_term_id = $this->getTerm('name', $area_conhecimento, 'area_conhecimento');
		
		foreach($lista as $item) {
			$post = array(
				'post_date' => $this->BRDateToSystem($item['data']),
				'post_title' => utf8_encode($item['titulo']),
				'meta_input' => array(
					'tipo_postagem' => $lex_term_id, //taxonomy
					'area_conhecimento' => array($area_conhecimento_term_id), //taxonomy
					'titulo_alternativo' => utf8_encode($item['descricao']),
					'tipo_ato' => array($this->getTerm('name', $item['tipo'], 'tipo_ato')), //taxonomy
//					// As informações do ato não são definidas nas postagens antigas da Veritae. Esses valores ficaram em branco para essas postagens.
//					'numero_ato' => null, 'informacoes_ato' => null, 'ementa' => null,
					'tipo_arquivo' => 'remoto',
					'arquivo' => null, //Deve inserir o arquivo
					'arquivo_url' => "http://www.veritae.com.br/" . $this->clearArquivoURL($item['arquivo']), //Deve inserir o arquivo
					// as informações de fonte e autor do artigo não existem no Lex
//					'fonte' => null, 'data_fonte' => null, 'autor_artigo' => null
				),
				'tax_input' => array(
					'tipo_postagem' => 'Lex',
					'area_conhecimento' => array($area_conhecimento),
					'tipo_ato' => array($item['tipo']),
				)
			);
			
			$response = $this->insertPost($post);
			if(is_object($response)) {
				$this->_error[] = [
					'error' => $response->get_error_messages(),
					'post' => $item['id'],
				];
			}
		}
	}
	private function clearArquivoURL($arquivo) {
		$output = str_replace("../", '', $anexo);
		return $output;
	}
}

new ImporterLex();
