<?php

namespace PgFramework\Renderer;

use Twig\Environment;
use DebugBar\DebugBar;
use Twig\Profiler\Profile;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Psr\Container\ContainerInterface;
use Twig\Extension\ProfilerExtension;
use DebugBar\Bridge\NamespacedTwigProfileCollector;

/**
 * Undocumented class
 */
class TwigRendererFactory
{
  /**
   * Undocumented function
   *
   * @param ContainerInterface $container
   * @return TwigRenderer
   */
    public function __invoke(ContainerInterface $container): TwigRenderer
    {
        $debug = $container->get('env') !== 'prod';

        $viewPath = $container->get('views.path');
        $loader = new FilesystemLoader($viewPath);
        $twig = new Environment($loader, [
            'debug' => $debug,
            'cache' => $debug ? false : 'tmp/views',
            'auto_reload' => $debug
        ]);
        $twig->addExtension(new DebugExtension());
        if ($container->has('twig.extensions')) {
            foreach ($container->get('twig.extensions') as $extension) {
                $twig->addExtension($extension);
            }
        }

        if ($debug && $container->has(DebugBar::class)) {
            /** @var DebugBar */
            $debugBar = $container->get(DebugBar::class);
            $profile = new Profile();
            $twig->addExtension(new ProfilerExtension($profile));
            $debugBar->addCollector(new NamespacedTwigProfileCollector($profile, $twig));
        }
        return new TwigRenderer($twig);
    }
}
