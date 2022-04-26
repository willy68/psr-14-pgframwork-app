<?php

declare(strict_types=1);

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
 * AccessMap allows configuration of different access control rules for
 * specific parts of the website.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kris Wallsmith <kris@symfony.com>
 */
interface AccessMapInterface
{
    /**
     * Returns security attributes and required channel for the supplied request.
     *
     * @return array A tuple of security attributes and the required channel
     */
    public function getPatterns(ServerRequestInterface $request);
}
