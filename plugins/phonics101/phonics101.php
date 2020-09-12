<?php
/**
 * @package Phonics101
 *
 * @version 1.1.2
 *
 * @license GPL 3.0
 */
/*
Plugin Name: Phonics 101
Description: Creates a shortcode that will create URI to auto login to readxyz.org/phonics101
Plugin URI:  https://bopptalks.xyz/phonics101-about
Author: Carl Baker
Version: 1.1.2
Author URI: https://readxyz.com/
License: GPL v3 or later
*/

/*
    shortcode_atts( array $pairs, array $atts, string $shortcode = '' )
    param $pairs - Entire list of supported attributes and their defaults.
    param $atts  - the attributes found in the shortcode tag
    param $shortcode - the shortcode used to register the function registered by add_shortcode
*/

/**
 * Determine if the target string contains a substring
 *
 * @param string $target the string to be searched
 * @param string $substr the substring to search for
 *
 * @return bool true if found, otherwise false
 */
function p101Contains(string $target, string $substr)
{
    return false !== strpos($target, $substr);
}

function p101IsLocal()
{
    $uri = get_site_url();

    return p101Contains($uri, 'localhost') || p101Contains($uri, '.test') || p101Contains($uri, '.local');
}

function p101RedBox(string $message): string
{
    $msg = "Unable to connect. $message";

    return "<h1 style='background-color: red; width:10em; text-align:center '>$msg...</h1>";
}

/**
 * attempts to login readxyz wordpress user into phonics 101.
 *
 * @param array $atts associative array of plugin parameters. We support the keys 'login', 'method' and 'label'.
 *                    login - overrides the current logged in user.
 *                    method - 'iframe', 'new' for link to new page, 'self' for link to same frame. Default is iframe.
 *                    label - a label for the link. Default is 'Lessons'. Ignored if method is iframe.
 *
 * @return string if login successful, returns HTML for an iframe or link for readxyz.org/phonics101 for the user.
 *                If login failed, returns an empty string or the message "Unable to connect"
 */
function readxyz_create_phonics101_login($atts)
{
    if (!is_user_logged_in()) {
        return ' ';
    }
    $current_user = wp_get_current_user();
    $login = $current_user->user_login;

    $default_atts = ['login' => $login];
    // provide the attribute list, the user specified attribute and shortcode tag
    $atts = shortcode_atts($default_atts, $atts, 'phonics101');
    // the current wordpress user or the user-specified login attribute if one was provided
    $computed_login = $atts['login'];

    // set a timeout for the post call to wp_rest.php (allow a long timeout if local)
    $timeout = p101IsLocal() ? 5000 : 5;

    // build the array of arguments used in the call to wp_rest.php
    $args = [
        'body' => ['login' => $computed_login],
        'timeout' => $timeout,
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => [],
        'cookies' => []
    ];

    $wpRequestUri = p101IsLocal() ? 'http://phonics101.test/wp' : 'http://phonics.readxyz.org/wp';
    $wpLoginUri = p101IsLocal() ? 'http://phonics101.test/otp' : 'http://phonics.readxyz.org/otp';

    // make the post call
    $post_response = wp_remote_post($wpRequestUri, $args);
    $response_code = wp_remote_retrieve_response_code($post_response);
    $response = (object) ['code' => 999, 'msg' => '', 'canlogin' => false];
    if ($post_response) {
        $response_body = wp_remote_retrieve_body($post_response);
        if ($response_body) {
            $response = json_decode($response_body);
        }
    }
    $msg = (isset($response)) ? $response->msg : '';

    if (is_wp_error($post_response) || (200 != $response_code)) {
        return p101RedBox("{$post_response->get_error_code()}: {$post_response->get_error_message()}  $msg");
    }

    //$responseString = "<p>Code: {$response->code} <br>Message: {$response->msg}<br>Login: {$response->canlogin}<br></p>";

    if ((200 == $response->code) and ('YES' == $response->canlogin)) {
        $r = 42;
        try {
            $r = strval(random_int(1, 99999999));
        } catch (Exception $ex) {
        }
        $otp = $response->msg;
        $args = http_build_query([
            'otp' => $otp,
            'decache' => strval($r)
        ]);
        $uri = $wpLoginUri . '/?' . $args;
        return "<iframe id=\"phonics101_iframe\" src=\"$uri\" height=\"1200px\"></iframe>";

    } else {
        if ('YES' != $response->canlogin) {
            $msg = 'This login has no associated students. Please talk to administrator. ';
            if ('OK"' != $msg) {
                $msg .= $response->msg;
            }

            return p101RedBox($msg);
        } else {
            return p101RedBox($response->code . ' ' . $response->msg . ' ' . $response->canlogin);
        }
    }
}

add_shortcode('phonics101', 'readxyz_create_phonics101_login');


