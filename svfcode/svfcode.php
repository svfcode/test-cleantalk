<?php
/**
 * @package SVFCode_test_CleanTalk
 * @version 0.1.0
 */
/*
Plugin Name: SVFCode test plugin
Plugin URI: https://github.com/svfcode/test-cleantalk
Description: Test plugin for get offer to CleanTalk
Author: SVFCode
Version: 0.1.0
Author URI: https://t.me/SergeyFrolenko
*/

require_once __DIR__ . '/class-Svfcode_Spamblock.php';

add_action('init', 'svfcode_spamblock', 11);

function svfcode_spamblock(){
    return Svfcode_Spamblock::instance();
}
