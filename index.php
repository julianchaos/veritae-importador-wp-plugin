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
		if( is_admin() ) {
			add_action('init', array($this, 'init'), 1);
			add_action('after_setup_theme', array($this, 'include_after_theme'), 1);
			add_action('plugins_loaded', array($this, 'enqueue_script'), 1);
		}
	}
	
	public function include_after_theme() {
		include_once('class/Importer.php');
		include_once('class/ImporterSimple.php');
		include_once 'class/ImporterLex.php';
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
		
		add_action('admin_menu', array($this,'admin_menu'));
	}
	function admin_menu() {
		add_menu_page(__("Importador Veritae",'veritae'), __("Importador Veritae",'veritae'), 'manage_options', 'edit.php?post_type=veritae-importador', array($this, 'import'), false, '80.025');
	}
	
	public function enqueue_script() {
		wp_register_script('veritae-importador-js', plugin_dir_url(__FILE__) . "js/veritae-importador.js", array('jquery'));
		wp_localize_script('veritae-importador-js', 'veritae_importador_src', array('ajaxurl' => admin_url( 'admin-ajax.php' )));
		wp_enqueue_script('veritae-importador-js');
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