<?php

declare(strict_types = 1);
class JWTCodec {

    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function encode(array $payload): string 
    {
        // payload : our container which has information about the user
        // JWT : header = base64url(header) . paylaod = base64url(payload) . signature = hash(algorithm, header_b64url . payload_b64url, secret key)
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);
        $header = $this->base64urlEncode($header);

        $payload = json_encode([
            $payload
        ]);
        $payload = $this->base64urlEncode($payload);
        
        $signature = hash_hmac("sha256",
                                $header . "." . $payload,
                                $this->secretKey, 
                                true);
        $signature = $this->base64urlEncode($signature);

        return $header . "." . $payload . "." . $signature;
    }

    public function decode(string $token): array
    {   
        if (
            preg_match(
                '/^(?P<header>.+)\.(?P<payload>.+)\.(?P<signature>.+)$/', 
                $token, 
                $matches
            ) !== 1
        ) {
            throw new InvalidArgumentException("invalid token format");
        }

        $signature = hash_hmac("sha256",
                                $matches["header"] . "." . $matches["payload"],
                                $this->secretKey, 
                                true);

        $signatureFromToken = $this->base64urlDecode($matches["signature"]);

        if (hash_equals($signature, $signatureFromToken) === false) throw new InvalidSignatureException("signature does not match");

        $payload = json_decode($this->base64urlDecode($matches["payload"]), true)[0];

        if ($payload["exp"] < time()) {
            throw new TokenExpiredException();
        }

        return $payload;
    }

    private function base64urlEncode(string $text): string
    {
        // turns the string or json does not matters, since the base64_encode will only looks for valid bits, into base64
        // after, changes 
        // + : -
        // / : _
        // = : '' or empty  
        // Making a secure base64url result

        // + might be interpreted as a space in URL query strings.
        // / might be treated as a path separator.
        // = is used in query parameters (key=value) and can cause parsing issues.

        return str_replace(
            ["+", "/", "="],
            ["-", "_", ""],
            base64_encode($text)
        );
    }

    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ["-", "_"],
            ["+", "/"],
            $text
        ));
    }
}