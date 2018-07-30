<?php

namespace Hodor\Database\Adapter\Testing;

use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversDefaultClass \Hodor\Database\Adapter\Testing\Database
 */
class DatabaseTest extends TestCase
{
    /**
     * @var Database
     */
    private $database;

    public function setUp()
    {
        $this->database = new Database();
    }

    /**
     * @covers ::getAll
     * @expectedException Exception
     */
    public function testAnExceptionIsThrownWhenRetrievingRowsFromNonExistentTable()
    {
        $this->database->getAll('non_existent');
    }

    /**
     * @covers ::getAll
     */
    public function testEmptyRowSetIsReturnedByDefault()
    {
        $this->assertSame([], $this->database->getAll('buffered_jobs'));
        $this->assertSame([], $this->database->getAll('queued_jobs'));
    }

    /**
     * @covers ::insert
     * @covers ::getAll
     */
    public function testRowCanBeInsertedIntoTable()
    {
        $buffered_jobs = [1 => ['column' => 'value'], 2 => ['field' => 'number']];
        $queued_jobs = [3 => ['hey' => 'there'], 4 => ['goodbye' => 'now']];

        $this->database->insert('buffered_jobs', 1, $buffered_jobs[1]);
        $this->database->insert('buffered_jobs', 2, $buffered_jobs[2]);
        $this->database->insert('queued_jobs', 3, $queued_jobs[3]);
        $this->database->insert('queued_jobs', 4, $queued_jobs[4]);

        $this->assertSame($buffered_jobs, $this->database->getAll('buffered_jobs'));
        $this->assertSame($queued_jobs, $this->database->getAll('queued_jobs'));
    }

    /**
     * @covers ::insert
     * @expectedException Exception
     */
    public function testIdCanBeInsertedIntoTableAtMostOnce()
    {
        $this->database->insert('buffered_jobs', 1, []);
        $this->database->insert('buffered_jobs', 1, []);
    }

    /**
     * @covers ::insert
     */
    public function testSameIdCanBeInsertedIntoDifferentTables()
    {
        $buffered_job = ['a' => 1];
        $queued_job = ['b' => 2];

        $this->database->insert('buffered_jobs', 1, $buffered_job);
        $this->database->insert('queued_jobs', 1, $queued_job);

        $this->assertSame([1 => $buffered_job], $this->database->getAll('buffered_jobs'));
        $this->assertSame([1 => $queued_job], $this->database->getAll('queued_jobs'));
    }

    /**
     * @covers ::delete
     */
    public function testRowCanBeDeletedFromTable()
    {
        $object = new stdClass();
        $this->database->insert('buffered_jobs', 1, ['object' => $object]);
        $row = $this->database->delete('buffered_jobs', 1);

        $this->assertSame([], $this->database->getAll('buffered_jobs'));
        $this->assertSame($object, $row['object']);
    }

    /**
     * @covers ::delete
     * @expectedException Exception
     */
    public function testAnExceptionIsThrownIfANonExistentRowIsDeleted()
    {
        $this->database->delete('buffered_jobs', 1);
    }

    /**
     * @covers ::has
     */
    public function testARowCanBeDetectedInATable()
    {
        $this->assertFalse($this->database->has('buffered_jobs', 1));
        $this->database->insert('buffered_jobs', 1, []);
        $this->assertTrue($this->database->has('buffered_jobs', 1));
    }

    /**
     * @covers ::requestAdvisoryLock
     */
    public function testAnAdvisoryLockCanBeAcquired()
    {
        $this->assertTrue($this->database->requestAdvisoryLock(1, 'test', 'lock'));
        $this->assertFalse($this->database->requestAdvisoryLock(2, 'test', 'lock'));
        $this->assertTrue($this->database->requestAdvisoryLock(2, 'test', 'lock2'));
        $this->assertTrue($this->database->requestAdvisoryLock(1, 'test', 'lock'));
    }

    /**
     * @covers ::requestAdvisoryLock
     * @covers ::releaseAdvisoryLocks
     */
    public function testAnAdvisoryLockCanBeReleased()
    {
        $this->assertTrue($this->database->requestAdvisoryLock(1, 'test', 'lock'));
        $this->assertFalse($this->database->requestAdvisoryLock(2, 'test', 'lock'));
        $this->assertFalse($this->database->requestAdvisoryLock(3, 'test', 'lock'));

        $this->database->releaseAdvisoryLocks(1);
        $this->database->releaseAdvisoryLocks(3);

        $this->assertTrue($this->database->requestAdvisoryLock(2, 'test', 'lock'));
        $this->assertFalse($this->database->requestAdvisoryLock(1, 'test', 'lock'));
        $this->assertFalse($this->database->requestAdvisoryLock(3, 'test', 'lock'));
    }

    /**
     * @covers ::allocateId
     */
    public function testAnIdCanBeAllocated()
    {
        $this->assertSame(1, $this->database->allocateId());
        $this->assertSame(2, $this->database->allocateId());
        $this->assertSame(3, $this->database->allocateId());
    }
}
