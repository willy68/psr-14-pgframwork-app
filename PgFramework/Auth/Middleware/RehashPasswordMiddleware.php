<?php

namespace PgFramework\Auth\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Server\MiddlewareInterface;
use PgFramework\Security\Hasher\PasswordHasherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class RehashPasswordMiddleware implements MiddlewareInterface
{
    protected $hasher;

    protected $em;

    public function __construct(PasswordHasherInterface $hasher, EntityManagerInterface $em)
    {
        $this->hasher = $hasher;
        $this->em = $em;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $result = $request->getAttribute('auth.result');

        if ($result) {

            /** @var User $user */
            $user = $result->getUser();
            $credentials = $result->getCredentials();
            $plainPassword = $credentials['password'] ?? null;

            if ($plainPassword && $this->hasher->needsRehash($user->getPassword())) {
                $password = $this->hasher->hash($plainPassword);
                $user->setPassword($password);
                $this->em->persist($user);
                $this->em->flush();

                $result->setUser($user);
            }
        }
        return $response;
    }
}
