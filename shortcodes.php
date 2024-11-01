<?php
//shortcodes as widgets on text widget
add_filter('widget_text', 'do_shortcode');

//where the buttons
$options = wclinks2p_get_options();
$lnks_where = $options['lnks_where'];
if (empty($lnks_where)) 
    $lnks_where='woocommerce_after_add_to_cart_button'; 
if ($lnks_where!='links2product')
    add_action( $lnks_where, 'add_wclinks2p_buttons' );
add_shortcode( 'wclinks2product', 'wclinks2p_btns' );


/*** SHORTCODE ***/
function wclinks2p_btns( $atts ){
    global $post;
    //default args
    $args = shortcode_atts( array(
        'header' => '', 
        'footer' => '',
    ), $atts );
    add_wclinks2p_buttons($args['header'], $args['footer']);
}   


/*** WOOCOMMERCE action ***/
function add_wclinks2p_buttons($l_header='', $l_footer='') {
    global $post;
    $metas = get_link2p_metas($post->ID); 
    if (!$metas) return false;
    //get options
    $options = wclinks2p_get_options();
    $f_price = $options['f_price'];
    $f_note = $options['f_note'];
    $b_pricebtn = $options['b_pricebtn'];
    if (empty($l_header)) $l_header = trim($options['l_header']);
    if (empty($l_footer)) $l_footer = trim($options['l_footer']);
    //build buttons
    $wrap='<div class="links2p-wrapp">';
    $hdr='';
    if ($l_header) 
        $hdr ='<div class="links2p-hdr">'.$l_header.'</div>';
    $body = '<div class="link2p-body">';
    $btns ='';
    foreach ($metas as $k=>$meta){
        $link = $meta[1];
        $note = ''; $price = '';
        $keyprice = 2; $keynote = 3;
        if ($f_note && !$f_price) $keynote = 2;
        if ($f_price && isset($meta[$keyprice])) 
            $price=trim($meta[$keyprice]);
        if ($f_note && isset($meta[$keynote])) 
            $note=trim($meta[$keynote]);
        if ($link=="#" && empty($note)) continue;
        $btns .= '<button type="submit" name="btn_'. $k.'" value="'.urlencode($link).'" data-l2pnote="';
        if ($link=='#' && !empty($note)) $btns .= wp_filter_post_kses($note);
        $btns .='" class="button alt link2p-btn">'.$meta[0];
        if ($b_pricebtn && $f_price && $price)
            $btns .='<span class="link2p-price">'.$price.'</span>';
        $btns .= '</button>';
    } 
    $endbody = '</div>';
    $footer = '';
    if ($l_footer)
        $footer ='<div class="links2p-ftr">'.$l_footer.'</div>';
    $endwrap = '</div>';
    if ($btns)
        echo $wrap.$hdr.$body.$btns.$endbody.$footer.$endwrap;
}

/** get meta values **/
function get_link2p_metas ($id){
    //get idx_max
    $idx_max = get_post_meta($id, 'idx_max',true);
    if (!$idx_max) $idx_max="00";
    //get options
    $options = wclinks2p_get_options();
    $f_price = $options['f_price'];
    $f_note = $options['f_note'];
    //get basic values
    $metas = array();
    $_retail = get_post_meta($id, 'wclinks2p_retail',true);
    $_retail_txt = $options['retailer_txt_'.$_retail];
    if (!$_retail_txt || $_retail=="00") 
        $_retail_txt = $options['retailer_txt'];
    $_link = get_post_meta($id, 'wclinks2p_link',true);
    if (empty($_link)) return false;
    if ($f_price) 
        $_price = get_post_meta($id, 'wclinks2p_price',true);
    if ($f_note)
        $_note = get_post_meta($id, 'wclinks2p_note',true);
    $metas[0] = array($_retail_txt, $_link);
    if ($f_price) $metas[0][] = $_price;
    if ($f_note) $metas[0][] = $_note;
    if ($idx_max=="00") return $metas;
    //get clone values
    $count = 0;
    for($i = 1; $i <= $idx_max*1; $i++){
        $j = $i;
        if ($i<10) $j = "0".$i;
        $_retail = get_post_meta($id, 'wclinks2p_retail_'.$j,true);
        $_link = get_post_meta($id, 'wclinks2p_link_'.$j,true);
        if ($_retail && !empty($_link)){
            if ($_retail=='00')
                $_retail_txt = $options['retailer_txt'];
            else
                $_retail_txt =$options['retailer_txt_'.$_retail];
            $count++;
            if ($f_price) 
                $_price = get_post_meta($id,'wclinks2p_price_'.$j,true);
            if ($f_note)
                $_note = get_post_meta($id, 'wclinks2p_note_'.$j,true);
            $metas[$count] = array($_retail_txt, $_link);
            if ($f_price) $metas[$count][] = $_price;
            if ($f_note) $metas[$count][] = $_note;
        }

    }
    return $metas;
}