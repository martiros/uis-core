<?php

namespace UIS\Core\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Guard as IlluminateGuard;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

class Guard extends IlluminateGuard
{
    /**
     * @var \UIS\Core\Auth\AuthTokenProviderContract
     */
    protected $authTokenProvider;

    public function __construct(UserProvider $provider,
        SessionInterface $session,
        Request $request = null,
        AuthTokenProviderContract $authTokenProvider)
    {
        $this->authTokenProvider = $authTokenProvider;
        parent::__construct($provider, $session, $request);
    }

    /**
     * Log a user into the application.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login(UserContract $user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        // If the user should be permanently "remembered" by the application we will
        // queue a permanent cookie that contains the encrypted copy of the user
        // identifier. We will then decrypt this later to retrieve the users.
        if ($remember) {
            $authToken = $this->createRememberTokenIfDoesntExist($user);
            if ($authToken) {
                $this->queueRememberMeToken($user, $authToken);
            }
        }

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
    }

    protected function createRememberTokenIfDoesntExist(UserContract $user)
    {
        if ($this->hasActiveRememberToken($user)) {
            return;
        }

        $expireDate = new Carbon();
        $expireDate->addMonth();

        return $this->authTokenProvider->create([
                    'token' => str_random(60),
                    'user_id' => $user->id,
                    'expire_date' => $expireDate,
                ]);
    }

    /**
     * @param UserContract $user
     * @return bool
     */
    protected function hasActiveRememberToken(UserContract $user)
    {
        $recallerId = $this->getRecallerId();
        if (!$recallerId) {
            return false;
        }
        $token = $this->authTokenProvider->retrieveById($recallerId);
        if (!$token) {
            return false;
        }

        if (!Str::equals($token->token, $this->getRecallerHash())) {
            return false;
        }

        if (!$token->isActiveToken()) {
            $this->authTokenProvider->delete($recallerId);

            return false;
        }

        return true;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $user = $this->user();

        // If we have an event dispatcher instance, we can fire off the logout event
        // so any further processing can be done. This allows the developer to be
        // listening for anytime a user signs out of this application manually.
        $this->clearUserDataFromStorage();

        $this->removeRememberMeToken();

        if (isset($this->events)) {
            $this->events->fire('auth.logout', [$user]);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    protected function removeRememberMeToken()
    {
        $recallerId = $this->getRecallerId();
        if (!$recallerId) {
            return;
        }
        $this->authTokenProvider->delete($recallerId);
    }

    /**
     * Queue the remember me token cookie into the cookie jar.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function queueRememberMeToken(UserContract $user, AuthToken $authToken)
    {
        $value = $authToken->getTokenIdentifier().'|'.$authToken->getToken();

        $this->getCookieJar()->queue($this->createRecaller($value));
    }

    public function validateUser($user, $password)
    {
        return $this->attemptUser($user, $password, false, false);
    }

    /**
     * Attempt to authenticate a user using the given user object.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string $password
     * @param  bool   $remember
     * @param  bool   $login
     * @return bool
     */
    public function attemptUser($user, $password, $remember = false, $login = true)
    {
        $credentials = ['id' => $user->id, 'password' => $password];
        $this->fireAttemptEvent($credentials, $remember, $login);

        $this->lastAttempted = $user;

        if ($this->hasValidCredentials($user, $credentials)) {
            if ($login) {
                $this->login($user, $remember);
            }

            return true;
        }

        return false;
    }

    /**
     * Pull a user from the repository by its recaller ID.
     *
     * @param  string  $recaller
     * @return mixed
     */
    protected function getUserByRecaller($recaller)
    {
        if ($this->validRecaller($recaller) && !$this->tokenRetrievalAttempted) {
            $this->tokenRetrievalAttempted = true;

            list($tokenId, $token) = explode('|', $recaller, 2);

            $this->viaRemember = !is_null($user = $this->retrieveUserByToken($tokenId, $token));

            return $user;
        }
    }

    protected function retrieveUserByToken($tokenId, $token)
    {
        $authToken = AuthToken::find($tokenId);
        if (!$authToken) {
            return;
        }

        if (!Str::equals($authToken->token, $token)) {
            return;
        }

        return User::find($authToken->user_id);
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember';
    }

    protected function validRecaller($recaller)
    {
        if (!is_string($recaller) || !str_contains($recaller, '|')) {
            return false;
        }

        $segments = explode('|', $recaller);

        return count($segments) == 2 && trim($segments[0]) !== '' && trim($segments[1]) !== '';
    }

    /**
     * Get the auth token ID from the recaller cookie.
     *
     * @return string
     */
    protected function getRecallerId()
    {
        if ($this->validRecaller($recaller = $this->getRecaller())) {
            return head(explode('|', $recaller));
        }
    }

    protected function getRecallerHash()
    {
        if ($this->validRecaller($recaller = $this->getRecaller())) {
            return last(explode('|', $recaller));
        }
    }
}
