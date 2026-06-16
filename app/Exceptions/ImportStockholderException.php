<?php

namespace App\Exceptions;

use Exception;

class ImportStockholderException extends Exception
{
    protected array $data;

    public function __construct(string $message, array $data = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
