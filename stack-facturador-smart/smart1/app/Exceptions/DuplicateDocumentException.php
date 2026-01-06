<?php
namespace App\Exceptions;

use Exception;

class DuplicateDocumentException extends Exception
{
    protected $additionalData;

    public function __construct($message = "", $code = 0, $additionalData = [], Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->additionalData = $additionalData;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}