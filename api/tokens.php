<?php

// In order to use JWT
// First we have to get the informations 
// key -> value
// using sub instead of id is a sub
// Claim Standard
// sub -> id
// exp -> expire time
$payload = [
    "sub" => $user["id"],
    "username" => $user["username"],
    "exp" => time() + 20
];

// In order to save credentials with security layer 
// We used to have base64_encode in a json associative array containing the informations needed
// Since it's not secure, stick with JWT 
// $access_token = base64_encode(json_encode($payload));

$access_token = $codec->encode($payload);

$refresh_token_expiry = time() + 432000;

$refresh_token = $codec->encode([
    "sub" => $user["id"],
    "exp" => $refresh_token_expiry
]);


echo json_encode([
    "access_token" => $access_token,
    "refresh_token" => $refresh_token 
]); 