<?php

namespace App\Http\Response;


class ApiResponse extends Response
{
    public function json()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'data' => $this->getData(),
            'count' => $this->getCount(),
        ], $this->http_code);
    }
}
