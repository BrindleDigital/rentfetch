<?php
/**
 * Render the tabbed unit editor.
 *
 * @package rentfetch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get unit editor tab definitions.
 *
 * @return array<string, array<string, mixed>>
 */
function rentfetch_get_unit_editor_tabs() {
	return array(
		'overview'     => array(
			'label'    => 'Unit Information',
			'sections' => array(
				array( 'callback' => 'rentfetch_units_info_metabox_callback' ),
			),
		),
		'availability' => array(
			'label'    => 'Availability',
			'sections' => array(
				array( 'callback' => 'rentfetch_units_availability_metabox_callback' ),
			),
		),
		'images'       => array(
			'label'    => 'Images',
			'sections' => array(
				array( 'callback' => 'rentfetch_units_images_metabox_callback' ),
			),
		),
		'diagnostics'  => array(
			'label'    => 'Diagnostics',
			'lazy'     => 'diagnostics',
			'sections' => array(
				array(
					'label'    => 'API Responses',
					'callback' => 'rentfetch_units_api_response_metabox_callback',
				),
			),
		),
	);
}

/**
 * Render one section inside a unit editor tab.
 *
 * @param array<string, mixed> $section Section definition.
 * @param WP_Post              $post    Current unit.
 * @return void
 */
function rentfetch_render_unit_editor_section( $section, $post ) {
	$callback = $section['callback'] ?? '';

	if ( ! is_callable( $callback ) ) {
		return;
	}
	?>
	<section class="rf-property-editor-section">
		<?php if ( ! empty( $section['label'] ) ) : ?>
			<h3 class="rf-property-editor-section-title"><?php echo esc_html( $section['label'] ); ?></h3>
		<?php endif; ?>
		<?php call_user_func( $callback, $post ); ?>
	</section>
	<?php
}

/**
 * Render a lazy unit editor placeholder.
 *
 * @param string $fragment Fragment identifier.
 * @param int    $post_id  Current unit post ID.
 * @return void
 */
function rentfetch_render_unit_editor_lazy_fragment( $fragment, $post_id ) {
	?>
	<div class="rf-property-editor-lazy" data-rf-lazy-fragment="<?php echo esc_attr( $fragment ); ?>" data-post-id="<?php echo esc_attr( $post_id ); ?>">
		<p class="rf-property-editor-loading" role="status">Loading…</p>
	</div>
	<?php
}

/**
 * Render the standalone Unit Details editor.
 *
 * @param WP_Post $post Current unit.
 * @return void
 */
function rentfetch_units_editor_callback( $post ) {
	$tabs = rentfetch_get_unit_editor_tabs();

	wp_nonce_field( 'rentfetch_units_metabox_nonce', 'rentfetch_units_metabox_nonce' );
	rentfetch_render_unit_identity_bar( $post );
	?>
	<div class="rf-property-editor rf-unit-editor" data-rf-property-editor data-user-id="<?php echo esc_attr( get_current_user_id() ); ?>">
		<div class="rf-property-editor-tabs-wrap">
			<div class="rf-property-editor-tabs" role="tablist" aria-label="Unit details">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<button
						type="button"
						class="rf-property-editor-tab"
						id="rf-unit-editor-tab-<?php echo esc_attr( $tab_id ); ?>"
						role="tab"
						aria-controls="rf-unit-editor-panel-<?php echo esc_attr( $tab_id ); ?>"
						aria-selected="<?php echo 'overview' === $tab_id ? 'true' : 'false'; ?>"
						tabindex="<?php echo 'overview' === $tab_id ? '0' : '-1'; ?>"
						data-rf-property-tab="<?php echo esc_attr( $tab_id ); ?>"
					><?php echo esc_html( $tab['label'] ); ?></button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="rf-property-editor-panels">
			<?php foreach ( $tabs as $tab_id => $tab ) : ?>
				<div
					class="rf-property-editor-panel<?php echo 'overview' === $tab_id ? ' is-active' : ''; ?>"
					id="rf-unit-editor-panel-<?php echo esc_attr( $tab_id ); ?>"
					role="tabpanel"
					aria-labelledby="rf-unit-editor-tab-<?php echo esc_attr( $tab_id ); ?>"
					data-rf-property-panel="<?php echo esc_attr( $tab_id ); ?>"
				>
					<?php
					if ( ! empty( $tab['lazy'] ) ) {
						rentfetch_render_unit_editor_lazy_fragment( $tab['lazy'], $post->ID );
					} else {
						foreach ( $tab['sections'] as $section ) {
							rentfetch_render_unit_editor_section( $section, $post );
						}
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<script>
		(function(editor, tabIds) {
			if (!editor || !tabIds.length) {
				return;
			}

			var userId = editor.getAttribute('data-user-id') || '0';
			var storageKey = 'rentfetchEditor.activeTab.user.' + userId;
			var hashTab = window.location.hash.replace(/^#/, '');
			var storedTab = '';
			var activeTab = 'overview';

			try {
				hashTab = decodeURIComponent(hashTab);
			} catch (error) {
				hashTab = '';
			}

			try {
				storedTab = window.localStorage.getItem(storageKey) || '';
			} catch (error) {
				storedTab = '';
			}

			if (tabIds.indexOf(hashTab) !== -1) {
				activeTab = hashTab;
			} else if (tabIds.indexOf(storedTab) !== -1) {
				activeTab = storedTab;
			}

			editor.querySelectorAll('[data-rf-property-tab]').forEach(function(tab) {
				var isActive = tab.getAttribute('data-rf-property-tab') === activeTab;
				tab.classList.toggle('is-active', isActive);
				tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
				tab.setAttribute('tabindex', isActive ? '0' : '-1');
			});

			editor.querySelectorAll('[data-rf-property-panel]').forEach(function(panel) {
				var isActive = panel.getAttribute('data-rf-property-panel') === activeTab;
				panel.classList.toggle('is-active', isActive);
				panel.hidden = !isActive;
			});

			editor.classList.add('is-ready');
			editor.setAttribute('data-rf-property-editor-bootstrapped', activeTab);
		})(document.currentScript.previousElementSibling, <?php echo wp_json_encode( array_keys( $tabs ) ); ?>);
	</script>
	<?php
}
