<?php

class Storychief_WPML_Admin {
	private static $notices = array();

	/*----------- NOTICES -----------*/
	public static function admin_notice() {
		if (!empty(self::$notices)) {
			foreach (self::$notices as $notice) {
				Storychief_WPML::view('notice', $notice);
			}

			self::$notices = array();
		}
	}

	public static function notice_invalid_version() {
		self::$notices[] = array(
			'type' => 'version',
		);
	}

	public static function notice_parent_plugin_required() {
		self::$notices[] = array(
			'type' => 'parent-plugin',
		);
	}
}