<?php

/**
 * @package: WordPress Replace Unlicensed and Broken Images
 * @Since: 1.0.5
 * @Date: March 2017
 * @Author: CK MacLeod
 * @Author: URI: http://ckmacleod.com
 * @License: GPL3
 */

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

/* 
 * DELETE SETTINGS DATA ON FULL UNINSTALL
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
    
    exit;

//delete options if present 

if ( get_option( 'cks_rui_options' ) != false ) {
    delete_option( 'cks_rui_options' );
    delete_option( 'cks_rui_version' );
}

