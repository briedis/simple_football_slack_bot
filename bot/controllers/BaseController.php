<?php

namespace w\Bot\controllers;

use Slack\ApiClient;
use Slack\Payload;
use w\Bot\Database;
use w\Bot\FootballState;

abstract class BaseController
{
    protected $db;
    protected $client;
    protected $footballState;
    protected $payload;

    protected $types = [];

    private static $routes = [
        '/event' => EventController::class,
        '/default' => DefaultController::class,
        '/action' => ActionController::class,
    ];

    function __construct(
        ApiClient $client,
        Database $db,
        FootballState $footballState,
        Payload $payload
    )
    {
        $this->client = $client;
        $this->db = $db;
        $this->footballState = $footballState;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public static function getController()
    {
        $requestUrl = $_SERVER['REQUEST_URI'];
        // strip GET variables from URL
        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $pos);
        }

        if (isset(self::$routes[$requestUrl])) {
            return self::$routes[$requestUrl];
        }

        return self::$routes['/default'];
    }

    public function process()
    {
        // Log request
        file_put_contents(__DIR__ . '/events.txt', static::class . "\n" . json_encode($this->payload->getData(), JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

        if (!isset($this->types[$this->payload['type']])) {
            throw new \InvalidArgumentException('Invalid payload type received!');
        }

        return call_user_func([$this, $this->types[$this->payload['type']]]);
    }}