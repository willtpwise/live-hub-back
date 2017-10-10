<?php
/**
 * Encrypts the passed string of text (suitable for passwords)
 *
 * @param $string : The string to encrypt
 * @return The encrypted string
 */
function encrypt_password($string) {
  return password_hash($string, PASSWORD_DEFAULT);
}
