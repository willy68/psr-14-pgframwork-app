<?php

declare(strict_types=1);

namespace PgFramework\Auth\RememberMe;

use PgFramework\Auth\Provider\UserProviderInterface;

abstract class AbstractRememberMe implements RememberMeInterface
{
    use RememberMeCookieAwareTraits;

    protected string $algo = 'sha256';

    protected string $salt;

    protected UserProviderInterface $userProvider;

    protected array $options = [
        'name' => 'auth_login',
        'attribute' => '_rememberme.cookie',
        'field' => 'username',
        'lifetime' => 3600 * 24 * 3,
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => true,
        'samesite' => null,
    ];

    public function __construct(
        UserProviderInterface $userProvider,
        string $salt = ''
    ) {
        $this->userProvider = $userProvider;
        $this->salt = $salt;
    }

    /**
     * Modifie le tableau d’options du cookie
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options = []): self
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        return $this;
    }

    /**
     * Initialise l’algorithme de cryptage si null vaut sha256 par défaut
     *
     * @param string|null $algo
     * @return self
     */
    public function setAlgo(?string $algo = null): self
    {
        if (!is_null($algo)) {
            $availableAlgorithms = hash_algos();
            if (!in_array($algo, $availableAlgorithms, true)) {
                throw new \RuntimeException(sprintf(
                    'The hash type `%s` was not found. Available algorithms are: %s',
                    $algo,
                    implode(', ', $availableAlgorithms)
                ));
            }
            $this->algo = strtolower($algo);
        }
        return $this;
    }
}
