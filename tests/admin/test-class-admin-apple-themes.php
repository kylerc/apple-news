<?php

use \Apple_Exporter\Settings as Settings;

class Admin_Apple_Themes_Test extends WP_UnitTestCase {

	public function setup() {
		parent::setup();

		$this->settings = new Settings();
	}

	public function createDefaultTheme() {
		// Create default settings in the database
		$settings = new \Admin_Apple_Settings();
		$settings->save_settings( $this->settings->all() );

		// Force creation of a default theme
		$themes = new \Admin_Apple_Themes();
		$themes->setup_theme_pages();
	}

	public function createNewTheme( $name ) {
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['apple_news_theme_name'] = $name;
		$_POST['action'] = 'apple_news_create_theme';
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;

		$themes = new \Admin_Apple_Themes();
		$themes->action_router();
	}

	public function getFormattingSettings( $all_settings ) {
		// Get only formatting settings
		$formatting = new Admin_Apple_Settings_Section_Formatting( '' );
		$formatting_settings = $formatting->get_settings();

		$formatting_settings_keys = array_keys( $formatting_settings );
		$filtered_settings = array();

		foreach ( $formatting_settings_keys as $key ) {
			if ( isset( $all_settings[ $key ] ) ) {
				$filtered_settings[ $key ] = $all_settings[ $key ];
			}
		}

		return $filtered_settings;
	}

	public function testCreateTheme() {
		// Set the POST data required to create a new theme
		$name = 'Test Theme';
		$this->createNewTheme( $name );

		// Check that the data was saved properly
		$themes = new \Admin_Apple_Themes();
		$current_settings = $this->settings->all();

		// Array diff against the option value
		$diff_settings = $this->getFormattingSettings( $current_settings );
		$new_theme_settings = get_option( $themes->theme_key_from_name( $name ) );
		$this->assertEquals( $diff_settings, $new_theme_settings );
	}

	public function testCreateDefaultTheme() {
		$themes = new \Admin_Apple_Themes();

		// Create the default theme
		$this->createDefaultTheme();

		// Ensure the default theme was created
		$this->assertEquals( __( 'Default', 'apple-news' ), get_option( $themes::theme_active_key ) );
		$this->assertEquals(
			$this->getFormattingSettings( $this->settings->all() ),
			get_option( $themes->theme_key_from_name( __( 'Default', 'apple-news' ) ) )
		);
		$this->assertEquals(
			array( __( 'Default', 'apple-news' ) ),
			get_option( $themes::theme_index_key )
		);
	}

	public function testSetTheme() {
		$themes = new \Admin_Apple_Themes();

		// Create the default theme
		$this->createDefaultTheme();

		// Name a new theme
		$name = 'Test Theme';

		// Get Apple News settings and alter a setting to create a new theme
		$settings_obj = new \Admin_Apple_Settings();
		$settings = $settings_obj->fetch_settings()->all();
		$settings['layout_margin'] = 50;
		$settings_obj->save_settings( $settings );
		$this->createNewTheme( $name );

		// Simulate the form submission to set the theme
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['apple_news_theme_name'] = $name;
		$_POST['action'] = 'apple_news_set_theme';
		$_POST['apple_news_active_theme'] = $name;
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;
		$themes->action_router();

		// Check that the theme got set
		$this->assertEquals( $name, get_option( $themes::theme_active_key ) );

		$current_settings = $settings_obj->fetch_settings();
		$this->assertEquals( 50, $current_settings['layout_margin'] );
	}

	public function testDeleteTheme() {
		$themes = new \Admin_Apple_Themes();

		// Create the default theme
		$this->createDefaultTheme();

		// Name and create a new theme
		$name = 'Test Theme';
		$this->createNewTheme( $name );

		// Ensure both themes exist
		$this->assertEquals(
			array( __( 'Default', 'apple-news' ), $name ),
			get_option( $themes::theme_index_key )
		);
		$this->assertNotEmpty( get_option( $themes->theme_key_from_name( __( 'Default', 'apple-news' ) ) ) );
		$this->assertNotEmpty( get_option( $themes->theme_key_from_name( $name ) ) );

		// Delete the test theme
		$nonce = wp_create_nonce( 'apple_news_themes' );
		$_POST['apple_news_theme_name'] = $name;
		$_POST['action'] = 'apple_news_delete_theme';
		$_POST['apple_news_theme'] = $name;
		$_POST['page'] = 'apple-news-themes';
		$_REQUEST['_wp_http_referer'] = '/wp-admin/admin.php?page=apple-news-themes';
		$_REQUEST['_wpnonce'] = $nonce;
		$themes->action_router();

		// Ensure both themes exist
		$this->assertEquals(
			array( __( 'Default', 'apple-news' ) ),
			get_option( $themes::theme_index_key )
		);
		$this->assertEmpty( get_option( $themes->theme_key_from_name( $name ) ) );
	}
}
