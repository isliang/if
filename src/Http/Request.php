<?php
namespace Ipf\Http;

use Ipf\Exception\MethodNotExistException;
use Ipf\Utils\TSingleton;

/**
 * Class Request
 * @package Ipf\Http
 * @method getMethod()
 * @method getUri()
 */
class Request
{
    use TSingleton;

    /**
     * @var \GuzzleHttp\Psr7\Request
     */
    private $request;

    /**
     * @var array
     */
    private $query = [];

    /**
     * @var array
     */
    private $post = [];

    private function __construct()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $k = str_replace('_', '-', substr($key, 5));
                $headers[$k] = $value;
            }
        }
        $this->request = new \GuzzleHttp\Psr7\Request(
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
            $headers,
            file_get_contents('php://input')
        );
    }

    /**
     * @param null $name
     * @return array|mixed
     * 返回get参数
     */
    public function getQuery($name = null)
    {
        if (empty($this->query) && $query = $this->request->getUri()->getQuery()) {
            parse_str($query, $this->query);
        }
        return is_null($name) ? $this->query : $this->query[$name];
    }

    public function getPost($name = null)
    {
        if (empty($this->post) && $post = (string)$this->request->getBody()) {
            switch ($this->getHeader('content-type')) {
                case 'application/x-www-form-urlencoded':
                    parse_str($post, $this->post);
                    break;
                case 'application/json':
                    $this->post = json_decode($post, true);
                    break;
            }
        }
        return is_null($name) ? $this->post : $this->post[$name];
    }

    public function getFile($name = null)
    {
        return is_null($name) ? $_FILES : $_FILES[$name];
    }

    public function getHeader($name = null)
    {
        return is_null($name) ? $this->request->getHeaders() :
            $this->request->getHeaderLine($name);
    }

    public function getIp()
    {
        $ip = $this->getHeader('client-ip') ?: $this->getHeader('x-forward-for') ?: $this->getHeader('remote-addr');
        $arr = explode(',', $ip);
        $ip = trim(end($arr));
        $ip = long2ip(ip2long($ip));
        return $ip;
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @throws MethodNotExistException
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->request, $name)) {
            return call_user_func_array([$this->request, $name], $arguments);
        }

        throw new MethodNotExistException(get_called_class(), $name);
    }
}