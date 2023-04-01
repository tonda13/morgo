<?php
declare(strict_types=1);

namespace Morgo\App;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseResolver
 * TODO: Add meaningful comment
 */
final class ResponseResolver
{
    public function resolve(ResponseInterface $response): never {
        if (!$response->hasHeader('Content-Type')) {
            //TODO save default content type
        }

        $headers = $response->getHeaders();
        foreach ($headers as $name => $values) {
            header($name . ':' . implode(", ", $values));
        }

        echo $response->getBody();
        exit;
    }
}
