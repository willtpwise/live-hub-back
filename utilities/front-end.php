<?php
/**
 * Functionality that provides information on the front end environment
 */

/**
 * Returns the front ends home page URL, based on the environment
 *
 * For example, if the incoming request is from a localhost, then the response
 * would be 'http://localhost:8080'. If the request was from production, then
 * the response would be 'https://www.livehub.com.au'
 *
 * @return The URL, without the protocal and a trailing slash
 */
function front_end_home () {
  $request_url = parse_url($_SERVER['HTTP_REFERER']);
  return str_replace($request_url['path'], '', $_SERVER['HTTP_REFERER']);
}
