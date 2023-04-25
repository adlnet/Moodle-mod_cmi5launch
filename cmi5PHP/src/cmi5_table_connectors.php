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

    public function getRetrieveAus()
    {
        return [$this, 'retrieveAus'];
    }

    public function retrieveAus($id, $retUrl){
		global $DB;

		//Retrieve actor record, this enables correct actor info for URL storage
		$record = $DB->get_record("cmi5launch", array('id' => $id));
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
        //If I make it here can everyone use it?
        //MB
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
    * @param mixed $homeUrl - Tenants url for homepage.
     * @return mixed
     */
    public function saveURLs($id, $urlInfo, $retUrl, $registrationid/* $homeUrl*/)
    {
        //Ok, so it is used to urlInfo being the launch response from get retreivurl
//can we get it? and return it to view from retreive url        
        global $DB;

        echo "<br>";
echo "Ok, what is return url heree then??";
var_dump($retUrl);
echo "<br>";

        $table = "cmi5launch_player";
        $settings = cmi5launch_settings($id);

        $homepage = $settings['cmi5launchcustomacchp'];
        //Retrieve URL information from $urlInfo object
       //// $urlDecoded = json_decode($urlInfo, true);
       //$url = $urlInfo['url'];
        
        //Parse url for info such as regid
       //// parse_str($url, $urlInfo);
        

        $regid = $registrationid;
        $returnUrl = $retUrl;
        //$homepage = $homeUrl;

        //Retrieve actor record, this enables correct actor info for URL storage
        $record = $DB->get_record("cmi5launch", array('id' => $id));

        //get regid this way?
       /// $regid = $record->registrationid;

        //Make sure record doesn't exist before attempting to create
        $check = $DB->get_record($table, ['registrationid' => $regid,], '*', IGNORE_MISSING);

        //If false, record doesn't exist, so create  it
        if (!$check) {

            echo "<br>";
            echo "Ok, what is urlinfo then??";
            var_dump($urlInfo);
            echo "<br>";
            //Ok, so it 
            //Retrieve user settings to apply to newly created record
            $settings = cmi5launch_settings($record->id);
            $record->tenantname = $settings['cmi5launchtenantname'];
            $record->tenanttoken = $settings['cmi5launchtenanttoken'];
            $record->cmi5playerurl = $settings['cmi5launchplayerurl'];
           //LEt change urldecod     to urlinfo
            $record->sessionid = $urlInfo['id'];
            $record->launchmethod = $urlInfo['launchMethod'];
            $record->launchurl = $urlInfo['url'];
           /////////////////
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
               //LEts change urldecoded to urlinfo
                $record->sessionid = $urlInfo['id'];
                $record->launchmethod = $urlInfo['launchMethod'];
                $record->launchurl = $urlInfo['url'];
                ///////////////////////
                
               // $record->registrationid = $regid;


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
        
        //Make sure record doesn't exist before attempting to create
        $check = $DB->get_record($table, ['id' => $id,], '*', IGNORE_MISSING);

        //If false, record doesn't exist, so import it
        if (!$check) {

            $DB->import_record($table, $record, true);

        } else {

            // If it does exist, update it
            $DB->update_record($table, $record, true);

        }

    }
}
    ?>