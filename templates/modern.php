<header class="wpsf-header <?php echo $sticky_header; ?>">
	<?php if ( ! empty( $title ) ) : ?>
		<h1><?php echo $title; ?></h1>
	<?php endif; ?>

	<fieldset>
		<?php
		if ( $ajax === 'yes' ) {
			echo '<span id="wpsf-save-ajax">' . esc_html__( "Settings Saved", 'wpsf-framework' ) . '</span>';
		}

		echo $class->get_settings_buttons();
		?>
	</fieldset>
	<?php
	if ( $class->is( "has_nav" ) === true ) {
		echo '<a href="#" class="wpsf-expand-all"><i class="fa fa-eye-slash"></i> ' . __( "Show All Options" ) . '</a>';
	}
	echo '<div class="clear"></div>';
	?>

</header>

<div class="wpsf-body <?php echo $has_nav; ?>">
	<div class="wpsf-nav">
		<ul> <?php wpsf_modern_navs( $class->navs(), $class ); ?> </ul>
	</div>


	<div class="wpsf-content">
		<div class="wpsf-sections">
			<?php
			foreach ( $class->options as $option ) {
				if ( $single_page === 'no' && $option['name'] !== $class->active() ) {
					continue;
				}

				$pg_active = ( $option['name'] === $class->active() ) ? true : false;

				if ( isset( $option['sections'] ) ) {
					foreach ( $option['sections'] as $section ) {
						if ( $single_page === 'no' && $section['name'] !== $class->active( false ) ) {
							continue;
						}

						$sc_active = ( $pg_active === true && $section['name'] === $class->active( false ) ) ? true : false;
						$fields    = $class->render_fields( $section );

						echo '<div ' . $class->is( 'page_active', $sc_active ) . ' 
                        id="wpsf-tab-' . $option['name'] . '-' . $section['name'] . '" 
                        class="wpsf-section">' . $class->get_title( $section ) . $fields . '</div>';
					}
				} elseif ( isset( $option['fields'] ) || isset( $option['callback_hook'] ) ) {
					$fields = $class->render_fields( $option );
					echo '<div ' . $class->is( 'page_active', $pg_active ) . ' 
                        id="wpsf-tab-' . $option['name'] . '" 
                        class="wpsf-section">' . $class->get_title( $option ) . $fields . '</div>';
				}
			}
			?>
		</div>
		<div class="clear"></div>
	</div>
	<div class="wpsf-nav-background"></div>
</div>

<footer class="wpsf-footer">
	<div class="wpsf-block-left"><?php _e( "Powered by WordPress Settings Framework (WPSF)" ) ?></div>
	<div class="wpsf-block-right"><?php _e( "Version" );
		echo ' ' . WPSF_VERSION; ?></div>
	<div class="clear"></div>
</footer>