<?php

namespace Tests\App\Account\Action;

use PgFramework\Auth\Auth;
use App\Account\User;
use Prophecy\Argument;
use App\Auth\UserTable;
use Tests\ActionTestCase;
use PgFramework\Session\FlashService;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use App\Account\Action\AccountEditAction;
use PgFramework\Renderer\RendererInterface;

class AccountEditActionTest extends ActionTestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy
     */
    private $renderer;

    /**
     * @var AccountEditAction
     */
    private $action;

    /**
     * @var ObjectProphecy
     */
    private $auth;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ObjectProphecy
     */
    private $userTable;

    public function setUp(): void
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->user = new User();
        $this->user->id = 3;
        $this->auth = $this->prophesize(Auth::class);
        $this->auth->getUser()->willReturn($this->user);
        $this->userTable = $this->prophesize(UserTable::class);
        $this->action = new AccountEditAction(
            $this->renderer->reveal(),
            $this->auth->reveal(),
            $this->prophesize(FlashService::class)->reveal(),
            $this->userTable->reveal()
        );
    }

    public function testValid()
    {
        $this->userTable->update(3, [
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ])->shouldBeCalled();
        $response = call_user_func($this->action, $this->makeRequest('/demo', [
            'firstname' => 'John',
            'lastname'  => 'Doe'
        ]));
        $this->assertRedirect($response, '/demo');
    }

    public function testValidWithPassword()
    {
        $this->userTable->update(3, Argument::that(function ($params) {
            $this->assertEquals(['firstname', 'lastname', 'password'], array_keys($params));
            return true;
        }))->shouldBeCalled();
        $response = call_user_func($this->action, $this->makeRequest('/demo', [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'password'  => '0000',
            'password_confirm' => '0000'
        ]));
        $this->assertRedirect($response, '/demo');
    }

    public function testPostInvalid()
    {
        $this->userTable->update()->shouldNotBeCalled();
        $this->renderer->render('@account/account', Argument::that(function ($params) {
            $this->assertEquals(['password'], array_keys($params['errors']));
            return true;
        }));
        $response = call_user_func($this->action, $this->makeRequest('/demo', [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'password'  => '0000',
            'password_confirm' => '00'
        ]));
    }
}
