<?php
/**
 * Plugin Name: WooCommerce Links to Product
 * Description: Add links to your woocommerce products (in order to consider many retailers, extend product information, etc.). 
 * Version: 1.0.0
 * Author: Ernesto Ortiz
 * Author URI:
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: woocommerce-links-to-product
 * Domain Path: /languages
 */

// load plugin text domain
function wclinks2p_init() {
    load_plugin_textdomain( 'woocommerce-links-to-product', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action('plugins_loaded', 'wclinks2p_init');



/*** scripts & styles ***/
$_page = $GLOBALS['pagenow'];
if ( $_page === 'post.php' || $_page === 'post-new.php'
    || $_page === 'admin.php')
    if (get_post_type($_GET['post'])=='product'
        || $_GET['post_type']=='product' 
        || $_GET['page']=='woocommerce-links-to-product' )
        add_action('admin_enqueue_scripts','wclinks2p_backscripts');
//enqueue in backend
function wclinks2p_backscripts() {
    wp_enqueue_style('wclinks2p_backend_css', plugins_url('/css/backend.css',__FILE__));
    //vars and translations
    $wclinks2p__vars = array(
        'admin_uri' => admin_url().'admin.php?page=woocommerce-links-to-product',
        'ajaxnonce' => json_encode(wp_create_nonce("__ajax_nonce")),
        'ajaxurl' => json_encode(admin_url("admin-ajax.php")),
        'option_empty' => __('Fields can not be empty','woocommerce-links-to-product'),
        'product_empty' => __('The Link fields can not be empty','woocommerce-links-to-product')
    );
    wp_enqueue_script('wclinks2p_backend', plugins_url('/js/backend.js',__FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('wclinks2p_backend','wclinks2p_vars',$wclinks2p__vars);
}
//enqueue in frontend
add_action('wp_enqueue_scripts', 'wclinks2p_frontscripts');
function wclinks2p_frontscripts() {
    if(is_admin()) return;
    //script for modal
    wp_enqueue_script('wclinks2p_frontend', plugins_url('/js/frontend.js',__FILE__), array('jquery'), '1.0.0', true);
    wp_enqueue_style('wclinks2p_style', plugins_url('/css/style.css',__FILE__));
}

/*** backend actions ***/

//add a custom product type
add_filter('product_type_selector','wclinks2p_add_custom_ptype' );
function wclinks2p_add_custom_ptype( $types ){
    $types[ 'wclinks2p_custom_product' ] = __( 'Links to Product','woocommerce-links-to-product' );
    return $types;
}
//add its settings under ‘General’ product sub-menu
add_action( 'woocommerce_product_options_general_product_data', 'wclinks2p_add_custom_settings' );
function wclinks2p_add_custom_settings() {
    global $woocommerce, $post;
    //get retails
    $selretails = wclinks2p_getdretails();
    //get idx_max
    $idx_max = get_post_meta($post->ID, 'idx_max',true);
    if (!$idx_max) $idx_max="00"; 
    //get options
    $options = wclinks2p_get_options();
    $f_price = $options['f_price'];
    $f_note = $options['f_note'];
    //draw links to product
    ?>
    <div class="options_group wclinks2p_group">
        <p class="wclinks2p_title"><?php echo __('Links to Product','woocommerce-links-to-product' );?></p>
        <div class="wclinks2p_replica">
        <?php
        //retail
        woocommerce_wp_select(array(
            'id'    => 'wclinks2p_retail',
            'label' => __('Choose Retailer / Anchor','woocommerce-links-to-product' ),
            'desc_tip'  => 'true',
            'description'   => __( 'You can add new retailers/anchors or delete the ones you created (in <em>settings</em> page).', 'woocommerce-links-to-product' ),
            'options'   => $selretails,
            ));
        //link
        woocommerce_wp_text_input(array(
            'id'    => 'wclinks2p_link',
            'label' => __('Link to the product','woocommerce-links-to-product'),
            'type'  => 'text',
            'placeholder' => __('(Write your link, please)','woocommerce-links-to-product')
            ));
        //price
        if ($f_price)
        woocommerce_wp_text_input(array(
            'id'    => 'wclinks2p_price',
            'label' => __('Price', 'woocommerce-links-to-product'),
            'type'  => 'text'
            ));
        //note
        if ($f_note)
        woocommerce_wp_textarea_input(array(
            'id'    => 'wclinks2p_note',
            'label' => __('A short note','woocommerce-links-to-product'),
            'type'  => 'textarea'
            ));
        ?>
        </div>
        <?php //adjust idx_max
        $clones = trim(wclinks2p_prodclones());
        if (!$clones) $idx_max = "00";
        ?>
        <div class="wclinks2p_clones"><?php echo $clones; ?></div>
        <div id="wclinks2p_alert"></div>
        <input type="hidden" id="idx_max" name="idx_max" value="<?php echo $idx_max;?>" />
        <input type="hidden" id="the_postid" name="the_postid" value="<?php echo $post->ID;?>" />
        <br /><p><button class="new_wclinks2p"><?php echo __('New LINK to product', 'woocommerce-links-to-product' );?></button><button class="retail_wclinks2p"><?php echo __('Manage retailers/anchors', 'woocommerce-links-to-product' );?></button></p>
    </div>
    <?php
}


//save meta post
add_action( 'woocommerce_process_product_meta', 'wclinks2p_save_custom_settings' );
function wclinks2p_save_custom_settings( $post_id ){
    //get values
    $wclinks2p_retail = sanitize_text_field($_POST['wclinks2p_retail']);
    $wclinks2p_link = sanitize_text_field($_POST['wclinks2p_link']);
    $wclinks2p_price = sanitize_text_field($_POST['wclinks2p_price']);
    $wclinks2p_note = wp_filter_post_kses($_POST['wclinks2p_note']);
    //update basic values
    update_post_meta($post_id,'wclinks2p_retail',$wclinks2p_retail);
    if (empty($wclinks2p_link)) $wclinks2p_link="#";
    update_post_meta($post_id,'wclinks2p_link', $wclinks2p_link);
    update_post_meta($post_id,'wclinks2p_price', $wclinks2p_price);
    update_post_meta($post_id,'wclinks2p_note', $wclinks2p_note);
    //quantity of clones
    $idx_max = sanitize_text_field($_POST['idx_max']);
    if (!$idx_max || $idx_max=='00') return;
    update_post_meta($post_id,'idx_max', $idx_max);
    //update clones
    for($i = 1; $i <= $idx_max*1; $i++){
        $j = $i;
        if ($i<10) $j = "0".$i;
        if (isset($_POST['wclinks2p_retail_'.$j])){
            $wclinks2p_retail = sanitize_text_field($_POST['wclinks2p_retail_'.$j]);
            $wclinks2p_link= esc_attr($_POST['wclinks2p_link_'.$j]);
            $wclinks2p_price = sanitize_text_field($_POST['wclinks2p_price_'.$j]);
            $wclinks2p_note = wp_filter_post_kses($_POST['wclinks2p_note_'.$j]);
            //update clone values
            update_post_meta($post_id,'wclinks2p_retail_'.$j,$wclinks2p_retail);
            if (empty($wclinks2p_link)) $wclinks2p_link="#";
            update_post_meta($post_id,'wclinks2p_link_'.$j, $wclinks2p_link);
            update_post_meta($post_id,'wclinks2p_price_'.$j, $wclinks2p_price);
            update_post_meta($post_id,'wclinks2p_note_'.$j, $wclinks2p_note);
        }
    }
}

/** AJAX FUNCTIONS **/
//if ( $_page === 'post.php' || $_page === 'post-new.php')
    //if (get_post_type($_GET['post'])=='product')
        include "ajaxes.php";

/** SHORTCODES **/
if (!is_admin()) include "shortcodes.php";

/** OPTIONS PAGE **/
if (is_admin()) include "optionspage.php";


/** other FUNCTIONS **/

//get default settings if options not saved yet
function wclinks2p_get_options(){
    $options = get_option('wclinks2p_settings');
    $defaults = array(
        'retailer_n' => 'Amazon',
        'retailer_txt' => __('Buy on Amazon','woocommerce-links-to-product'),
        'lnks_where' =>'woocommerce_after_add_to_cart_button',
        'f_price' => 0,
        'f_note' => 0,
        'b_pricebtn' => 0,
        'idx_max' => '00',
    );
    $options = wp_parse_args(get_option('wclinks2p_settings'), $defaults);
    return $options;
}

//build clones in options page
function wclinks2p_retailclones(){
    $options = wclinks2p_get_options();
    $idx_max = $options['idx_max']*1;
    $html = '';
    //recursive building
    for($i = 1; $i <= $idx_max; $i++){
        $j = $i;
        if ($i<10) $j = "0".$i;
        $retailer_n = $options['retailer_n_'.$j];
        if ($retailer_n) {
            $retailer_txt = $options['retailer_txt_'.$j];
            $html .= '<div class="wclinks2p_clone"><p><label>'.__('Name of the Retailer','woocommerce-links-to-product').': </label><input type="text" id="retailer_n_'.$j.'" name="wclinks2p_settings[retailer_n_'.$j.']" value="'.$retailer_n.'"></p><p><label>'.__('Text on the purchase button','woocommerce-links-to-product').': </label><input type="text" id="retailer_txt_'.$j.'" name="wclinks2p_settings[retailer_txt_'.$j.']" value="'.$retailer_txt.'"></p><div class="wclinks2p_killer">×</div></div>';
        }
    }
    return $html;
}

//build clones in product post
function wclinks2p_prodclones(){ 
    global $post;
    $id = $post->ID; 
    $idx_max = get_post_meta($id, 'idx_max',true);
    if (!$idx_max) $idx_max="00";
    $options = wclinks2p_get_options();
    $f_price = $options['f_price'];
    $f_note = $options['f_note'];
    $html = '';
    //recursive building
    $retails = wclinks2p_getdretails();
    for($i = 1; $i <= $idx_max*1; $i++){
        $j = $i;
        if ($i<10) $j = "0".$i;
        $retail_j = get_post_meta($id,'wclinks2p_retail_'.$j,true);
        if ($retail_j) {
            $link_j = get_post_meta($id,'wclinks2p_link_'.$j,true);
            if ($f_price) $price_j = get_post_meta($id, 'wclinks2p_price_'.$j,true);
            if ($f_note) $note_j = get_post_meta($id,'wclinks2p_note_'.$j,true); $html .= '<div class="wclinks2p_clone"><p class="form-field wclinks2p_retail_'.$j.'_field "><label for="wclinks2p_retail_'.$j.'">'.__('Choose Retailer','woocommerce-links-to-product').'</label><select id="wclinks2p_retail_'.$j.'" name="wclinks2p_retail_'.$j.'" class="select short" style="">';
            foreach ($retails as $kk => $retail) {
                $html .= '<option value="'.$kk.'"';
                if ($retail_j==$kk) $html .= ' selected="selected"';
                $html .= '>'.$retail.'</option>';
            }
            $html .= '</select></p>';
            $html .= '<p class="form-field wclinks2p_link_'.$j.'_field "><label for="wclinks2p_link_'.$j.'">'.__('Link to the product','woocommerce-links-to-product').'</label><input type="text" class="short" style="" name="wclinks2p_link_'.$j.'" id="wclinks2p_link_'.$j.'" value="'.$link_j.'" placeholder=""></p>';
            if ($f_price) $html .= '<p class="form-field wclinks2p_price_'.$j.'_field "><label for="wclinks2p_price_'.$j.'">'.__('Price','woocommerce-links-to-product').'</label><input type="text" class="short" style="" name="wclinks2p_price_'.$j.'" id="wclinks2p_price_'.$j.'" value="'.$price_j.'" placeholder=""></p>';
            if ($f_note) $html .= '<p class="form-field wclinks2p_note_'.$j.'_field "><label for="wclinks2p_note_'.$j.'">'.__('A short note','woocommerce-links-to-product').'</label><textarea class="short" style="" name="wclinks2p_note_'.$j.'" id="wclinks2p_note_'.$j.'" placeholder="" rows="2" cols="20">'.$note_j.'</textarea></p>';
            $html .= '<div class="wclinks2p_killer">×</div></div>';
        }
    }
    return $html;
}

//get array of retails
function wclinks2p_getdretails(){
    $options = wclinks2p_get_options();
    $optidx_max = $options['idx_max']*1;
    //recursive building
    $retailers = array();
    $retailers['00'] = $options['retailer_n'];
    for($i = 1; $i <= $optidx_max; $i++){
        $j = $i;
        if ($i<10) $j = "0".$i;
        $retailer_n = $options['retailer_n_'.$j];
        if ($retailer_n) 
            $retailers[$j] = $retailer_n;
    }
    return $retailers;
}
?>
