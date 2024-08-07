<?php
namespace cmi5Test;

use mod_cmi5launch\local\nullException;
use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\au;
use mod_cmi5launch\local\errorover;

require_once(dirname(dirname(dirname(__FILE__))) . 'cmi5lPHP\tests\errorover.php');
/**
 * Tests for AuHelpers class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \errorover
 * @covers \errorover::array_chunk_warning
 */
class erroroverTest extends TestCase
{

    /**
     * Test the array_chunk_warning function. Which we use to override the php error
     * // for arrays and make throw exceptions for better null handling. 
     */
    public function testcmi5launch_retrieve_aus_exception()
    {   
        // Catch the exception
        $this->expectException(nullException::class);

        //can we just call it?
     // is this necessary?   errorover::array_chunk_warning(E_WARNING, "This is a test error", "testfile.php", 1);


    }
}