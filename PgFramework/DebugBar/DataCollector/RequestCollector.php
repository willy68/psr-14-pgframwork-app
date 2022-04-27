<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\DataCollector;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use PgFramework\ApplicationInterface;
use DebugBar\DataCollector\Renderable;
use Psr\Http\Message\ResponseInterface;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 * Based on \Symfony\Component\HttpKernel\DataCollector\RequestDataCollector by Fabien Potencier <fabien@symfony.com>
 * Based on https://github.com/barryvdh/laravel-debugbar/blob/master/src/DataCollector/RequestCollector.php
 *
 */
class RequestCollector extends DataCollector implements Renderable, AssetProvider
{
    /** @var ServerRequestInterface $request */
    protected $request;

    /** @var  ResponseInterface $response */
    protected $response;

    /** @var  SessionInterface $session */
    protected $session;

    // The HTML var dumper requires debug bar users to support the new inline assets, which not all
    // may support yet - so return false by default for now.
    protected $useHtmlVarDumper = false;

    /**
     * Create a new SymfonyRequestCollector
     *
     */
    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        SessionInterface $session = null
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'request';
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->isHtmlVarDumperUsed() ? $this->getVarDumper()->getAssets() : array();
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        $widget = $this->isHtmlVarDumperUsed()
            ? "PhpDebugBar.Widgets.HtmlVariableListWidget"
            : "PhpDebugBar.Widgets.VariableListWidget";
        return [
            "request" => [
                "icon" => "tags",
                "widget" => $widget,
                "map" => "request.data",
                "default" => "{}"
            ],
            'request:badge' => [
                'map' => 'request.status_code_raw',
                'default' => 0,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $request = $this->request;
        $response = $this->response;

        $responseHeaders = [];
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $responseHeaders[$name] = $value;
            }
        }

        $statusCode = $response->getStatusCode();

        $data['data'] = [
            'path_info' => $request->getServerParams()['PATH_INFO'] ?? '',
            'status_code' => $statusCode,
            'status_text' => ! empty($response->getReasonPhrase()) ? $response->getReasonPhrase() : '',
            'content_type' => $response->hasHeader('Content-Type') ? $response->getHeader('Content-Type') : 'text/html',
            'request_query' => $request->getQueryParams(),
            'request_headers' => $request->getHeaders(),
            'request_server' => $request->getServerParams(),
            'request_cookies' => $request->getCookieParams(),
            'response_headers' => $responseHeaders,
        ];

        if ($this->session) {
            $sessionAttributes = [];
            foreach ($this->session->toArray() as $key => $value) {
                $sessionAttributes[$key] = $value;
            }
            $data['data']['session_attributes'] = $sessionAttributes;
        }

        $request_attributes = $request->getAttributes();
        if (isset($request_attributes[ApplicationInterface::class])) {
            unset($request_attributes[ApplicationInterface::class]);
        }
        $data['data']['request_attributes'] = $request_attributes;

        foreach ($data['data']['request_server'] as $key => $value) {
            if (
                stripos($key, '_KEY') || stripos($key, '_PASSWORD')
                || stripos($key, '_SECRET') || stripos($key, '_PW')
                || stripos($key, '_TOKEN') || stripos($key, '_PASS')
            ) {
                $data['data']['request_server'][$key] = '******';
            }
        }

        foreach ($data['data'] as $key => $var) {
            if ($this->isHtmlVarDumperUsed()) {
                $data['data'][$key] = $this->getVarDumper()->renderVar($var);
            } else {
                $data['data'][$key] = $this->getDataFormatter()->formatVar($var);
            }
            $data['status_code_raw'] = $statusCode;
        }

        return $data;
    }

    private function getCookieHeader($name, $value, $expires, $path, $domain, $secure, $httponly)
    {
        $cookie = sprintf('%s=%s', $name, urlencode($value));

        if (0 !== $expires) {
            if (is_numeric($expires)) {
                $expires = (int) $expires;
            } elseif ($expires instanceof DateTime) {
                $expires = $expires->getTimestamp();
            } else {
                $expires = strtotime($expires);
                if (false === $expires || -1 == $expires) {
                    throw new InvalidArgumentException(
                        sprintf('The "expires" cookie parameter is not valid.', $expires)
                    );
                }
            }

            $cookie .= '; expires=' . substr(
                DateTime::createFromFormat('U', (string)$expires, new DateTimeZone('UTC'))->format('D, d-M-Y H:i:s T'),
                0,
                -5
            );
        }

        if ($domain) {
            $cookie .= '; domain=' . $domain;
        }

        $cookie .= '; path=' . $path;

        if ($secure) {
            $cookie .= '; secure';
        }

        if ($httponly) {
            $cookie .= '; httponly';
        }

        return $cookie;
    }

    /**
     * Sets a flag indicating whether the Symfony HtmlDumper will be used to dump variables for
     * rich variable rendering.
     *
     * @param bool $value
     * @return $this
     */
    public function useHtmlVarDumper($value = true)
    {
        $this->useHtmlVarDumper = $value;
        return $this;
    }

    /**
     * Indicates whether the Symfony HtmlDumper will be used to dump variables for rich variable
     * rendering.
     *
     * @return mixed
     */
    public function isHtmlVarDumperUsed()
    {
        return $this->useHtmlVarDumper;
    }
}
