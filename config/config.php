<?php
return array(
  's3' => array(
    'key' => 'AKIAJ32VOJPEJSGHB2GA',
    'secret' => '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'
  ),
  'jwt' => array(
    'key'       => '2RW/SRTbK6JVwV+WC0IQQfycXEtBwhEXl4UUEfyAHMHzYo+2WAKTAScH+5+/HKORUv0brnur57PNrNeiaihE0Q==',     // Key for signing the JWT's, I suggest generate it with base64_encode(openssl_random_pseudo_bytes(64))
    'algorithm' => 'HS512' // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
  ),
  'database' => array(
    'user'     => 'root', // Database username
    'password' => 'root', // Database password
    'host'     => 'localhost', // Database host
    'name'     => 'livehub', // Database schema name
  ),
  'serverName' => 'localhost',
);
