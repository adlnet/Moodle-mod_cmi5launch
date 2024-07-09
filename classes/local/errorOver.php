<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lets see if this works. making an ovrride error class, or several
 *
 * @copyright  2023 Megan Bohland
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_cmi5launch\local;

/**
 * An exception handler to use in AU cases when many different exceptions for data errors may be thrown. 
 * @param mixed $errno
 * @param mixed $errstr
 * @param mixed $errfile
 * @param mixed $errline
 * @throws \mod_cmi5launch\local\nullException
 * @return never
 */
function exception_au(\Throwable $exception)
{
    // echo"Error stirn ---  $errstr";
    // echo"Error number ---  $errno";
    echo " EHAT?";
    // Maybe we can construct the new errors here. This would allow the error personalization? And keep main code clean

    throw new fieldException('Error OVER: ' . $exception->getMessage(), 0);
    //  exit;
}
    function array_chunk_warning($errno, $errstr, $errfile, $errline)
    {
        // echo"Error stirn ---  $errstr";
        // echo"Error number ---  $errno";

        // Maybe we can construct the new errors here. This would allow the error personalization? And keep main code clean

        throw new nullException('Cannot parse array. Error: ' . $errstr, 0);
        //  exit;
    }

    /// Ok, this i a different error handler
    function sifting_data_warning($errno, $errstr, $errfile, $errline)
    {
       //  echo"Error stirn ---  $errstr";
      //   echo"Error number ---  $errno";
//echo"Error errfile ---  $errfile";
      //   echo"Error errline ---  $errline";
        // Maybe we can construct the new errors here. This would allow the error personalization? And keep main code clean

        throw new fieldException('Error: ' . $errstr, 0);
        //  exit;
    }

/**
 * Define a custom exception class, this will make pour tests meaningful
 * from php webpage: "Custom exception classes can allow you to write tests that prove your exceptions
    *  are meaningful. Usually testing exceptions, you either assert the message equals
*something in which case you can't change the message format without refactoring,
*or not make any assertions at all in which case you can get misleading messages
*later down the line. Especially if your $e->getMessage is something complicated
*like a var_dump'ed context array."
 */
class nullException extends \Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object (what is returned with echo)
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() {
        echo "A custom function for this type of exception\n";
    }
}
/**
 * Define a custom exception class, this will make pour tests meaningful
 * from php webpage: "Custom exception classes can allow you to write tests that prove your exceptions
    *  are meaningful. Usually testing exceptions, you either assert the message equals
*something in which case you can't change the message format without refactoring,
*or not make any assertions at all in which case you can get misleading messages
*later down the line. Especially if your $e->getMessage is something complicated
*like a var_dump'ed context array."
 */
class missingException extends \Exception
{
    // Redefine the exception so message isn't optional
    // I want an exception that takkkes what is missing and adds it to messsssage? 
    // Is this possivlbe? 
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

   
    // custom string representation of object (what is returned with echo)
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        // maybe here?
    }

    public function customFunction() {
        echo "  This error to string            :";
      //  $this->getTraceAsString();
    }
}
/**
 * Define a custom exception class, this will make pour tests meaningful
 * from php webpage: "Custom exception classes can allow you to write tests that prove your exceptions
    *  are meaningful. Usually testing exceptions, you either assert the message equals
*something in which case you can't change the message format without refactoring,
*or not make any assertions at all in which case you can get misleading messages
*later down the line. Especially if your $e->getMessage is something complicated
*like a var_dump'ed context array."
 */
class fieldException extends \Exception
{
    // Redefine the exception so message isn't optional
    // I want an exception that takkkes what is missing and adds it to messsssage? 
    // Is this possivlbe? 
    public function __construct($message, $code = 0, Throwable $previous = null) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

   
    // custom string representation of object (what is returned with echo)
    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
        // maybe here?
    }

    public function customFunction() {
        echo "  This error to string            :";
      //  $this->getTraceAsString();
    }
}