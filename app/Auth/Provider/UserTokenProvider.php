<?php

namespace App\Auth\Provider;

use App\Auth\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use PgFramework\Database\Hydrator;
use PgFramework\Auth\TokenInterface;
use PgFramework\Auth\Provider\TokenProviderInterface;

class UserTokenProvider implements TokenProviderInterface
{
    protected string $entity;

    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, string $entity = UserToken::class)
    {
        $this->em = $em;
        $this->entity = $entity;
    }

    /**
     * Get cookie token from a database with Doctrine library
     * use user series to find token.
     *
     * @param mixed $series
     * @return TokenInterface|null
     */
    public function getTokenBySeries($series): ?TokenInterface
    {
        $repo = $this->em->getRepository($this->entity);
        return $repo->findOneBy(["series" => $series]);
    }

    /**
     * Get token from a database
     * use credential (ex. username or email).
     *
     * @param mixed $credential
     * @return TokenInterface|null
     */
    public function getTokenByCredential(mixed $credential): ?TokenInterface
    {
        $repo = $this->em->getRepository($this->entity);
        return $repo->findOneBy(["credential" => $credential]);
    }

    public function saveToken(array $token): ?TokenInterface
    {
        if (empty($token)) {
            return null;
        }

        /** @var UserToken $newToken */
        // ['credential', 'random_password', 'expiration_date', 'is_expired']
        $newToken = Hydrator::hydrate($this->getParams($token), $this->entity);

        $this->em->persist($newToken);
        $this->em->flush();
        return $newToken;
    }

    public function updateToken(array $token, mixed $id): ?TokenInterface
    {
        $userToken = $this->em->find($this->entity, $id);
        if (null === $userToken) {
            return null;
        }

        $userToken = Hydrator::hydrate($this->getParams($token), $userToken);
        $this->em->flush();
        return $userToken;
    }

    public function deleteToken(int $id): void
    {
        $userToken = $this->em->find($this->entity, $id);
        if (null === $userToken) {
            return;
        }

        $this->em->remove($userToken);
        $this->em->flush();
    }

    /**
     * Filter params with:
     * ['series', 'credential', 'random_password', 'expiration_date']
     *
     * @param array $params
     * @return array
     */
    protected function getParams(array $params): array
    {
        return array_filter($params, function ($key) {
            return in_array($key, ['series', 'credential', 'random_password', 'expiration_date']);
        }, ARRAY_FILTER_USE_KEY);
    }
}
