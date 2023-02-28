<?php

namespace App\Contact;

use Mezzio\Router\RouterInterface;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Router\Annotation\Route;
use PgFramework\Session\FlashService;
use PgFramework\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactAction
{
    private RendererInterface $renderer;

    private string $to;

    private FlashService $flashService;

    private MailerInterface $mailer;

    private RouterInterface $router;

    public function __construct(
        string $to,
        RendererInterface $renderer,
        FlashService $flashService,
        MailerInterface $mailer,
        RouterInterface $router
    ) {
        $this->renderer = $renderer;
        $this->to = $to;
        $this->flashService = $flashService;
        $this->mailer = $mailer;
        $this->router = $router;
    }

    /**
     * @Route("/contact", name="contact", methods={"GET"})
     * @Route("/contact", methods={"POST"})
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     * @throws TransportExceptionInterface
     */
    #[Route(path: "/contact", name: "contact", methods: ['GET'])]
    #[Route(path: "/contact", methods: ['POST'])]
    public function __invoke(ServerRequestInterface $request): ResponseInterface|string
    {
        if ($request->getMethod() === 'GET') {
            return $this->renderer->render('@contact/contact');
        }
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->required('name', 'email', 'content')
            ->length('name', 5)
            ->email('email')
            ->length('content', 15);
        if ($validator->isValid()) {
            $this->flashService->success('Merci pour votre email');
            $email = (new Email())
                ->from($params['email'])
                ->to($this->to)
                ->subject('Formulaire de contact')
                ->text($this->renderer->render('@contact/email/contact.text', $params))
                ->html($this->renderer->render('@contact/email/contact.html', $params));
            $this->mailer->send($email);
            $path = $this->router->generateUri('blog.index');
            return new ResponseRedirect($path);
        } else {
            $this->flashService->error('Merci de corriger vos erreurs');
            $errors = $validator->getErrors();
            return $this->renderer->render('@contact/contact', compact('errors'));
        }
    }
}
