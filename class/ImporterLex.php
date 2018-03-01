<?php

class ImporterLex extends Importer {
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
		$previdencia = $this->loadAreaConhecimentoPrevidencia();
		$this->importLexPost($previdencia, 'Previdência Social');
		
		$sst = $this->loadAreaConhecimentoSST();
		$this->importLexPost($sst, 'Segurança e Saúde no Trabalho');	
	
		$trabalho = $this->loadAreaConhecimentoTrabalho();
		$this->importLexPost($trabalho, 'Trabalho');	
		
		$outros = $this->loadAreaConhecimentoOutros();
		$this->importLexPost($outros, 'Outros');	
	}
	private function loadAreaConhecimentoPrevidencia() {
		$query = "SELECT CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, 
				l.descricao, lt.tipo, p.anexo_lex as arquivo
			FROM lex l 
				JOIN previdencia p ON p.anexo_lex LIKE CONCAT('%', l.anexo) 
				LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id
			WHERE p.anexo_lex != '' AND l.anexo != ''";
		
		return $this->buildAreaConhecimentoList($query);
	}
	private function loadAreaConhecimentoSST(){
		$query = "SELECT CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, 
				l.descricao, lt.tipo, p.anexo_lex as arquivo
			FROM lex l 
				JOIN sst p ON p.anexo_lex LIKE CONCAT('%', l.anexo)
				LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id
			WHERE p.anexo_lex != '' AND l.anexo != ''";

		return $this->buildAreaConhecimentoList($query);
	}
	private function loadAreaConhecimentoTrabalho(){
		$query = "SELECT CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, 
				l.descricao, lt.tipo, p.anexo_lex as arquivo 
			FROM lex l 
				JOIN trabalho p ON p.anexo_lex LIKE CONCAT('%', l.anexo)
				LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id
			WHERE p.anexo_lex != '' AND l.anexo != ''";
		return $this->buildAreaConhecimentoList($query);
	}
	private function loadAreaConhecimentoOutros(){
		$query = "SELECT CONCAT(l.dia, '/', l.mes, '/', l.ano) as data, p.titulo, "
					. "l.descricao, lt.tipo, p.anexo_lex as arquivo "
				. "FROM lex l "
					. "JOIN outros p ON p.anexo_lex LIKE CONCAT('%', l.anexo) "
					. "LEFT JOIN lex_tipo lt ON l.tipo_lex = lt.id "
				. "WHERE p.anexo_lex != '' AND l.anexo != ''";
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
		echo "<p>";
		echo "$area_conhecimento: " . count($lista);
		echo "</p>";
		return;
		
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
			
			$this->insertPost($post);
		}
	}
	private function clearArquivoURL($arquivo) {
		$output = str_replace("../", '', $anexo);
		return $output;
	}
}

new ImporterLex();
