<?php

namespace App\Auth;

use App\Auth\Models\UserToken;
use Framework\Auth\TokenInterface;
use ActiveRecord\Exceptions\RecordNotFound;
use Framework\Auth\Repository\TokenRepositoryInterface;

class UserTokenRepository implements TokenRepositoryInterface
{
    /**
     *
     * @var UserToken
     */
    protected $model = UserToken::class;


    /**
     * get cookie token from database with ActiveRecord library
     *
     * use user series to find token
     *
     * @param mixed $series
     * @return TokenInterface|null
     */
    public function getTokenBySeries($series): ?TokenInterface
    {
        try {
            $token = $this->model::find('last', ['conditions' => ["series = ?", $series]]);
        } catch (\Exception $e) {
            return null;
        }
        if ($token) {
            return $token;
        }
        return null;
    }

    /**
     * get cookie token from database with ActiveRecord library
     *
     * use user credential (ex. username or email)
     *
     * @param mixed $credential
     * @return TokenInterface|null
     */
    public function getTokenByCredential($credential): ?TokenInterface
    {
        try {
            $token = $this->model::find('last', ['conditions' => ["credential = ?", $credential]]);
        } catch (\Exception $e) {
            return null;
        }
        if ($token) {
            return $token;
        }
        return null;
    }

    /**
     * @inheritDoc
     *
     * @param array $token
     * @return \Framework\Auth\TokenInterface|null
     */
    public function saveToken(array $token): ?TokenInterface
    {
        if (empty($token)) {
            return null;
        }

        /** @var UserToken */
        $tokenModel = new $this->model();
        $tokenModel->create($this->getParams($token));
        return $tokenModel;
    }

    /**
     * Mise à jour du token en database
     *
     * @param array $token
     * @param mixed $id
     * @return TokenInterface|null
     */
    public function updateToken(array $token, $id): ?TokenInterface
    {
        try {
            $userToken = $this->model::find($id);
        } catch (RecordNotFound $exception) {
            return null;
        }
        /** @var \ActiveRecord\Model $userToken*/
        $result = $userToken->update_attributes($this->getParams($token));
        if (!$result) {
            return null;
        }
        return $userToken;
    }

    public function deleteToken(int $id)
    {
        $model = $this->model::find($id);
        $model->delete();
    }

    /**
     * Get only the params needed by the array:
     * ['credential', 'random_password', 'expiration_date', 'is_expired']
     *
     * @param array $params
     * @return array
     */
    protected function getParams(array $params): array
    {
        $params = array_filter($params, function ($key) {
            return in_array($key, ['series', 'credential', 'random_password', 'expiration_date']);
        }, ARRAY_FILTER_USE_KEY);
        return $params;
    }
}
