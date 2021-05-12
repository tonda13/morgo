<?php
declare(strict_types=1);

namespace Morgo\Mvc;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController
{
    protected ServerRequestInterface $request;

    /**
     * @return void
     */
    public function __construct(ServerRequestInterface $request) {
        $this->request = $request;
    }

    // Not sure if better using direct access to class property or getter method
    //protected function getRequest() : ServerRequestInterface {
    //    return $this->request;
    //}

    protected function render() {
        //get content from view
        ob_start();
        require_once $view_file;
        $content = ob_get_contents(); //store output buffer into variable
        ob_clean(); //clear output buffer

        //get layout and pass $content
        require_once SERVER_ROOT . $this->layout;
        $layout = ob_get_contents();
        ob_clean();

        //get template and pass $layout
        require_once SERVER_ROOT . $this->template;
        $output = ob_get_clean(); //store & clear & end buffer

        //send composed website to browser
        echo $output;
    }

    protected function renderJson(array $date) : ResponseInterface {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Foo' => 'Bar',
        ];
        return new Response(200, $headers, json_encode($date));
    }

    protected function renderEmptyResponse() : ResponseInterface {
        return new Response(200, [], '');
    }
}