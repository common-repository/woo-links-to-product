<?php
/* ***************************** */
/*     HANDLE AJAX requests      */
/* ***************************** */

/*** AJAX for visitors and users ***/
add_action( 'wp_ajax_wclinks2p_reqs', 'wclinks2p_reqs_callback' );
add_action( 'wp_ajax_nopriv_wclinks2p_reqs', 'wclinks2p_reqs_callback' );

/*** HANDLER function ***/
function wclinks2p_reqs_callback() {
    //security
    check_ajax_referer( '__ajax_nonce', 'security' );
    //switch TODOs
    $suffix = sanitize_text_field($_POST['del_suffix']);
    if ($suffix*1 < 10) $suffix = '0'.$suffix;
    switch ($_POST['todo']) {
        case 'delmetas':
            $id = intval($_POST['postid']);
            if (!$id) break;
            $metas = array ('wclinks2p_retail_','wclinks2p_link_','wclinks2p_price_', 'wclinks2p_note_');
            foreach ($metas as $meta) 
                delete_post_meta($id, $meta.$suffix);
        break;
        case 'deloption':
            $options = wclinks2p_get_options();
            unset($options['retailer_n_'.$suffix]);
            unset($options['retailer_txt_'.$suffix]);
            update_option('wclinks2p_settings', $options);
        break;
    }
    //return values
    //nothing to echo
    wp_die();
}
