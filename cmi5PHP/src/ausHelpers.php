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

		//IS THIS being called in TWO places? Might be the problem!!
		//MB
		function createAUs($auStatements)
		{
			//So it should be fed an array of statements that then assigns the values to 
			//several aus, and then returns the au objects! (array of objects)

			//Needs to return our new AU objects
			$newAus = array();

			//for ($i = 0; $i < count($auStatements); $i++) {
			foreach($auStatements as $int => $info){
			
				//The aus come back decoded from DB nestled in an array, so they are the first key,
				//which is '0'
				$statement = $info[0];
			
				//Maybe just combine 45 and 48? TODO
				$au = new au($statement);
				
				//assign the newly created au to the return array
				$newAus[] = $au;

			}

			//Return our new list of AU!
			return $newAus;
		}


	}

?>