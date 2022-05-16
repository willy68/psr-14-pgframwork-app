<?php

namespace App\Contact;

use Mezzio\Router\RouterInterface;
use PgFramework\Validator\Validator;
use PgFramework\Session\FlashService;
use PgFramework\Router\Annotation\Route;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var string
     */
    private $to;

    /**
     * @var FlashService
     */
    private $flashService;

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var RouterInterface
     */
    private $router;

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
     * @Route("/register", name="contact", methods={"GET"})
     * @Route("/register", methods={"POST"})
     *
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     */
    #[Route(path: "/contact", name: "contact", methods:['GET'])]
    #[Route(path: "/contact", methods:['POST'])]
    public function __invoke(ServerRequestInterface $request)
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
            $patn = $this->router->generateUri('blog.index');
            return new ResponseRedirect($patn);
        } else {
            $this->flashService->error('Merci de corriger vos erreurs');
            $errors = $validator->getErrors();
            return $this->renderer->render('@contact/contact', compact('errors'));
        }
    }
}
