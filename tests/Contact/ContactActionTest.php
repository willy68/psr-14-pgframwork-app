<?php

namespace Tests\App\Contact;

use App\Contact\ContactAction;
use Mezzio\Router\RouterInterface;
use PgFramework\Renderer\RendererInterface;
use PgFramework\Response\ResponseRedirect;
use PgFramework\Session\FlashService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Tests\ActionTestCase;

class ContactActionTest extends ActionTestCase
{
    private $action;
    private $renderer;
    private $flash;
    private $mailer;

    /**
     * @var string
     */
    private $to = "demo@local.dev";

    public function setUp(): void
    {
        /** @var RendererInterface $renderer */
        $renderer = $this->renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
        /** @var FlashService $flash */
        $flash = $this->flash = $this->getMockBuilder(FlashService::class)->disableOriginalConstructor()->getMock();
        /** @var MailerInterface $mailer */
        $mailer = $this->mailer = $this
            ->getMockBuilder(MailerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var RouterInterface $router */
        $router = $this->getMockBuilder(RouterInterface::class)->getMock();
        $this->action = new ContactAction($this->to, $renderer, $flash, $mailer, $router);
    }

    public function testGet()
    {
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@contact/contact')
            ->willReturn('');
        call_user_func($this->action, $this->makeRequest('/contact'));
    }

    public function testPostInvalid()
    {
        $request = $this->makeRequest('/contact', [
            'name'    => 'Jean marc',
            'email'   => 'azezae',
            'content' => 'azezaezaezeae'
        ]);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                '@contact/contact',
                $this->callback(function ($params) {
                    $this->assertArrayHasKey('errors', $params);
                    $this->assertArrayHasKey('email', $params['errors']);
                    return true;
                })
            )
            ->willReturn('');
        $this->flash->expects($this->once())->method('error');
        call_user_func($this->action, $request);
    }

    public function testPostValid()
    {
        $request = $this->makeRequest('/contact', [
            'name'    => 'Jean marc',
            'email'   => 'demo@local.dev',
            'content' => 'lorem lorem lorem lorem lorem '
        ]);
        $this->flash->expects($this->once())->method('success');
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function (Email $message) {
                $this->assertContains($this->to, (array)$message->getTo()[0]);
                $this->assertContains('demo@local.dev', (array)$message->getFrom()[0]);
                $this->assertStringContainsString('tetexttextxt', $message->getTextBody());
                $this->assertStringContainsString('htmhtmlhtmll', $message->getHtmlBody());
                return true;
            }));
        $this->renderer->expects($this->any())
            ->method('render')
            ->willReturn('tetexttextxt', 'htmhtmlhtmll');
        $response = call_user_func($this->action, $request);
        $this->assertInstanceOf(ResponseRedirect::class, $response);
    }
}
