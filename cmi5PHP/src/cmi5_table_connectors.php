<?php

///Class to hold methods for working with table cmi5launch_playe for cmi5 connections.
// @property -
// - MB 
class cmi5Tables
{

    //To make a new instance and hold variables??
    //  global $returnedToken = $connectors->$GLOBALS.$bearerToken;

    public function getCheckRecord()
    {
        return [$this, 'checkRecord'];
    }
    public function getPopulateTable()
    {
        return [$this, 'populateTable'];
    }
    public function getSaveURL()
    {
        return [$this, 'saveURL'];
    }

    /**
	* //Function to save URL info to it's table
	* @param mixed $id - base id to make sure record is saved to correct actor
	* @param mixed $urlInfo - ur;lInfo that was returned from cmi5 such as sessionId, launchWindow, URL
	* @return mixed
	*/
    public function saveURL($id, $urlInfo)
    {
        global $DB;

		$table = "cmi5launch_player";
   
          //Make sure record doesn't exist before attempting to create
          $check = $this->checkRecord($id, $table);

		//Retrieve actor record, this enables correct actor info for URL storage
		$record = $DB->get_record("cmi5launch", array('id' => $id));
	

          //If false, record doesn't exist, so import it
          if (!$check) {
  
			echo "<br>";
			echo "Record doesn't exist";
			echo "<br>";
  
              //import_record returns a true/false value based on if record created successfully  
              $recordImported = $DB->import_record($table, $record, true);
  
              //Ensure it was imported successfully
              if ($recordImported != null || false) {
  
                  echo"<br>";
                  echo "S if record is 1 that means it should com here bein neither false or ull";
                  echo"<br>";
  
                  //Retrieve newly created record
                  $newRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
   
                  //////////////////////////////////////////////////////////////////////////
                  //Retrieve user settings to apply to newly created record
                  $settings = cmi5launch_settings($record->id);
                  $newRecord->tenantname = $settings['cmi5launchtenantname'];
                  $newRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                  $newRecord->cmi5playerurl = $settings['cmi5launchplayerurl'];
                  $newRecord->cmi5playerport = $settings['cmi5launchplayerport'];
     			$newRecord->sessionid = $urlInfo['id'];
     			$newRecord->launchmethod = $urlInfo['launchMethod'];
     			$newRecord->launchurl = $urlInfo['url'];
   

                  //Update record in table with newly retrieved tenant data
                  //Ok, here is were the trouble isits not updatin table caus et says the 
                  //courseid is dup, quesion is, do we want that? Maybe we have MakeCOurse ni wron spo, althouh if thye pdte
                  //the course we would want itto have new id...hrmm
                  $DB->update_record($table, $newRecord, true);
                  echo"<br>";
                  echo "Are we ettin here Is this where it's haning up?? ";
                  echo"<br>";
  
                  //new way to make url
                  $url = "http://" . $newRecord->cmi5playerurl . $newRecord->cmi5playerport . "/api/v1/course/12/launch-url/0";
  
                  //Return record from updated table
                  return $newRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
              }
          }   else {
  
              echo "<br>";
              echo "Record DOES existand needs to be updated";
              echo "<br>";
                  // If it does exist, update it
                  //update_record returns true/false depending on success
                  $recordUpdate = $DB->update_record($table, $record, true);
  
                  //Ensure it was updated successfully
                  if ($recordUpdate != null || false) {
  
                      //Retrieve the updated record
                      $updatedRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
  
                      	//Retrieve user settings to apply to newly created record
                      	$settings = cmi5launch_settings($id);
                      	$updatedRecord->tenantname = $settings['cmi5launchtenantname'];
                      	$updatedRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                      	$updatedRecord->sessionid = $urlInfo['id'];
     				$updatedRecord->launchmethod = $urlInfo['launchMethod'];
     				$updatedRecord->launchurl = $urlInfo['url'];

                      //Update record in table with newly retrieved tenant data
                      $DB->update_record($table, $updatedRecord, true);
  
                      //Return record from updated table
                      return $updatedRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
                  }
              }
   
   
    }



    //////
    //Function to populate a DB table
    /* @param mixed $id - the id to update/create record on
    * @param object $record - info to create record in DB 
    *@param $table - the table to be populated
    * @return $newRecord/updatedRecord - record that has been created/updated
    */
    //If we chnage how populate table works it'll be good, it doesn't
	//need a record really cause all it takes is the record id? So just pass an id? 
    //NUT NO, it needs record hmm
    public function populateTable($record, $table)
    {
        global $DB;
        //Id to create/update record
        $id = $record->courseid;

        //TODO - I am hardcoding these for now, want to check with others as to best way to collect this info
        //such as from cmi5 mod install page, or cmi5 course uploadpage??? -MB
        $homepage ="http://myLMSexample.com";
        $returnUrl="http://127.0.0.1:63398.com";
        $url= "http://localhost:63398/api/v1/course/12/launch-url/0" ;
        
        //Make sure record doesn't exist before attempting to create
        $check = $this->checkRecord($id, $table);

        echo "<br>";
        echo "O s the problem it is ot findin it if it DOES exist? Check equals" . $check;
        echo "<br>";

        //If false, record doesn't exist, so import it
        if (!$check) {

            echo "<br>";
        echo "Record doesn't exist";
        echo "<br>";

            //import_record returns a true/false value based on if record created successfully  
            $recordImported = $DB->import_record($table, $record, true);

            echo "<br>";
            echo "WHAT IS RECORDIMPORTED HERE?" . $recordImported;
            echo"<br>";

            //Ok, so now what

            //Ensure it was imported successfully
            if ($recordImported != null || false) {

                echo"<br>";
                echo "S if record is 1 that means it should com here bein neither false or ull";
                echo"<br>";

                //Retrieve newly created record
                //rees the rolem, it's lookin for the new id
                $newRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
 
                //////////////////////////////////////////////////////////////////////////
                //Retrieve user settings to apply to newly created record
                //Maybe its here? ITs lokin for reg id? 
                $settings = cmi5launch_settings($record->id);
                $newRecord->tenantname = $settings['cmi5launchtenantname'];
                $newRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                $newRecord->cmi5playerurl = $settings['cmi5launchplayerurl'];
                $newRecord->cmi5playerport = $settings['cmi5launchplayerport'];
                $newRecord->homepage = $homepage;
                $newRecord->returnurl = $returnUrl;
                $newRecord->requesturl = $url;

                //Update record in table with newly retrieved tenant data
                //Ok, here is were the trouble isits not updatin table caus et says the 
                //courseid is dup, quesion is, do we want that? Maybe we have MakeCOurse ni wron spo, althouh if thye pdte
                //the course we would want itto have new id...hrmm
                $DB->update_record($table, $newRecord, true);
                echo"<br>";
                echo "Are we ettin here Is this where it's haning up?? ";
                echo"<br>";

                //new way to make url
                $url = "http://" . $newRecord->cmi5playerurl . $newRecord->cmi5playerport . "/api/v1/course/12/launch-url/0";

                //Return record from updated table
                return $newRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
            }
        }   else {

            echo "<br>";
            echo "Record DOES existand needs to be updated";
            echo "<br>";
                // If it does exist, update it
                //update_record returns true/false depending on success
                $recordUpdate = $DB->update_record($table, $record, true);

                //Ensure it was updated successfully
                if ($recordUpdate != null || false) {

                    //Retrieve the updated record
                    $updatedRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);

                    //Retrieve user settings to apply to newly created record
                    $settings = cmi5launch_settings($id);
                    $updatedRecord->tenantname = $settings['cmi5launchtenantname'];
                    $updatedRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                    $updatedRecord->homepage = $homepage;
                    $updatedRecord->returnurl = $returnUrl;
                    $updatedRecord->requesturl = $url;

                    //Update record in table with newly retrieved tenant data
                    $DB->update_record($table, $updatedRecord, true);

                    //Return record from updated table
                    return $updatedRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
                }
            }
        
    }

    /**
     * Summary of checkRecord
     * Checks if record exists, returns true if it does, else returns false
     * @param mixed $id - the id to search record by
     * @param mixed $table - the table to search
     * @return true|false
     */
    public function checkRecord($id, $table)
    {
        global $DB;

        //Attempt to get record
        $cmi5launchID = $DB->get_record($table, ['id' => $id,], '*', IGNORE_MISSING);

        if (!$cmi5launchID) {
            return false;
        } else {
            return true;
        }
    }
}
    ?>