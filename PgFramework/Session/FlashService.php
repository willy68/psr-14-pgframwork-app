<?php

declare(strict_types=1);

namespace PgFramework\Session;

use Mezzio\Session\SessionInterface;

class FlashService
{
    private SessionInterface $session;

    private string $sessionKey = 'flash';

    private ?array $messages = null;

    /**
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param string $message
     * @return void
     */
    public function success(string $message): void
    {
        $flash = $this->session->get($this->sessionKey, []);
        $flash['success'] = $message;
        $this->session->set($this->sessionKey, $flash);
    }

    /**
     * @param string $message
     * @return void
     */
    public function error(string $message): void
    {
        $flash = $this->session->get($this->sessionKey, []);
        $flash['error'] = $message;
        $this->session->set($this->sessionKey, $flash);
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function get(string $type): ?string
    {
        if (is_null($this->messages)) {
            $this->messages = $this->session->get($this->sessionKey, []);
            $this->session->unset($this->sessionKey);
        }

        if (array_key_exists($type, $this->messages)) {
            return $this->messages[$type];
        }
        return null;
    }
}
