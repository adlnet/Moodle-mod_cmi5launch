<?php

namespace Tests\Unit;

use Au;
use Tests\TestCase;

/**
 * Class AuTest.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \Au
 */
final class AuTest extends TestCase
{
    private Au $au;

    private mixed $statement;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->statement = null;
        $this->au = new Au($this->statement);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->au);
        unset($this->statement);
    }

    public function testSet_name(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }

    public function testGet_name(): void
    {
        /** @todo This test is incomplete. */
        $this->markTestIncomplete();
    }
}
