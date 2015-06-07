<?php
namespace UIS\Core\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Guard as IlluminateGuard;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Symfony\Component\Security\Core\Util\StringUtils;

class Guard extends IlluminateGuard
{

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
        if ($remember)
        {
            $authToken = $this->createRememberTokenIfDoesntExist($user);

            $this->saveRecallerCookie($user, $authToken);
        }

        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
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

        if ( ! is_null($this->user))
        {
//            $this->refreshRememberToken($user);
        }

        if (isset($this->events))
        {
            $this->events->fire('auth.logout', [$user]);
        }

        // Once we have fired the logout event we will clear the users out of memory
        // so they are no longer available as the user is no longer considered as
        // being signed into this application and should not be available here.
        $this->user = null;

        $this->loggedOut = true;
    }

    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function queueRecallerCookie(UserContract $user)
    {
        $value = $user->getAuthIdentifier().'|'.$user->getRememberToken();

        $this->getCookieJar()->queue($this->createRecaller($value));
    }

    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    protected function saveRecallerCookie(UserContract $user, AuthToken $authToken)
    {
        $value = $authToken->id.'|'.$authToken->token;

        $this->getCookieJar()->queue($this->createRecaller($value));
    }

    /**
     * Create a remember me cookie for a given ID.
     *
     * @param  string  $value
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function createRecaller($value)
    {
        return $this->getCookieJar()->forever($this->getRecallerName(), $value);
    }

    /**
     * Remove the user data from the session and cookies.
     *
     * @return void
     */
    protected function clearUserDataFromStorage()
    {
        $this->session->remove($this->getName());

        $recaller = $this->getRecallerName();

        $this->getCookieJar()->queue($this->getCookieJar()->forget($recaller));
    }


    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_'.md5(get_class($this));
    }

    protected function createRememberTokenIfDoesntExist(UserContract $user)
    {
        if ($this->hasActiveRememberToken($user)) {
            return null;
        }

        $expireDate = new Carbon();
        $expireDate->addYear();
        $authToken = new AuthToken([
            'token' => str_random(60),
            'user_id' => $user->id,
            'expire_date' => $expireDate
        ]);
        $authToken->save();
        return $authToken;
        //  $this->viaRemember = true;
    }

    protected function hasActiveRememberToken(UserContract $user)
    {
        return false;
        $recallerId = $this->getRecallerId();
        if (!$recallerId) {
            return false;
        }

        dd($this->getRecaller(), $user);
    }


    /****************************************************************************************/
    /****************************************************************************************/
    /****************************************************************************************/

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

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials))
        {
            if ($login) $this->login($user, $remember);

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
        if ($this->validRecaller($recaller) && ! $this->tokenRetrievalAttempted)
        {
            $this->tokenRetrievalAttempted = true;

            list($tokenId, $token) = explode('|', $recaller, 2);

            $this->viaRemember = ! is_null($user = $this->retrieveUserByToken($tokenId, $token));

            return $user;
        }
    }

    protected function retrieveUserByToken($tokenId, $token)
    {
        $authToken = AuthToken::find($tokenId);
        if (!$authToken) {
            return;
        }

        if (!StringUtils::equals($authToken->token, $token)) {
            return;
        }

        // @TODO: Check is active user who can login ???
        $user = User::find($authToken->user_id);

//        dd($user);
        return $user;
//        if ()
//        dd('FUNCTION - retrieveUserByToken', func_get_args());
    }

    protected function validRecaller($recaller)
    {
        if ( ! is_string($recaller) || ! str_contains($recaller, '|')) return false;

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
        if ($this->validRecaller($recaller = $this->getRecaller()))
        {
            return head(explode('|', $recaller));
        }
    }
}
