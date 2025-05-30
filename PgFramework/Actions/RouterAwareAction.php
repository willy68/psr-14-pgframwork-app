<?php

declare(strict_types=1);

namespace PgFramework\Actions;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Rajoute des méthodes à l’utilisation du routeur
 */
trait RouterAwareAction
{
  /**
   * Redirection
   *
   * @param string $path
   * @param array $params
   * @return ResponseInterface
   */
    public function redirect(string $path, array $params = []): ResponseInterface
    {
        $redirectUri = $this->router->generateUri($path, $params);
        return (new Response())
        ->withStatus(301)
        ->withHeader('Location', $redirectUri);
    }
}
