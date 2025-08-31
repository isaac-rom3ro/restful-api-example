<?php
/**
 * Token Expired Exception
 * Thrown when a JWT token has passed its expiration time
 * Indicates the token is no longer valid and needs to be refreshed
 */

class TokenExpiredException extends Exception 
{
}