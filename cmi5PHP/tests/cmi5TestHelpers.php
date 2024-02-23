<?php
namespace cmi5Test;

use PHPUnit\Framework\TestCase;


/**
 * Tests for cmi5 connectors class.
 *
 * @copyright 2023 Megan Bohland
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \auHelpers
 * @covers \auHelpers::getAuProperties
 */
class cmi5TestHelpers 
{
  private $auProperties, $emptyStatement, $mockStatementValues, $mockStatement2, $returnedAUids;


  public function get_file_get_contents() {
    return [$this, 'file_get_contents'];
  }
  
  public $auidForTest;

    //Return something to test intead of file
   public function file_get_contents($url, $false, $context)
    {
      return 'Test';
    }

   public function cmi5launch_settings($id)
    {
      if ($id == 0){
        return array ('cmi5launchplayerurl'=>'https://cmi5launchplayerurl.com');
      }
    }
  

  

}
?>
