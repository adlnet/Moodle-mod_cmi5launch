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
			$resultChunked = array_chunk($returnedInfo["metadata"]["aus"], 1);
			//The info has now been broken into chunks
			//Return the AU with chunks
			$newAus = $this->createAUs($resultChunked);

			return $newAus;
		}

		function createAUs($auStatements)
		{

			//So it should be fed an array of statements that then assigns the values to 
			//several aus, and then returns the au objects! (array of objects)

			//MNeeds to return our new AU objects
			$newAus = array();

			//First get length of array passed in...wait not needed with a foreach?
			for ($i = 0; $i < count($auStatements); $i++) {
				//No it IS needed! beacuase we want to do this for each statment
				$statement = $auStatements[$i];

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