<?php

namespace Morgo\App;

use Psr\Http\Message\ResponseInterface;

class ResponseResolver {


    public function resolve(ResponseInterface $response) {
        if (!$response->hasHeader('Content-Type')) {
            //TODO save default content type
        }

        $headers = $response->getHeaders();
        foreach ($headers as $headerName => $header) {
            header("{$headerName}:{$header}");
        }

        echo $response->getBody();
        exit;
    }
}
