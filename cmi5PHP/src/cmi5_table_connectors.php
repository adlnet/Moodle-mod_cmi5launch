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


    //////
    //Function to populate a DB table
    /* @param mixed $id - the id to update/create record on
    * @param object $record - info to create record in DB 
    *@param $table - the table to be populated
    * @return $newRecord/updatedRecord - record that has been created/updated
    */
    public function populateTable($record, $table)
    {
        global $DB;
        //Id to create/update record
        $id = $record->id;

        //TODO - I am hardcoding these for now, want to check with others as to best way to collect this info
        //such as from cmi5 mod install page, or cmi5 course uploadpage??? -MB
        $homepage ="http://myLMSexample.com";
        $returnUrl="http://127.0.0.1:63398.com";
        $url= "http://localhost:63398/api/v1/course/12/launch-url/0" ;

        //Make sure record doesn't exist before attempting to create
        $check = $this->checkRecord($id, $table);

        //If false, record doesn't exist, so import it
        if (!$check) {

            //import_record returns a true/false value based on if record created successfully  
            $recordImported = $DB->import_record($table, $record, true);

            //Ensure it was imported successfully
            if ($recordImported != null || false) {

                //Retrieve newly created record
                $newRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);
 
                //////////////////////////////////////////////////////////////////////////
                //Retrieve user settings to apply to newly created record
                $settings = cmi5launch_settings($id);
                $newRecord->tenantname = $settings['cmi5launchtenantname'];
                $newRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                $newRecord->homepage = $homepage;
                $newRecord->returnurl = $returnUrl;
                $newRecord->requesturl = $url;

                //Update record in table with newly retrieved tenant data
                $DB->update_record($table, $newRecord, true);


                //Return record from updated table
                return $newRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);
            }
        }   else {
                // If it does exist, update it
                //update_record returns true/false depending on success
                $recordUpdate = $DB->update_record($table, $record, true);

                //Ensure it was updated successfully
                if ($recordUpdate != null || false) {

                    //Retrieve the updated record
                    $updatedRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);

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
                    return $updatedRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);
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