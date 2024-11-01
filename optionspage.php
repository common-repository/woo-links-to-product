<?php

/** Add plugin options page **/
add_action( 'admin_menu', 'wclinks2p_menu' );
function wclinks2p_menu() {
    //Add to submenu /what if not woocommerce?
    add_submenu_page( 'woocommerce', __('Links to Product', 'woocommerce-links-to-product'), __('Links to Product', 'woocommerce-links-to-product'), 'manage_options', 'woocommerce-links-to-product', 'wclinks2p_options');
}

/** Set options form **/
function wclinks2p_options() {
    if ( !current_user_can( 'manage_options' ) )
        wp_die( esc_html__('You do not have sufficient permissions to access this page.' ) );
    ?>
    <div id = "optionspage" class="wrap">
        <h2><?php echo esc_html__('Links to Product Plugin Options', 'woocommerce-links-to-product');?></h2>
        <br/>
        <form action="options.php" method="post">
            <?php
            settings_fields('wclinks2p_ffields');
            do_settings_sections('wclinks2p_sections');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/** Set form fields **/
add_action( 'admin_init', 'wclinks2p_setfields' );
function wclinks2p_setfields(  ) {
    register_setting( 'wclinks2p_ffields', 'wclinks2p_settings');
    /* sections */
    $section_titles = array(
        esc_html__('Retailers / Anchors', 'woocommerce-links-to-product'),
        esc_html__('Link Options', 'woocommerce-links-to-product' ),
        esc_html__('On Uninstall', 'woocommerce-links-to-product' )
        );
    //settings section
    foreach ($section_titles as $k=>$section_title)
        add_settings_section(
            'wclinks2p_section_'.($k+1),
            $section_title,
            'wclinks2p_section_callback',
            'wclinks2p_sections'
        );
    //section callback
    function wclinks2p_section_callback(  ) {
        return false;
    }

    /* fields */
    $field_names = array(
        'retailers',
        'options',
        'on_uninstall'
    );
    $field_titles = array(
        esc_html__('Retailers / Anchors', 'woocommerce-links-to-product' ),
        esc_html__('Link options', 'woocommerce-links-to-product' ),
        esc_html__('Choose what to do...', 'woocommerce-links-to-product' )
    );
    //setting fields
    foreach ($field_names as $k=>$field_name)
        add_settings_field(
            $field_name,
            $field_titles[$k],
            'wclinks2p_field_'.($k+1).'_callback',
            'wclinks2p_sections',
            'wclinks2p_section_'.($k+1)
        );
}


/** Draw fields' content **/
function wclinks2p_field_1_callback() {
    $options = wclinks2p_get_options();
    $retailer_n = $options['retailer_n'];
    $retailer_txt = $options['retailer_txt'];
    $idx_max = $options['idx_max'];
    ?>
    <div class="wclinks2p_group">
        <div class="wclinks2p_replica">
            <p>
            <label><?php echo esc_html__('Name of the Retailer or Anchor', 'woocommerce-links-to-product');?>: </label>
            <input type='text' id='retailer_n' name='wclinks2p_settings[retailer_n]' value='<?php echo $retailer_n; ?>'>
            </p>
            <p>
            <label><?php echo esc_html__('Text on the purchase button', 'woocommerce-links-to-product');?>: </label>
            <input type='text' id='retailer_txt' name='wclinks2p_settings[retailer_txt]' value='<?php echo $retailer_txt; ?>'>
            </p>
        </div>
        <div class="wclinks2p_clones"><?php echo wclinks2p_retailclones();?></div>
        <div id="wclinks2p_alert"></div>
        <input type="hidden" id="idx_max" name='wclinks2p_settings[idx_max]' value="<?php echo $idx_max;?>" />
        <br />
        <p><button class="new_wclinks2p"><?php echo __('New Retailer / Anchor', 'woocommerce-links-to-product');?></button></p>
    </div>
<?php
}

function wclinks2p_field_2_callback() {
    $options = wclinks2p_get_options();
    $f_price = $options['f_price'];
    $f_note = $options['f_note'];
    $b_pricebtn = $options['b_pricebtn'];
    if (empty($f_price)) $f_price="0";
    if (empty($f_note)) $f_note="0";
    if (empty($b_pricebtn)) $b_pricebtn="0"; 
    $l_header = $options['l_header'];
    if (!isset($l_header)) $l_header = '';
    $l_footer = $options['l_footer'];
    if (!isset($l_footer)) $l_footer = '';
    $lnks_options = array(
        esc_html__('After Add to cart button', 'woocommerce-links-to-product'),
        esc_html__('Before Add to cart button', 'woocommerce-links-to-product'),
        esc_html__('After Single Product Summary','woocommerce-links-to-product'),
        esc_html__('Only in the shortcode','woocommerce-links-to-product'). ' [links2product]',
    );
    $lnks_values = array(
        'woocommerce_after_add_to_cart_button',
        'woocommerce_before_add_to_cart_button',
        'woocommerce_after_single_product_summary',
        'links2product',
    );
    $lnks_where = $options['lnks_where'];
    if (empty($lnks_where)) 
        $lnks_where='woocommerce_after_add_to_cart_button';
    ?>

    <label><em><?php echo esc_html__('Select where Links to product would appear', 'woocommerce-links-to-product');?></em></label><br />
    <select name='wclinks2p_settings[lnks_where]'>
        <?php
        foreach ($lnks_options as $k=>$option){ 
            $value = $lnks_values[$k];?>
            <option value='<?php echo $value;?>' <?php selected( $lnks_where, $value ); ?>><?php echo $option;?></option>
        <?php
        }
        ?>
    </select>
    <br />
    <br />
    <label><em><?php echo esc_html__('A header before the links?', 'woocommerce-links-to-product');?></em></label><br />
    <textarea class="widefat" name='wclinks2p_settings[l_header]'><?php echo $l_header;?></textarea>
    <br />
    <label><em><?php echo esc_html__('A footer after the links?', 'woocommerce-links-to-product');?></em></label><br />
    <textarea class="widefat" name='wclinks2p_settings[l_footer]'><?php echo $l_footer;?></textarea>
    <br />
    <br />
    <label><em><?php echo esc_html__('Choose which fields to include', 'woocommerce-links-to-product');?></em></label><br />
    <input type='checkbox' name='wclinks2p_settings[f_price]' <?php checked( $f_price, 1 ); ?> value='1'>
    <label><em><?php echo esc_html__('The Price', 'woocommerce-links-to-product');?></em></label>
    <br />
    <input type='checkbox' name='wclinks2p_settings[f_note]' <?php checked( $f_note, 1 ); ?> value='1'>
    <label><em><?php echo esc_html__('A short Note', 'woocommerce-links-to-product');?></em></label>
    <br />
    <br />
    <label><em><?php echo esc_html__('And its behaviour...', 'woocommerce-links-to-product');?></em></label><br />
    <input type='checkbox' name='wclinks2p_settings[b_pricebtn]' <?php checked( $b_pricebtn, 1 ); ?> value='1'>
    <label><em><?php echo esc_html__('Append Price to the purchase button', 'woocommerce-links-to-product' );?></em></label>
    <br />
    
    <?php
}

function wclinks2p_field_3_callback() {
    $options = wclinks2p_get_options();
    $value = $options['on_uninstall'];
    $keep_options = array(
        esc_html__('Keep all the plugin meta data, and option settings', 'woocommerce-links-to-product' ),
        esc_html__('Keep only the option settings', 'woocommerce-links-to-product' ),
        esc_html__('Delete all, the plugin meta data and settings', 'woocommerce-links-to-product' )
    )
    ?>
    <select name='wclinks2p_settings[on_uninstall]'>
        <?php
        foreach ($keep_options as $key=>$keep_option){ ?>
            <option value='<?php echo $key;?>' <?php selected( $value, $key ); ?>><?php echo $keep_option;?></option>
        <?php
        }
        ?>
    </select>
    <p class="description"><?php echo esc_html__('Choose what to do once uninstalled this plugin', 'woocommerce-links-to-product' );?></p>
    <?php
}


/*** ON SAVING OPTIONS ***/
function update_links2psettings( $new_value, $old_value ) {
    foreach($new_value as $k=>$v) {
        if ($k=="retailer_n" && !trim($v)) 
            $new_value[$k] = 'Amazon';
        if ($k=="retailer_txt" && !trim($v)) 
            $new_value[$k] = __('Buy on Amazon','woocommerce-links-to-product');
        if (substr($k, 0, 11)=="retailer_n_" && !trim($v)){
            unset($new_value[$k]);
            $idx = substr($k, 11, 13);
            unset($new_value["retailer_n_".$idx]);
            unset($new_value["retailer_txt_".$idx]);
            $new_idx = $new_value['idx_max'];
            if ($idx == $new_idx){ //current is the last idx
                $idx = $new_idx*1 - 1;
                if ($idx<10) $idx = "0".$idx;
                $new_value['idx_max'] = $idx;
            }
        }
    }
    //fix idx_max
    $zero = true;
    foreach($new_value as $k=>$v) {
        if (substr($k, 0, 11)=="retailer_n_") $zero = false;
        if (!$zero) break;
    }
    if ($zero) $new_value['idx_max'] = '00';
    //done
    return $new_value;
}
function wclinks2p_savingoptions() {
    add_filter( 'pre_update_option_wclinks2p_settings', 'update_links2psettings', 10, 2 );
}
add_action( 'init', 'wclinks2p_savingoptions' );

