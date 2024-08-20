<?php
	/**
	 * basic config external file ajax
	 */
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-config.php' );
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php' );
	//$wpdb = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST);
	if ( !( defined('BLOCK_LOAD') && BLOCK_LOAD ) ) require_once(ABSPATH . 'wp-settings.php');

	/**
	 * Load library functions
	 */
	include AI_MESSAGE_CLIENT_PATH.'/libs/function.php';