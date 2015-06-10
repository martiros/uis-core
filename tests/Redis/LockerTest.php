<?php

use UIS\Core\Redis\Locker;
use Illuminate\Support\Facades\Redis;

class LockerTest extends TestCase
{
    public function testLockAndUnlock()
    {
        $objThatNeedLock = new ClassThatNeedLock();
        $objThatNeedLock->unlock();

        $this->assertTrue($objThatNeedLock->lock());
        $this->assertFalse($objThatNeedLock->lock());

        $objThatNeedLock->unlock();

        $this->assertFalse(Redis::exists($objThatNeedLock->getLockKey()));
    }

    public function testIsLocked()
    {
        $objThatNeedLock = new ClassThatNeedLock();
        $objThatNeedLock->unlock();

        $this->assertFalse($objThatNeedLock->isLocked());
        $this->assertTrue($objThatNeedLock->lock());
        $this->assertTrue($objThatNeedLock->isLocked());

        $objThatNeedLock->unlock();
    }

    public function testLockAndIgnoreExpiredKeyNotCalledWhenLockTimeFalse()
    {
        $objThatNeedLock = new ClassThatNeedLockWithoutLockTime();
        Redis::set($objThatNeedLock->getLockKey(), time() - 60 * 60 * 24);

        $this->assertFalse($objThatNeedLock->lock());
        $objThatNeedLock->unlock();
    }

    public function testOldLockRemovedWhenMaxLockTimePassed()
    {
        $objThatNeedLock = new ClassThatNeedLock();
        $objThatNeedLock->unlock();

        Redis::set($objThatNeedLock->getLockKey(), time() - $objThatNeedLock->getMaxLockTime() - 1);
        $oldLockTime = $objThatNeedLock->getLockTime();

        $this->assertTrue($objThatNeedLock->lock());
        $this->assertTrue($objThatNeedLock->isLocked());

        $this->assertGreaterThan($oldLockTime, $objThatNeedLock->getLockTime());

        $objThatNeedLock->unlock();
    }

    public function testOldLockNotRemovedWhenMaxLockTimeNotPassed()
    {
        $objThatNeedLock = new ClassThatNeedLock();
        $objThatNeedLock->unlock();

        Redis::set($objThatNeedLock->getLockKey(), time() - $objThatNeedLock->getMaxLockTime() + 1);
        $oldLockTime = $objThatNeedLock->getLockTime();

        $this->assertFalse($objThatNeedLock->lock());
        $this->assertTrue($objThatNeedLock->isLocked());

        $this->assertEquals($oldLockTime, $objThatNeedLock->getLockTime());

        $objThatNeedLock->unlock();
    }
}

class ClassThatNeedLock
{
    use Locker;

    public function getMaxLockTime()
    {
        return 1 * 60;
    }

    public function getLockKey()
    {
        return 'test:redis_lock:key';
    }
}

class ClassThatNeedLockWithoutLockTime
{
    use Locker;

    public function getMaxLockTime()
    {
        return false;
    }

    public function getLockKey()
    {
        return 'test:redis_lock:key';
    }
}
