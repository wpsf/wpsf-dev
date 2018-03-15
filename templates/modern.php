<?php
/**
 * Renders Modern Theme Menu
 *
 * @param      $navs
 * @param      $class
 * @param null $parent
 */
function wpsf_modern_navs($navs, $class, $parent = NULL) {
    $parent = ( $parent === NULL ) ? '' : 'data-parent-section="' . $parent . '"';
    foreach( $navs as $i => $nav ):
        $title = ( isset($nav['title']) ) ? $nav['title'] : "";
        $href  = ( isset($nav['href']) && $nav['href'] !== FALSE ) ? $nav['href'] : '#';
        if( ! empty($nav['submenus']) ) {
            $is_active    = ( isset($nav['is_active']) && $nav['is_active'] === TRUE ) ? ' style="display: block;"' : '';
            $is_active_li = ( isset($nav['is_active']) && $nav['is_active'] === TRUE ) ? ' wpsf-tab-active ' : '';
            echo '<li class="wpsf-sub ' . $is_active_li . '">';
            echo '<a href="#" class="wpsf-arrow">' . $class->icon($nav) . ' ' . $title . '</a>';
            echo '<ul ' . $is_active . '>';
            wpsf_modern_navs($nav['submenus'], $class, $nav['name']);
            echo '</ul>';
            echo '</li>';
        } else {
            if( isset($nav['is_separator']) && $nav['is_separator'] === TRUE ) {
                echo '<li><div class="wpsf-seperator">' . $class->icon($nav) . ' ' . $title . '</div></li>';
            } else {
                $is_active = ( isset($nav['is_active']) && $nav['is_active'] === TRUE ) ? "class='wpsf-section-active'" : '';
                echo '<li>';
                echo '<a ' . $is_active . ' href="' . $href . '" ' . $parent . ' data-section="' . $nav['name'] . '">' . $class->icon($nav) . ' ' . $title . '</a>';
                echo '</li>';
            }
        }

    endforeach;
}

?>
<header class="wpsf-header <?php echo $sticky_header; ?>">
    <?php if( ! empty($title) ) : ?>
        <h1><?php echo $title; ?></h1>
    <?php endif; ?>

    <fieldset>
        <?php
        if( $ajax === 'yes' ) {
            echo '<span id="wpsf-save-ajax">' . esc_html__("Settings Saved", 'wpsf-framework') . '</span>';
        }

        echo $class->get_settings_buttons();
        ?>
    </fieldset>
    <?php
    if( $class->is("has_nav") === TRUE ) {
        echo '<a href="#" class="wpsf-expand-all"><i class="fa fa-eye-slash"></i> ' . __("Show All Options") . '</a>';
    }
    echo '<div class="clear"></div>';
    ?>

</header>

<div class="wpsf-body <?php echo $has_nav; ?>">
    <div class="wpsf-nav">
        <ul> <?php wpsf_modern_navs($class->navs(), $class); ?> </ul>
    </div>


    <div class="wpsf-content">
        <div class="wpsf-sections">
            <?php
            foreach( $class->options as $option ) {
                if( $single_page === 'no' && $option['name'] !== $class->active() ) {
                    continue;
                }

                $pg_active = ( $option['name'] === $class->active() ) ? TRUE : FALSE;

                if( isset($option['sections']) ) {
                    foreach( $option['sections'] as $section ) {
                        if( $single_page === 'no' && $section['name'] !== $class->active(FALSE) ) {
                            continue;
                        }

                        $sc_active = ( $pg_active === TRUE && $section['name'] === $class->active(FALSE) ) ? TRUE : FALSE;
                        $fields    = $class->render_fields($section);

                        echo '<div ' . $class->is('page_active', $sc_active) . ' 
                        id="wpsf-tab-' . $option['name'] . '-' . $section['name'] . '" 
                        class="wpsf-section">' . $class->get_title($section) . $fields . '</div>';
                    }
                } else if( isset($option['fields']) || isset($option['callback_hook']) ) {
                    $fields = $class->render_fields($option);
                    echo '<div ' . $class->is('page_active', $pg_active) . ' 
                        id="wpsf-tab-' . $option['name'] . '" 
                        class="wpsf-section">' . $class->get_title($option) . $fields . '</div>';
                }
            }
            ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="wpsf-nav-background"></div>
</div>

<footer class="wpsf-footer">
    <div class="wpsf-block-left"><?php _e("Powered by WordPress Settings Framework (WPSF)") ?></div>
    <div class="wpsf-block-right"><?php _e("Version");
        echo ' ' . WPSF_VERSION; ?></div>
    <div class="clear"></div>
</footer>