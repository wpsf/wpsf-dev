<?php
/**
 * @uses WPSFramework_Settings @class
 */
$single_page   = ( $class->is('single_page') === TRUE ) ? 'yes' : 'no';
$sticky_header = ( $class->is('sticky_header') === TRUE ) ? 'wpsf-sticky-header' : FALSE;
$ajax          = ( $class->is('ajax_save') === TRUE ) ? 'yes' : 'no';
$title         = $class->_option("framework_title");
$has_nav       = ( $class->is('has_nav') === FALSE ) ? 'wpsf-show-all' : '';
?>
<div class="wpsf-framework wpsf-option-framework wpsf-theme-<?php echo $class->theme(); ?>"
     data-theme="<?php echo $class->theme(); ?>"
     data-single-page="<?php echo $single_page; ?>"
     data-stickyheader="<?php echo $sticky_header; ?>">

    <form method="post" action="options.php" enctype="multipart/form-data" class="wpsf-form">
        <?php settings_fields($class->get_unique()); ?>
        <input type="hidden" class="wpsf-reset" name="wpsf-section-id" value="<?php echo $class->active(FALSE); ?>"/>
        <input class="wpsf_parent_section_id" type="hidden" name="wpsf-parent-id"
               value="<?php echo $class->active(); ?>"/>

        <?php

        wpsf_template($class->override_location(), $class->theme() . '.php', array(
            'class'         => $class,
            'single_page'   => $single_page,
            'sticky_header' => $sticky_header,
            'ajax'          => $ajax,
            'title'         => $title,
            'has_nav'       => $has_nav,
        ));
        ?>

    </form>
    <div class="clear"></div>
</div>