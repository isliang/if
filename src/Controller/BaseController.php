<?php
namespace Ipf\Controller;

use Ipf\Http\Request\RequestInterface;
use Ipf\Http\Response\ResponseInterface;

abstract class BaseController
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * BaseController constructor.
     * @param $request RequestInterface
     * @param $response ResponseInterface
     */
    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function json($data)
    {
        $data = is_string($data) ? $data : json_encode($data);
        $this->response->header('Content-type', 'application/json');
        $this->response->send($data);
    }
}