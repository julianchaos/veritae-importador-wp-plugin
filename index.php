<?php
/*
Plugin Name: Veritae Importador
Plugin URI: http://ndrade.com.br/
Description: Importador da base de dados Veritae
Version: 0.1
Author: Julian Andrade
Author URI: http://ndrade.com.br/
License: GPL
Copyright: Julian Andrade
*/

Class VeritaeImportador {
	public function __construct() {
		add_action('init', array($this, 'init'), 1);
		add_action('after_setup_theme', array($this, 'include_after_theme'), 1);
	}
	
	function include_after_theme() {
		include_once('class/Importer.php');
		include_once('class/ImporterSimple.php');
	}
	
	public function init() {
		register_post_type('veritae-importador', array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => true,
			'_builtin' =>  false,
			'capability_type' => 'page',
			'hierarchical' => true,
			'rewrite' => false,
			'query_var' => "acf",
			'supports' => array(
				'title',
			),
			'show_in_menu'	=> false,
		));
		
		// admin only
		if( is_admin() )
		{
			add_action('admin_menu', array($this,'admin_menu'));
		}
	}
	function admin_menu()
	{
		add_menu_page(__("Importador Veritae",'veritae'), __("Importador Veritae",'veritae'), 'manage_options', 'edit.php?post_type=veritae-importador', array($this, 'import'), false, '80.025');
	}
	function import(){
		$post = array(
			'post_date' => '2017-01-01',
			'post_title' => 'título',
			'post_status' => 'publish',
			'comment_status' => 'closed',
			'tax_input' => array(
				'area_conhecimento' => array('Teste', 'Saúde', 'Previdência'),	
				'tipo_ato' => 'Muamba Brasileira',
			),
			'meta_input' => array(
				'nivel_acesso' => 'assinante', //assinante|publico
				'tipo_postagem' => 'jurisprudencia',
				'titulo_alternativo' => 'título alterntivo',
				'numero_ato' => '123',
				'informações_ato' => 'informações do ato',
				'ementa' => 'ementa do ato',
				'arquivo' => null,
				'fonte' => 'Fonte do texto',
				'data_fonte' => '2017-01-05',
				'autor_artigo' => 'autor do artigo'
			)
		);
//		$error = wp_insert_post($post, true);
//		var_dump($error);
	}
}
function initVeritaeImportador() {
	global $veritaeImportador;
	
	if(!isset($veritaeImportador)) {
		$veritaeImportador = new VeritaeImportador();
	}
	return $veritaeImportador;
}
initVeritaeImportador();