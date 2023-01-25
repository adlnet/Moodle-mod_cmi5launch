<?php

///Class to hold methods for working with tables for cmi5 connections.
// - MB 
class cmi5Tables
{
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
     * @param mixed $urlInfo - urlInfo that was returned from cmi5 such as sessionId, launchWindow, URL
     * @return mixed
     */
    public function saveURL($id, $urlInfo)
    {
        global $DB;

        $table = "cmi5launch_player";

        //Retrieve URL information from $urlInfo object
        $urlDecoded = json_decode($urlInfo, true);
        $url = $urlDecoded['url'];
        parse_str($url, $urlInfo);
        $regid = $urlInfo['registration'];

        //Retrieve actor record, this enables correct actor info for URL storage
        $record = $DB->get_record("cmi5launch", array('id' => $id));

        //Make sure record doesn't exist before attempting to create
        $check = $DB->get_record($table, ['registrationid' => $regid,], '*', IGNORE_MISSING);

        //If false, record doesn't exist, so create  it
        if (!$check) {

            //Retrieve user settings to apply to newly created record
            $settings = cmi5launch_settings($record->id);
            $record->tenantname = $settings['cmi5launchtenantname'];
            $record->tenanttoken = $settings['cmi5launchtenanttoken'];
            $record->cmi5playerurl = $settings['cmi5launchplayerurl'];
            $record->cmi5playerport = $settings['cmi5launchplayerport'];
            $record->sessionid = $urlDecoded['id'];
            $record->launchmethod = $urlDecoded['launchMethod'];
            $record->launchurl = $urlDecoded['url'];
            //Assign new regid
            $record->registrationid = $regid;

            //new way to make url
            $url = "http://" . $record->cmi5playerurl . $record->cmi5playerport . "/api/v1/course/".$record->cmi5playerport."/launch-url/0";
            //Assign new url
            $record->requrl = $url;

            $DB->import_record($table, $record, true);


        } else {
            // If it does exist, update it

                //Retrieve user settings to apply to newly created record
                $settings = cmi5launch_settings($id);
                $record->tenantname = $settings['cmi5launchtenantname'];
                $record->tenanttoken = $settings['cmi5launchtenanttoken'];
                $record->sessionid = $urlDecoded['id'];
                $record->launchmethod = $urlDecoded['launchMethod'];
                $record->launchurl = $urlDecoded['url'];

                //Update record in table with newly retrieved tenant data
                $DB->update_record($table, $record, true);

                //Return record from updated table
                //return $updatedRecord = $DB->get_record($table, ['courseid' => $id], '*', IGNORE_MISSING);
                echo "<br>";
                echo "Record updated, URL is saved, regid is  " . $regid;
                echo "<br>";

            }
        }

    //////
    //Function to populate a DB table
    /* @param object $record - record object to be passed in and added to table
    *  @param $table - the table to be populated
    *  @return $newRecord/updatedRecord - record that has been created/updated
    *////////    
    public function populateTable($record, $table)
    {
        global $DB;
        //Id to create/update record
        $id = $record->id;

        //Make sure record doesn't exist before attempting to create
        $check = $DB->get_record($table, ['id' => $id,], '*', IGNORE_MISSING);

        //If false, record doesn't exist, so import it
        if (!$check) {

            //import_record returns a true/false value based on if record created successfully  
            $recordImported = $DB->import_record($table, $record, true);

            //Ensure it was imported successfully
            if ($recordImported != null || false) {

                //Retrieve newly created record
                $newRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);

                //Update record in table with newly retrieved tenant data
                $DB->update_record($table, $newRecord, true);

                //Return record from updated table
//                return $newRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);
            }
        } else {

            // If it does exist, update it
            //update_record returns true/false depending on success
            $recordUpdate = $DB->update_record($table, $record, true);

            //Ensure it was updated successfully
            if ($recordUpdate != null || false) {

                //Retrieve the updated record
                $updatedRecord = $DB->get_record($table, ['id' => $id], '*', IGNORE_MISSING);

                //Update record in table with newly retrieved tenant data
                $DB->update_record($table, $updatedRecord, true);
            }
        }

    }
}
    ?>