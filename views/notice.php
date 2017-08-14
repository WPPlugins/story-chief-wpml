<?php if (isset($type)): ?>
	<?php if ($type == 'parent-plugin') : ?>
		<div id="storychief-warning" class="warning notice">
			<p>
				<strong><?php printf(esc_html__('Storychief WPML %s requires Storychief 0.3.0 or higher and the WPML plugin.', 'storychief-wpml'), STORYCHIEF_WPML_VERSION); ?></strong>
				<?php printf(__('Please <a href="%1$s">install Storychief</a> and/or <a href="%1$s">WPML</a>.', 'storychief-wpml'), 'https://wordpress.org/plugins/story-chief/', 'http://wpml.org/'); ?>
			</p>
		</div>
	<?php elseif ($type == 'version') : ?>
		<div id="storychief-warning" class="warning notice">
			<p>
				<strong><?php printf(esc_html__('Storychief WPML %s requires WordPress 4.6 or higher.', 'storychief-wpml'), STORYCHIEF_WPML_VERSION); ?></strong>
				<?php printf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'storychief-wpml'), 'http://codex.wordpress.org/Upgrading_WordPress'); ?>
			</p>
		</div>
	<?php endif; ?>
<?php endif; ?>