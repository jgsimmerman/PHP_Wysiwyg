<?php

namespace EspressoDev\InstagramBasicDisplay;

class InstagramBasicDisplayException extends \Exception
{
    /**
     * Invalid APU URI
     */
    const TYPE_NOT_ALLOWED  = 'APINotAllowedError';

    /**
     * Authorization error
     */
    const TYPE_OAUTH        = 'OAuthAccessTokenException';

    /**
     * Type of error
     * 
     * @var string
     */
    protected $type;

    /**
     * Constructor
     * 
     * @param string  $message Error message
     * @param integer $code Error code
     * @param string  $type Error type
     * @param Exception $previous Previous exception
     */
    public function __construct( $message = null, $code = 0, $type = null, \Exception $previous = null ) {
        $this->type = $type;
        parent::__construct( $message, $code, $previous );
    }

    /**
     * Get error type
     * 
     * @return string Get teh error type
     */
    public function getType() {
        return $this->type;
    }

}
