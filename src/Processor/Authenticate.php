<?php namespace Orchestra\Foundation\Processor;

use Orchestra\Contracts\Auth\Guard;

abstract class Authenticate extends Processor
{
    /**
     * The auth guard implementation.
     *
     * @var \Orchestra\Contracts\Auth\Guard
     */
    protected $auth;

    /**
     * Create a new processor instance.
     *
     * @param  \Orchestra\Contracts\Auth\Guard  $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Get user.
     *
     * @return \Orchestra\Model\User|null
     */
    protected function getUser()
    {
        return $this->auth->getUser();
    }
}
