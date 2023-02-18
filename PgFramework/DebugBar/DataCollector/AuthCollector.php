<?php

declare(strict_types=1);

namespace PgFramework\DebugBar\DataCollector;

use PgFramework\Auth\Auth;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;

class AuthCollector extends DataCollector implements Renderable, AssetProvider
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function getName(): string
    {
        return 'auth';
    }

    public function collect(): array
    {
        $user = $this->auth->getUser();
        if (null === $user) {
            $data = [
                'data' => ['user' => 'Unknown']
            ];
            $text =  'Unknown';
        }

        if ($user) {
            $data['data'] = [
                'Username' => $user->getUsername(),
                'Email' => $user->getEmail(),
                'Roles' => $user->getRoles(),
            ];
            $text =  $user->getUsername();
        }

        foreach ($data['data'] as $key => $value) {
            $data['data'][$key] = $this->getVarDumper()->renderVar($value);
        }
        $data['text'] = $text;

        return $data;
    }

    public function getWidgets(): array
    {
        return [
            "auth" => [
                "icon" => "lock",
                "widget" => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                "map" => "auth.data",
                "default" => "{}"
            ],
            'currentUser' => [
                "icon" => "user",
                "tooltip" => "User",
                "map" => "auth.text",
                "default" => ""
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->getVarDumper()->getAssets();
    }
}
