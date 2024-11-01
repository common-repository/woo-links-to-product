<?php
// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

$options = get_option( 'wclinks2p_settings' );
$keep_option = $options['on_uninstall'];
/** keep options **/
/*
    0:  Keep all the plugin meta data, and option settings
    1:  Keep only the option settings
    2:  Delete all, the plugin meta data and settings
*/

if ($keep_option == '0') return;

global $wpdb;

/* Delete post metas */
$wpdb->query("
        DELETE FROM {$wpdb->postmeta} WHERE 
        meta_key LIKE '%wclinks2p_%' OR meta_key='idx_max'
    ");

if ($keep_option != '2') return;
/* Delete option settings */
delete_option( 'wclinks2p_settings' );
?>