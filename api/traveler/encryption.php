<?php
function encrypt($data, $key) {
    $key = hash('sha256', $key, true); 

    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')); 
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv); 
    
    return base64_encode($iv . $encrypted);
}

function decrypt($data, $key) {
    $key = hash('sha256', $key, true);
    
    $data = base64_decode($data); 
    
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $iv_length); 
    $encrypted = substr($data, $iv_length); 
    
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv); 
}
?>
