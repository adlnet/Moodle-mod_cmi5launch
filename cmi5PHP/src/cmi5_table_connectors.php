<?php

///Class to hold methods for working with the table for cmi5 connections.
//The table is cmi5launch_player
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
    //Function populate a DB tables
    /* @param mixed $id - the id to update/create record on
    * @param mixed $record - info to create record in DB - object
    *@param $table - the table to be populated
    * @return $newRecord - record that has been created/updated
    */
    public function populateTable($record, $table)
    {

        echo "Table about to be populated! is " . $table;
        echo "Table will be populated with id " . $record->id;
        global $DB;

        //Make sure record doesn't exist before attempting to create
        $check = $this->checkRecord($record->id, $table);
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "HEEEEYYYYYY!!!!! WHAT IS CHECK HERE???" . $check;
        echo "<br>";
        echo "<br>";
        echo "<br>";
        echo "<br>";
        //ok why is it true??? This record SHOULD be new right?


        //If false, record doesn't exist, so import it
        if (!$check) {
            echo '<br>';
            echo '*******************************************';
            echo "<br>";
            $returnedID = $DB->import_record($table, $record, true);
            
            //Ensure it was imported successfully
            if ($returnedID != null || false) {


                $newRecord = $DB->get_record($table, ['id' => $returnedID], '*', IGNORE_MISSING);
                
                //Update newly created record to hold tenant info for sending to cmi5
                $settings = cmi5launch_settings($returnedID);

                echo '<br>';
            echo '**********What is settings here??? '. var_dump($settings) .'*********************************';
            echo "<br>";

                   //Ahh of course! I am trying to assign to wrong thing! This is the bool
                   //that shows if record was imported successfully or not
                   //NOW we need to get the record and assign! -MB
                $newRecord->tenantname = $settings['cmi5launchtenantname'];
                $newRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                echo '<br>';
                echo '###########################################';
                echo "<br>";

                //Return created record
                //??MB Is it enought to just return newrecord? I dont know if retreiving it again is necessary
                return $newRecord = $DB->get_record($table, ['id' => $returnedID], '*', IGNORE_MISSING);
            }
        } else {

                echo '<br>';
                echo '%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%';
                echo "<br>";

                // If it does exist, update it
                $returnedID = $DB->update_record($table, $record, true);
                $updatedRecord = $DB->get_record($table, ['id' => $returnedID], '*', IGNORE_MISSING);
                
                //Ensure it was imported successfully
                if ($returnedID != null || false) {

                    //Update newly created record to hold tenant info for sending to cmi5
                    $settings = cmi5launch_settings($returnedID);
                    $updatedRecord->tenantname = $settings['cmi5launchtenantname'];
                    $updatedRecord->tenanttoken = $settings['cmi5launchtenanttoken'];
                
                    echo '<br>';
                    echo '###########################################';
                    echo "<br>";
    
                    //Return created record
                    return $updatedRecord = $DB->get_record($table, ['id' => $returnedID], '*', IGNORE_MISSING);
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
        //OG COUSE! I need to make this TABLE ionstead of explicitly say table name
        $cmi5launchID = $DB->get_record(
            $table,
            [
                'id' => $id,
            ],
            '*',
        IGNORE_MISSING
        );

        if (!$cmi5launchID) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Creates or updates database table with passed in record on passed in id
     * @param mixed $id - the id to update/create record on
     * @param mixed $record - info to create record in DB - object
     * @return $newRecord - record that has been created/updated
     */
    public function createRecord($id, $record)
    {

        global $DB;

        //Make sure record doesn't exist before attempting to create
        $check = $this->checkRecord($id);

        //If false, record doesn't exist, so import it
        if (!$check) {
            echo '<br>';
            echo '*******************************************';
            echo "<br>";
            $returnedID = $DB->import_record('cmi5launch_player', $record, true);
            if ($returnedID != null || false) {

                echo '<br>';
                echo '###########################################';
                echo "<br>";

                //Return created record
                return $newRecord = $DB->get_record('cmi5launch_player', ['id' => $returnedID], '*', IGNORE_MISSING);

            } else {

                echo '<br>';
                echo '%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%';
                echo "<br>";

                // If it does exist, update it
                $DB->update_record('cmi5launch_player', $record, true);
                //Return created record
                return $newRecord = $DB->get_record('cmi5launch_player', ['id' => $returnedID], '*', IGNORE_MISSING);
            }
        }

    }
}
    ?>