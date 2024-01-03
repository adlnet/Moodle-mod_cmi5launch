<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;
use mod_cmi5launch\local\au;
use mod_cmi5launch\local\au_helpers;
/**
 * Tests for cmi5 connectors class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \auHelpers
 * @covers \auHelpers::getAuProperties
 */
class cmi5ConnectorsTest extends TestCase
{
    private $auProperties, $emptyStatement, $mockStatementValues, $mockStatement2, $returnedAUids;

    public $auidForTest;

    protected function setUp(): void
    {
    
    }

    protected function tearDown(): void
    {
        //  $this->example = null;
    }


    // Create course sends data to the player. 
    // Now we dont actually want to talk to player or we will either make a bunch of unreal courses
    // unless we then delete it?
    // or have to make a fake player???
    // Does php have mocks or stubs?
    // They do, and what is actually returned is a massive json encoded string. IT is the return
    // response when a course is created from poayer, but all we care about is a strin return from stub? 
    public function testcmi5launch_create_course()
    {

       // global $auidForTest;
        $helper = new au_helpers();

        // It takes singular ids, so we will iterate through them
        foreach ($auidForTest as $auId) {
            
            $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($auId);
            
            // And the return should be an au object
            $this->assertInstanceOf(au::class, $returnedAu, "Expected retrieved object to be an au object");
        }

        // And if it fails it should fail gracefully
        $badid = 0;
        $returnedAu = $helper->cmi5launch_retrieve_aus_from_db($badid);
        
        // And the return should be a false value
        $this->assertNotTrue($returnedAu, "Expected retrieved object to be false");
        //And it should output this error message
        $this->expectOutputString("<p>Error attempting to get AU data from DB. Check AU id. AU id is: " . $badid . "</p>");

    }   
}