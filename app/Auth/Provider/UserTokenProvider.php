<?php

namespace App\Auth\Provider;

use App\Auth\Entity\UserToken;
use Doctrine\ORM\EntityManager;
use PgFramework\Database\Hydrator;
use PgFramework\Auth\TokenInterface;
use PgFramework\Auth\Provider\TokenProviderInterface;

class UserTokenProvider implements TokenProviderInterface
{
    /**
     * @var string
     */
    protected $entity;

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em, string $entity = UserToken::class)
    {
        $this->em = $em;
        $this->entity = $entity;
    }
    /**
     * get cookie token from database with Doctrine library
     *
     * use user series to find token
     *
     * @param mixed $series
     * @return TokenInterface|null
     */
    public function getTokenBySeries($series): ?TokenInterface
    {
        $token = null;
        try {
            $repo = $this->em->getRepository($this->entity);
            $token = $repo->findOneBy(["series" => $series]);
        } catch (\Exception $e) {
            return null;
        }
        return $token;
    }

    /**
     * get cookie token from database with Doctrine library
     *
     * use user credential (ex. username or email)
     *
     * @param mixed $credential
     * @return TokenInterface|null
     */
    public function getTokenByCredential($credential): ?TokenInterface
    {
        $token = null;
        try {
            $repo = $this->em->getRepository($this->entity);
            $token = $repo->findOneBy(["credential" => $credential]);
        } catch (\Exception $e) {
            return null;
        }
        return $token;
    }

    /**
     * @inheritDoc
     *
     * @param array $token
     * @return \PgFramework\Auth\TokenInterface|null
     */
    public function saveToken(array $token): ?TokenInterface
    {
        if (empty($token)) {
            return null;
        }

        /** @var UserToken */
        // ['credential', 'random_password', 'expiration_date', 'is_expired']
        $newToken = Hydrator::hydrate($this->getParams($token), $this->entity);

        $this->em->persist($newToken);
        $this->em->flush();
        return $newToken;
    }

    /**
     * Mise à jour du token en database
     *
     * @param array $token
     * @param mixed $id
     * @return TokenInterface|null
     */
    public function updateToken(array $token, $id): ?TokenInterface
    {
        try {
            $userToken = $this->em->find($this->entity, $id);
        } catch (\Exception $e) {
            return null;
        }

        if (null === $userToken) {
            return null;
        }

        $userToken = Hydrator::hydrate($this->getParams($token), $userToken);

        $this->em->flush();

        return $userToken;
    }

    public function deleteToken(int $id)
    {
        try {
            $userToken = $this->em->find($this->entity, $id);
        } catch (\Exception $e) {
            return;
        }

        if (null === $userToken) {
            return;
        }

        $this->em->remove($userToken);
        $this->em->flush();
    }

    /**
     * Get only the params needed by the array:
     * ['series', 'credential', 'random_password', 'expiration_date']
     *
     * @param array $params
     * @return array
     */
    protected function getParams(array $params): array
    {
        $params = array_filter($params, function ($key) {
            return in_array($key, ['series', 'credential', 'random_password', 'expiration_date']);
        }, ARRAY_FILTER_USE_KEY);
        return $params;
    }
}
