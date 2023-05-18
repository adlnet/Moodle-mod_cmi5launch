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
        return [$this, 'saveURLs'];
    }

    public function getSaveAuURLs()
    {
        return [$this, 'saveAuURLs'];
    }


    /**
     * //Function to save URL info to it's table
     * @param mixed $id - base id to make sure record is saved to correct actor
     * @param mixed $urlInfo - urlInfo that was returned from cmi5 such as sessionId, launchWindow, URL
     * @param mixed $retUrl - Tenants return url for when course window closes.
    * @param mixed $homeUrl - Tenants url for homepage.
     * @return mixed
     */
    public function saveAuURLs($id, $urlInfo, $retUrl, $homeUrl)
    {
        global $DB;
        
		$auObject = new stdClass();
		$auObject->au;
		$auObject->lms_id; 
		$auObject->au_id;
		$auObject->url;
		$auObject->launchMethod;
		$auObject->completedOrPassed;


        $table = "cmi5launch_player";

        //Retrieve URL information from $urlInfo object
        $urlDecoded = json_decode($urlInfo, true);
        $url = $urlDecoded['url'];
        parse_str($url, $urlInfo);
        $regid = $urlInfo['registration'];
        $returnUrl = $retUrl;
        $homepage = $homeUrl;
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
            $record->sessionid = $urlDecoded['id'];
            $record->launchmethod = $urlDecoded['launchMethod'];
            $record->launchurl = $urlDecoded['url'];
            //Assign new regid
            $record->registrationid = $regid;
            $record->returnurl = $returnUrl;
            $record->homepage = $homepage;

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
                $record->returnUrl = $returnUrl;
                $record->homepage = $homepage;
                //Update record in table with newly retrieved tenant data
                $DB->update_record($table, $record, true);

            }
        }

    /**
     * //Function to save URL info to it's table
     * @param mixed $id - base id to make sure record is saved to correct actor
     * @param mixed $urlInfo - urlInfo that was returned from cmi5 such as sessionId, launchWindow, URL
     * @param mixed $retUrl - Tenants return url for when course window closes.
     * @return mixed
     */
    public function saveURLs($id, $urlInfo, $retUrl, $registrationid)
    {
        global $DB;


        $table = "cmi5launch_player";
        $settings = cmi5launch_settings($id);

        $homepage = $settings['cmi5launchcustomacchp'];

        $regid = $registrationid;
        $returnUrl = $retUrl;

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
            $record->sessionid = $urlInfo['id'];
            $record->launchmethod = $urlInfo['launchMethod'];
            $record->launchurl = $urlInfo['url'];
           $record->returnUrl = $returnUrl;
            //Assign new regid
            $record->registrationid = $regid;
            $record->returnurl = $returnUrl;
            $record->homepage = $homepage;

            $DB->import_record($table, $record, true);
         
        } else {
            // If it does exist, update it

                //Retrieve user settings to apply to newly created record
                $settings = cmi5launch_settings($id);
                $record->tenantname = $settings['cmi5launchtenantname'];
                $record->tenanttoken = $settings['cmi5launchtenanttoken'];
                $record->sessionid = $urlInfo['id'];
                $record->launchmethod = $urlInfo['launchMethod'];
                $record->launchurl = $urlInfo['url'];
                $record->returnUrl = $returnUrl;
                $record->homepage = $homepage;

			 //Update record in table with newly retrieved tenant data
                $DB->update_record($table, $record, true);
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
        echo"<br>";
        echo "$$$$$$$$$$$$$$$$$$$$$ id in populate table is" . $id;
        echo"<br>";
        //Make sure record doesn't exist before attempting to create
       // $check = $DB->get_record($table, ['id' => $id,], '*', IGNORE_MISSING);
		$check = $DB->record_exists($table, ['id' => $id], '*', IGNORE_MISSING);

        //If false, record doesn't exist, so import it
        if (!$check) {
            echo"<br>";
            echo "Doesn't exist";

            $DB->import_record($table, $record, true);

        } else {
            echo"<br>";
            echo "does exist";

            // If it does exist, update it
            $DB->update_record($table, $record, true);
        }
    }
}
    ?>