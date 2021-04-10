<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PgFramework\Security\Firewall;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This interface must be implemented by firewall maps.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author William Lety <william.lety@gmail.com>
 */
interface FirewallMapInterface
{
    /**
     * Returns the authentication listeners, and the main app listener to use
     * for the given request.
     *
     * If there are no authentication listeners, the first inner array must be
     * empty.
     *
     * If there is no main app listener, the second inner array must be
     * empty.
     *
     * @return array of the format [[AuthenticationListener], [MainListener]]
     */
    public function getListeners(ServerRequestInterface $request);
}