<?php

namespace middleware;

use Psr\Http\Message\ServerRequestInterface as Request;

use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Slim\Routing\RouteContext;

use Slim\Psr7\Response;

class RequestLog
{
    private $requestId;

    // Every intercepted requests (and their responses too...) will have a uniqid
    public function __construct()
    {
        $this->requestId = uniqid();
    }

    private function getRemoteAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $clientRequest = array();

        $serverResponse = array();

        $clientRequest[$this->requestId] = array();

        $serverResponse[$this->requestId] = array();

        $uri = (string) $request->getUri();

        $args = RouteContext::fromRequest($request)->getRoute()->getArguments();

        $body = array_merge($request->getQueryParams(), (array) $request->getParsedBody());

        $uploaded = $request->getUploadedFiles();

        if (count($uploaded) != 0) {
            $files = [];

            foreach ($uploaded as $file) {
                $files[] = $file->getClientFilename();
            }

            $body["files"] = $files;
        }

        $method = $request->getMethod();

        $protocol = $request->getProtocolVersion();

        $userAgent = getenv('HTTP_USER_AGENT');

        $ip = $this->getRemoteAddress();

        $dt = new DateTime("now", new DateTimeZone('Europe/Rome')); // From Italy with love

        $dtFormatted = $dt->format("m/d/Y h:i:s");

        $clientRequest[$this->requestId]["dt"] = $dtFormatted;

        $clientRequest[$this->requestId]["uri"] = $uri;

        $clientRequest[$this->requestId]["args"] = $args;

        $clientRequest[$this->requestId]["body"] = $body;

        $clientRequest[$this->requestId]["method"] = $method;

        $clientRequest[$this->requestId]["httpVersion"] = $protocol;

        $clientRequest[$this->requestId]["userAgent"] = $userAgent;

        $clientRequest[$this->requestId]["ip"] = $ip;

        // Let's catch the server response too...
        $response = $handler->handle($request);

        $responseCode = $response->getStatusCode();

        $serverResponse[$this->requestId]["code"] = $responseCode;

        $content = (string) $response->getBody();

        try {
            json_decode($content, true);

            $isResponseJson = true;
        } catch (JsonException $e) {

            // ... bad Json, log your exception here...
            $isResponseJson = false;
        }

        if ($isResponseJson) {
            $responseBody = $content;
        } else {
            $responseBody = json_encode(["response" => $content]);
        }

        $serverResponse[$this->requestId]["body"] = $responseBody;

        return $response;
    }
}
