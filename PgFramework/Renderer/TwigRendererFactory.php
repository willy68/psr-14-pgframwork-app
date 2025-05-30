<?php

declare(strict_types=1);

namespace PgFramework\Renderer;

use DebugBar\DebugBarException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Twig\Environment;
use DebugBar\DebugBar;
use Twig\Profiler\Profile;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Psr\Container\ContainerInterface;
use Twig\Extension\ProfilerExtension;
use DebugBar\Bridge\NamespacedTwigProfileCollector;

class TwigRendererFactory
{
    /**
     * @param ContainerInterface $container
     * @return TwigRenderer
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws DebugBarException
     */
    public function __invoke(ContainerInterface $container): TwigRenderer
    {
        $debug = $container->get('env') !== 'prod';

        $viewPath = $container->get('views.path');
        $loader = new FilesystemLoader($viewPath);
        $twig = new Environment($loader, [
            'debug' => $debug,
            'cache' => $debug ? false : $container->get('app.cache.dir') . '/views',
            'auto_reload' => $debug
        ]);
        $twig->addExtension(new DebugExtension());
        if ($container->has('twig.extensions')) {
            foreach ($container->get('twig.extensions') as $extension) {
                $twig->addExtension($extension);
            }
        }

        if ($debug && $container->has(DebugBar::class)) {
            /** @var DebugBar $debugBar*/
            $debugBar = $container->get(DebugBar::class);
            $profile = new Profile();
            $twig->addExtension(new ProfilerExtension($profile));
            $debugBar->addCollector(new NamespacedTwigProfileCollector($profile, $twig));
        }
        return new TwigRenderer($twig);
    }
}
