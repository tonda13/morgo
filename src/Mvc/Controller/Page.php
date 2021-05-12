<?php
declare(strict_types=1);

namespace Morgo\Mvc\Controller;

use Morgo\Mvc\AbstractController;
use Psr\Http\Message\ResponseInterface;

class Page extends AbstractController
{
    public function onas($id) : ResponseInterface {
//        var_dump('X', func_get_args());

        return $this->renderJson(['Hello' => 'Pierre']);
        //return $this->renderEmptyResponse();
    }
}