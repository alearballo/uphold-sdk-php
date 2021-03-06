<?php

namespace Uphold\HttpClient;

use Uphold\HttpClient\Handler\ErrorHandler;
use Uphold\HttpClient\Message\MessageFactory;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Http client used to perform requests on Uphold API.
 */
class HttpClient implements HttpClientInterface
{
    /**
     * Uphold client.
     *
     * @var UpholdClient
     */
    protected $client;

    /**
     * Error handler.
     *
     * @var ErrorHandler
     */
    protected $errorHandler;

    /**
     * @var $options
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->options,
            array('message_factory' => new MessageFactory()),
            $options
        );

        $this->client = new GuzzleClient($this->options);
        $this->errorHandler = new ErrorHandler($this->options);
    }

    /**
     * {@inheritDoc}
     */
    public function getOption($name)
    {
        return $this->options[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function get($path, array $parameters = array(), array $headers = array())
    {
        return $this->request($path, null, 'GET', $headers, array('query' => $parameters));
    }

    /**
     * {@inheritDoc}
     */
    public function post($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'POST', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function patch($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'PATCH', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($path, $body = null, array $headers = array())
    {
        return $this->request($path, $body, 'DELETE', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function put($path, $body, array $headers = array())
    {
        return $this->request($path, $body, 'PUT', $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function request($path, $body, $httpMethod = 'GET', array $headers = array(), array $options = array())
    {
        if (!empty($this->options['debug'])) {
            $options['debug'] = $this->options['debug'];
        }

        if (count($headers) > 0) {
            $options['headers'] = $headers;
        }

        $options['body'] = $body;

        $request = $this->client->createRequest($httpMethod, $path, $options);

        try {
            $response = $this->client->send($request);
        } catch(\Exception $e) {
            $this->errorHandler->onException($e);
        }

        return $response;
    }
}
