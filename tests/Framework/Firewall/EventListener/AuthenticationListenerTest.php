<?php

namespace Tests\Framework\Firewall\EventListener;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PgFramework\Event\Events;
use PHPUnit\Framework\TestCase;
use League\Event\ListenerPriority;
use PgFramework\Auth\RememberMe\RememberMeInterface;
use PgFramework\Auth\UserInterface;
use PgFramework\Event\RequestEvent;
use PgFramework\Security\Authentication\AuthenticationInterface;
use PgFramework\Security\Authentication\Exception\AuthenticationFailureException;
use PgFramework\Security\Authentication\Result\AuthenticateResult;
use PgFramework\Security\Firewall\Event\LoginFailureEvent;
use PgFramework\Security\Firewall\Event\LoginSuccessEvent;
use Psr\Http\Message\ServerRequestInterface;
use PgFramework\Security\Firewall\EventListener\AuthenticationListener;
use Psr\EventDispatcher\EventDispatcherInterface;

class AuthenticationListenerTest extends TestCase
{
    private $rememberMe;

    private $dispatcher;

    public function setUp(): void
    {
        $this->rememberMe = $this->getMockBuilder(RememberMeInterface::class)->getMock();
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
    }

    public function makeAuthenticator($support = true)
    {
        $authenticator = $this->getMockBuilder(AuthenticationInterface::class)->getMock();
        $authenticator->expects($this->once())->method('supports')->willReturn(true);
        return $authenticator;
    }

    public function makeRequest()
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        return $request;
    }

    public function makeLoginFailureEvent($exception)
    {
        $event = $this->getMockBuilder(LoginFailureEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getException')->willReturn($exception);
        return $event;
    }

    public function makeLoginSuccessEvent($result)
    {
        $event = $this->getMockBuilder(LoginSuccessEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getResult')->willReturn($result);
        return $event;
    }

    public function makeRequestEvent($request)
    {
        $event = $this->getMockBuilder(RequestEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('getRequest')->willReturn($request);
        return $event;
    }

    public function makeResult($rememberMe = true)
    {
        $user = $this->getMockBuilder(UserInterface::class)->getMock();
        $credentials['identifier'] = 'john';
        $credentials['password'] = 'fake';
        if ($rememberMe) {
            $credentials['rememberMe'] = $rememberMe;
        }
        /** @var UserInterface $user */
        return new AuthenticateResult($credentials, $user);
    }

    public function makeListener(array $authenticator)
    {
        return new AuthenticationListener($authenticator, $this->rememberMe, $this->dispatcher);
    }

    public function testAuthenticationFailureWithResponse()
    {
        $exception = new AuthenticationFailureException();
        $request = $this->makeRequest();
        $response = new Response();
        $requestEvent = $this->makeRequestEvent($request);
        $requestEvent->expects($this->once())->method('setResponse')->with($response);
        $authenticator = $this->makeAuthenticator();
        $authenticator->expects($this->once())->method('authenticate')->with($request)->willThrowException($exception);
        $authenticator->expects($this->once())->method('onAuthenticateFailure')->willReturn($response);
        $loginFailureEvent = $this->makeLoginFailureEvent($exception);
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturn($loginFailureEvent);
        /** @var RequestEvent $requestEvent */
        $this->makeListener([$authenticator])->onAuthentication($requestEvent);
    }

    public function testAuthenticationFailure()
    {
        $exception = new AuthenticationFailureException();
        $request = $this->makeRequest();
        $requestEvent = $this->makeRequestEvent($request);
        $requestEvent->expects($this->never())->method('setResponse');
        $authenticator = $this->makeAuthenticator();
        $authenticator->expects($this->once())->method('authenticate')->with($request)->willThrowException($exception);
        $authenticator->expects($this->once())->method('onAuthenticateFailure')->willReturn(null);
        $loginFailureEvent = $this->makeLoginFailureEvent($exception);
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturn($loginFailureEvent);
        /** @var RequestEvent $requestEvent */
        $this->makeListener([$authenticator])->onAuthentication($requestEvent);
    }

    public function testAuthenticationSuccessWithResponseWithRememberMe()
    {
        $result = $this->makeResult();
        $request = (new ServerRequest('GET', '/'))->withAttribute('auth.result', $result);
        $response = new Response();
        $requestEvent = $this->makeRequestEvent($request);
        $requestEvent->expects($this->once())->method('setRequest')->with($request);
        $requestEvent->expects($this->once())->method('setResponse')->with($response);
        $authenticator = $this->makeAuthenticator();
        $authenticator->expects($this->once())->method('authenticate')->with($request)->willReturn($result);
        $authenticator->expects($this->once())->method('onAuthenticateSuccess')->willReturn($response);
        $authenticator->expects($this->once())->method('supportsRememberMe')->willReturn(true);
        $loginSuccessEvent = $this->makeLoginSuccessEvent($result);
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturn($loginSuccessEvent);
        $this->rememberMe->expects($this->once())->method('onLogin')->willReturn($response);
        /** @var RequestEvent $requestEvent */
        $this->makeListener([$authenticator])->onAuthentication($requestEvent);
    }

    public function testAuthenticationSuccessWithResponseWithoutRememberMe()
    {
        $result = $this->makeResult(false);
        $request = (new ServerRequest('GET', '/'))->withAttribute('auth.result', $result);
        $response = new Response();
        $requestEvent = $this->makeRequestEvent($request);
        $requestEvent->expects($this->once())->method('setRequest')->with($request);
        $requestEvent->expects($this->once())->method('setResponse')->with($response);
        $authenticator = $this->makeAuthenticator();
        $authenticator->expects($this->once())->method('authenticate')->with($request)->willReturn($result);
        $authenticator->expects($this->once())->method('onAuthenticateSuccess')->willReturn($response);
        $authenticator->expects($this->once())->method('supportsRememberMe')->willReturn(false);
        $loginSuccessEvent = $this->makeLoginSuccessEvent($result);
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturn($loginSuccessEvent);
        $this->rememberMe->expects($this->never())->method('onLogin');
        /** @var RequestEvent $requestEvent */
        $this->makeListener([$authenticator])->onAuthentication($requestEvent);
    }

    public function testAuthenticationSuccessWithoutResponse()
    {
        $result = $this->makeResult(false);
        $request = (new ServerRequest('GET', '/'))->withAttribute('auth.result', $result);
        $response = new Response();
        $requestEvent = $this->makeRequestEvent($request);
        $requestEvent->expects($this->once())->method('setRequest')->with($request);
        $requestEvent->expects($this->never())->method('setResponse')->with($response);
        $authenticator = $this->makeAuthenticator();
        $authenticator->expects($this->once())->method('authenticate')->with($request)->willReturn($result);
        $authenticator->expects($this->once())->method('onAuthenticateSuccess')->willReturn(null);
        $authenticator->expects($this->never())->method('supportsRememberMe');
        $loginSuccessEvent = $this->makeLoginSuccessEvent($result);
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturn($loginSuccessEvent);
        $this->rememberMe->expects($this->never())->method('onLogin');
        /** @var RequestEvent $requestEvent */
        $this->makeListener([$authenticator])->onAuthentication($requestEvent);
    }

    public function testSubscribeEvent()
    {
        $this->assertEquals(
            [Events::REQUEST => ['onAuthentication', ListenerPriority::HIGH]],
            AuthenticationListener::getSubscribedEvents()
        );
    }
}
