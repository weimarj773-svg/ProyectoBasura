<?php
function jwt_encode($payload, $secret, $alg = 'HS256') {
    $header = ['typ' => 'JWT', 'alg' => $alg];
    $segments = [];
    $segments[] = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $segments[] = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signing_input = implode('.', $segments);
    $signature = hash_hmac('sha256', $signing_input, $secret, true);
    $segments[] = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    return implode('.', $segments);
}

function jwt_decode($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return null;
    list($headb64, $payloadb64, $sigb64) = $parts;
    $payload = json_decode(base64_decode(strtr($payloadb64, '-_', '+/')), true);
    $sig = base64_decode(strtr($sigb64, '-_', '+/'));
    $valid = hash_hmac('sha256', "$headb64.$payloadb64", $secret, true);
    if (!hash_equals($valid, $sig)) return null;
    return $payload;
}
