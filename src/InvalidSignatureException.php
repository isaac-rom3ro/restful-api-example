<?php
/**
 * Invalid Signature Exception
 * Thrown when a JWT token's signature doesn't match the expected signature
 * Indicates the token has been tampered with or uses wrong secret key
 */
class InvalidSignatureException extends Exception 
{

}