<div class="wpsf-framework wpsf-option-framework wpsf-theme-<?php echo $theme; ?>" data-theme="<?php echo $theme; ?>" data-single-page="<?php echo $is_single_page; ?>">
	<form method="post" action="options.php" enctype="multipart/form-data"
		class="wpsf-form">

		<input type="hidden" class="wpsf-reset" name="wpsf_section_id"
			value="<?php echo $current_section_id; ?>" />
        <?php echo $settings_fields; ?>