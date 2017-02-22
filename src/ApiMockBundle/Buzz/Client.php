<?php
namespace ApiMockBundle\Buzz;

use Buzz\Client\Curl;
use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intercepor client
 */
class Client extends Curl
{
    /**
     * @var array
     */
    protected $interceptHosts = [];

    /**
     * @var HttpKernel
     */
    protected $httpKernel;

    /**
     * @param string $host
     */
    public function intercept(string $host)
    {
        $this->interceptHosts[] = $host;
    }

    /**
     * @param HttpKernelInterface $httpKernel
     */
    public function setHttpKernel(HttpKernelInterface $httpKernel)
    {
        $this->httpKernel = $httpKernel;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RequestInterface $request, MessageInterface $response, array $options = [])
    {
        if (in_array($request->getHost(), $this->interceptHosts)) {
            return $this->localSend($request, $response, $options);
        }

        return parent::send($request, $response, $options);
    }

    protected function localSend(RequestInterface $request, MessageInterface $response, array $options = array())
    {
        $localResponse = $this->httpKernel->handle(
            Request::create(
                $request->getHost().$request->getResource(),
                $request->getMethod(),
                $request->getContent()
            )
        );

        list($headers, $content) = explode("\r\n\r\n", (string) $localResponse);

        $response->setHeaders(explode("\r\n", $headers));
        $response->setContent($content);
    }
}
