<?php

class Storychief_WPML {

	private static $initiated = false;

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		add_action('storychief_before_publish_action', array('Storychief_WPML', 'setLocale'), 1);
		add_action('storychief_after_publish_action', array('Storychief_WPML', 'linkTranslations'), 1);

		add_filter('storychief_save_categories_filter', array('Storychief_WPML', 'saveCategories'), 1);
		add_filter('storychief_save_tags_filter', array('Storychief_WPML', 'saveTags'), 1);
	}

	public static function setLocale($payload) {
		global $sitepress;
		$language = isset($payload['language']) ? $payload['language'] : $sitepress->get_default_language();
		$sitepress->switch_lang($language);
	}

	public static function linkTranslations($payload) {
		global $sitepress;
		$post_ID = $payload['external_id'];
		$post_language = $payload['language'];
		$src_ID = isset($payload['source']['data']['external_id']) ? $payload['source']['data']['external_id'] : null;

		// Translate Post
		if ($src_ID && $post_language && $sitepress) {
			$src_trid = $sitepress->get_element_trid($src_ID);
			$sitepress->set_element_language_details($post_ID, 'post_post', $src_trid, $post_language);
		}
	}

	public static function saveCategories($story) {
		global $sitepress;
		if (isset($story['categories']['data'])) {
			$categories = array();
			foreach ($story['categories']['data'] as $category) {
				if (!$cat_ID = self::findTermLocalized($category['name'], $sitepress->get_current_language(), 'category')) {
					// try to find the category ID for cat with name X in language Y
					// if it does not exist. create that sucker
					if (!function_exists('wp_insert_category')) require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
					$cat_ID = wp_insert_category(array(
						'cat_name'          => $category['name'],
						'category_nicename' => $category['name'] . ' ' . $sitepress->get_current_language(),
					));
				}

				$categories[] = $cat_ID;
			}

			wp_set_post_terms($story['external_id'], $categories, 'category', $append = false);

			// make sure we don't retry this in another filter...
			unset($story['categories']['data']);
		}

		return $story;
	}

	public static function saveTags($story) {
		global $sitepress;

		if (isset($story['tags']['data'])) {
			$tags = array();
			foreach ($story['tags']['data'] as $tag) {
				if (!$tag_ID = self::findTermLocalized($tag['name'], $sitepress->get_current_language(), 'post_tag')) {
					// try to find the tag ID for tag with name X in language Y
					// if it does not exist. create that sucker

					if (!function_exists('wp_insert_term')) require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');
					$tag = wp_insert_term($tag['name'], 'post_tag', array(
						'slug' => $tag['name'] . ' ' . $sitepress->get_current_language(),
					));
					$tag_ID = isset($tag['term_id']) ? $tag['term_id'] : null;
				}
				$tags[] = $tag_ID;
			}

			wp_set_post_terms($story['external_id'], $tags, 'post_tag', $append = false);

			// make sure we don't retry this in another filter...
			unset($story['tags']['data']);
		}

		return $story;
	}

	public static function view($name, array $args = array()) {
		$args = apply_filters('storychief_view_arguments', $args, $name);
		foreach ($args AS $key => $val) {
			$$key = $val;
		}

		load_plugin_textdomain('storychief-wpml');
		$file = STORYCHIEF_WPML__PLUGIN_DIR . 'views/' . $name . '.php';
		include($file);
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if (version_compare($GLOBALS['wp_version'], STORYCHIEF_WPML__MINIMUM_WP_VERSION, '<')) {
			Storychief_WPML_Admin::notice_invalid_version();
			Storychief_WPML_Admin::admin_notice();
			Storychief_WPML::bail_on_activation();
		} elseif (!class_exists('Storychief') || !function_exists('icl_object_id')) {
			Storychief_WPML_Admin::notice_parent_plugin_required();
			Storychief_WPML_Admin::admin_notice();
			Storychief_WPML::bail_on_activation();
		}
	}

	/**
	 * Removes all connection options
	 * @static
	 */
	public static function plugin_deactivation() {
	}

	/**
	 * @param bool $deactivate
	 */
	private static function bail_on_activation($deactivate = true) {
		if ($deactivate) {
			$plugins = get_option('active_plugins');
			$storychief = plugin_basename(STORYCHIEF_WPML__PLUGIN_DIR . 'storychief-wpml.php');
			$update = false;
			foreach ($plugins as $i => $plugin) {
				if ($plugin === $storychief) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ($update) {
				update_option('active_plugins', array_filter($plugins));
			}
		}
		exit;
	}

	private static function findTermLocalized($name, $lang, $taxonomy) {
		$args = array(
			'get'                    => 'all',
			'name'                   => $name,
			'number'                 => 0,
			'taxonomy'               => $taxonomy,
			'update_term_meta_cache' => false,
			'orderby'                => 'none',
			'suppress_filter'        => true,
			'lang'                   => $lang,
		);
		$terms = get_terms($args);
		if (is_wp_error($terms) || empty($terms)) {
			return false;
		}
		$term = array_shift($terms);

		return $term->term_id;
	}
}