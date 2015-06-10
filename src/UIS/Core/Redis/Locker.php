<?php

namespace UIS\Core\Redis;

use Illuminate\Support\Facades\Redis;
use UIS\Core\Exceptions\Exception;
use BadMethodCallException;

trait Locker
{
    /**
     * Return true is locked successfully, else false if already locked.
     * @return bool
     * @throws Exception
     */
    public function lock()
    {
        Redis::clearLastError();

        $notLocked = Redis::setnx(self::getLockKey(), time());
        if (!empty(Redis::getLastError())) {
            throw new Exception('Redis error-'.Redis::getLastError());
        }

        if (!$notLocked) {
            return $this->lockAndIgnoreExpiredKey();
        }

        return true;
    }

    public function unlock()
    {
        Redis::delete(self::getLockKey());
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return Redis::exists($this->getLockKey());
    }

    protected function lockAndIgnoreExpiredKey()
    {
        if ($this->getMaxLockTime() === false) {
            return false;
        }
        Redis::clearLastError();

        $script = $this->getCheckLockScript();
        $scriptSha = sha1($script);
        $scriptArgs = [$this->getLockKey(), time(), $this->getMaxLockTime()];
        $result = Redis::evaluateSha($scriptSha, $scriptArgs, 1);
        if (strpos(Redis::getLastError(), 'NOSCRIPT') !== false) {
            Redis::clearLastError();
            $result = Redis::evaluate($script, $scriptArgs, 1);
        }

        if (!empty(Redis::getLastError())) {
            throw new UIS_Application_Exception('Redis error - '.Redis::getLastError());
        }

        return boolval($result);
    }

    /**
     * Lua script to check is lock key exists and not expired.
     * <pre>
     *      KEYS[1] - lock key
     *      ARGV[1] - time(now in seconds)
     *      ARGV[2] - max lock time
     * </pre>.
     * @return string
     */
    protected static function getCheckLockScript()
    {
        return "
            local lastProcessTime = redis.call('GET', KEYS[1])

            --  Set new lock key and return true if last process time is not set,
            if lastProcessTime == false then
                redis.call('SET', KEYS[1], ARGV[1])
                return true
            end

            --  Set new lock key and return true if passed time to old
            local time = tonumber(ARGV[1])
            local lockTime = tonumber(ARGV[2])
            local passedTime = time - lastProcessTime
            if passedTime > lockTime then
                redis.call('SET', KEYS[1], ARGV[1])
                return true
            end

            return false  ";
    }

    public function updateLockTime()
    {
        Redis::set($this->getLockKey(), time());
    }

    public function getLockTime()
    {
        $lockTime = Redis::get($this->getLockKey());
        if ($lockTime === false) {
            return false;
        }

        return (int) $lockTime;
    }

    public function getMaxLockTime()
    {
        throw new BadMethodCallException('You must implements getMaxLockTime method.');
    }

    public function getLockKey()
    {
        throw new BadMethodCallException('You must implements getLockKey method.');
    }
}
