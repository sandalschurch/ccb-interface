<?php
namespace CCBI;

/**
 * CCBInterface is an interface that allows child classes to write their own 
 * implementation depending on the strategy we want to use.
 * @package CCBInterface
 * @author Bryan Orozco <bryanorozco@sandalschurch.com>
 * @author Thomas Renck <thomasrenck@sandalschurch.com>
 * @version 1.0
 * @access public
 * @see https://www.php.net/manual/en/language.oop5.abstract.php
 * @see https://sourcemaking.com/design_patterns/strategy
 */

abstract class CCBInterface {

    /**
     * Returns the individual's CCB Object.
     * If individual is not found, it will create a profile in CCB return the CCB Object
     * 
     * @param string    $name the first and last name of the user
     * @param string    $phone the user's phone number
     * @param string    $email the user's email
     * @param int       $campus_id the CCB campus ID, @link http://sandalschurch.com/api/campus_info
     * @return object   the user information from CCB
     */
    abstract protected function getIndividual($name, $phone, $email, $campus_id = null);
   
    /**
     * Returns the individual's CCB Object.
     * 
     * @param int    $id user's ccb id
     */
    abstract protected function getIndividualById($id);

    /**
     * Creates a new user record in CCB and returns the user object
     * 
     * @param string    $name the first and last name of the user
     * @param string    $phone the user's phone number
     * @param string    $email the user's email
     * @param int       $campus_id the CCB campus ID, @link http://sandalschurch.com/api/campus_info, defualt is Hunter Park (ID: 1)
     * @return object   the user information from CCB
     */
    abstract protected function createIndividual($name, $phone, $email, $campus_id = 1);

    /**
     * Add and an individual to a CCB Process Queue 
     * 
     * @param int       $individual_id the user's CCB ID
     * @param int       $queue_id the CCB Queue ID
     * @param string    $note a note that will show up in the queue for this individual
     * @param int       $manager_id the CCB ID of a queue manager who will get this user auto assigned them
     * @param int       $campus_id the CCB campus ID, @link http://sandalschurch.com/api/campus_info
     * @return bool     If successful returns true else false
     */
    abstract protected function addIndividualToProcessQueue($individual_id, $queue_id, $note = "", $manager_id = null);

    /**
     * Make an api call to CCB
     * 
     * @see https://designccb.s3.amazonaws.com/helpdesk/files/official_docs/api.html
     * 
     * @param string    $service_name the Service Name of the api call we cant to perform
     * @param array     $params_array optional parameters for the request
     * @param string    $method the HTTP request method
     * @return
     */
    abstract protected function apiCall($service_name, $params_array, $post_array = null, $method='get');

    /**
     * Log errors
     * to implement the PSR-3 interface, we use monolog
     * 
     * @see https://www.php-fig.org/psr/psr-3/
     * 
     * @param string    $level log level @see https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels
     * @param array     $message error message
     * @param string    $context contex array @see https://www.php-fig.org/psr/psr-3/#13-context
     * @return
     */
    abstract protected function log($level, $message, $context = array());
}