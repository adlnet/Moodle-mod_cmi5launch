<?php
class Au_Helpers {
 
  public function getRetrieveAUs() {
	return [$this, 'retrieveAUs'];
   }
   public function getCreateAUs() {
	return [$this, 'createAUs'];
   }

   public function getUpdateVerb() {
	return [$this, 'updateVerbs'];
   }

	function updateVerbs($aus, $verbList)
	{

		//This gets the aus separate and then compares to update them
		//Their verbs and such
		foreach ($aus as $key) {

			//Retrieve individual AU as array
			$au = (array) ($aus[$key]);

		}
	}
		function retrieveAus($returnedInfo)
		{
			
			//The results come back as nested array under more then statments. We only want statements, and we want them separated into unique statments
			$resultChunked = array_chunk($returnedInfo["metadata"]["aus"], 1,);
			//The info has now been broken into chunks
			//Return the AU with the chuncks, but start at 0 because array_chunk returns an array, all will be 
			//nestled under 0
		
			//$newAus = $this->createAUs($resultChunked[0]);
			
			//return $newAus;
			//it was returning aus then encoding them then creating them again
		return $resultChunked;
		}
		/**
		 *So it should be fed an array of statements that then assigns the values to 
		 *several aus, and then returns them as au objects!
		 * @param mixed $auStatements
		 * @return array<au>
		 */
		function createAUs($auStatements, $record)
		{
			//Needs to return our new AU objects
			$newAus = array();

			//for ($i = 0; $i < count($auStatements); $i++) {
			foreach($auStatements as $int => $info){
			
				//The aus come back decoded from DB nestled in an array, so they are the first key,
				//which is '0'
				$statement = $info[0];
			
				//Maybe just combine 45 and 48? TODO
				$au = new au($statement);
			saveAUs($au, $record);
				//Save the new AUs to DB? 

				//assign the newly created au to the return array
				$newAus[] = $au;
			}

			//Return our new list of AU!
			return $newAus;
		}
	}

	    /**
     * //can we take an AU and just if this matches then this matches and save to table?
     * @param mixed $id - base id to make sure record is saved to correct actor
     * @param mixed $urlInfo - urlInfo that was returned from cmi5 such as sessionId, launchWindow, URL
     * @param mixed $retUrl - Tenants return url for when course window closes.
     * @return mixed
     */
    function saveAUs($auObject, $record)
    {
        global $DB, $CFG, $cmi5launch;


        $table = "cmi5launch_aus";
       // $settings = cmi5launch_settings($id);

       // $homepage = $settings['cmi5launchcustomacchp'];

        //$regid = $registrationid;
        //$returnUrl = $retUrl;

		//We can check for no dupes by AUindex because its numerical, and even if more AUs are added on they won't replacE!!!
	$auindex = $auObject->auIndex;

        //Make sure record doesn't exist before attempting to create
        $check = $DB->get_record($table, ['auindex' => $auindex,], '*', IGNORE_MISSING);

        //If false, record doesn't exist, so create  it
        if (!$check) {

            //Retrieve user settings to apply to newly created record
            $settings = cmi5launch_settings($record->id);
            //These will already exist!
			//$record->tenantname = $settings['cmi5launchtenantname'];
            //$record->courseid = $settings['cmi5launchtenanttoken'];
            //$record->cmi5playerurl = $settings['cmi5launchplayerurl'];
            //$record->sessionid = $urlInfo['id'];
			
			foreach($auObject as $key => $value){
    
				//Key will be property name
				//value will be property value
				
				//if($auObject->key == );
				//Wait if shouldnt be needed because we are making the new record	
				//BUT a diff if may work to not overwrite shtuff
				if($record->$key == null){
					//If its null it doesn't exist yet, so won't overwrite (for instance record id)
				$record->$key = $value;
				} 
			}	
			
			//$record->launchmethod = $urlInfo['launchMethod'];
           // $record->launchurl = $urlInfo['url'];
           //$record->returnUrl = $returnUrl;
            //Assign new regid
            //$record->registrationid = $regid;
            //$record->returnurl = $returnUrl;
            //$record->homepage = $homepage;

            $DB->import_record($table, $record, true);
         
        } else {
            // If it does exist, update it
			//Wait, this should NEVER exist
/*
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
  */
			}
        }

?>