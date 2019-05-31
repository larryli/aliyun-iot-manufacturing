<?php

namespace app\aliyun;

use AlibabaCloud\Client\Exception\AlibabaCloudException;
use Throwable;

class Exception extends AlibabaCloudException
{
    /**
     * @param string $errorMessage
     * @param string $errorCode
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($errorMessage, $errorCode, $message = '', $code = 0, Throwable $previous = null)
    {
        $this->errorMessage = $errorMessage;
        $this->errorCode = $errorCode;
        parent::__construct($message, $code, $previous);
    }
}
