<?php
namespace CCBI;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use CCBI\CCBInterface;
use GuzzleHttp\Client;

/**
 * CCBPublicAPI is the implementation of the public CCB API
 * 
 * @package CCBPublicAPI
 * @author Bryan Orozco <bryanorozco@sandalschurch.com>
 * @author Thomas Renck <thomasrenck@sandalschurch.com>
 * @version 1.0
 * @access public
 * @see https://designccb.s3.amazonaws.com/helpdesk/files/official_docs/api.html
 */

class CCBPublicAPI extends CCBInterface {

    protected $client;

    public function __construct($baseURL = null, $user = null, $pass = null) {
        $this->client = new Client([
            'base_uri' => $baseURL,
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERPWD => $user . ":" . $pass,
                CURLOPT_RETURNTRANSFER => 1,
                CURLINFO_HEADER_OUT => 1
            ]
        ]);
    }

    public function getIndividual($name = null, $phone = null, $email = null, $campus_id = null) {
        //Split name
        $pieces = preg_split('/\s+/', trim($name));

        if(count($pieces) < 1){
            $this->log(Logger::NOTICE, "[getIndividual]", [
                'level' => Logger::NOTICE,
                'message' => "Name didn't explode correctly",
                'email' => $email
            ]);
        }

        $last_name = count($pieces) > 1? end($pieces) : '';
        $first_name = $pieces[0];
        $individual = false;

        $apiResponse = $this->apiCall('individual_search', array(
            "phone"      => $phone,
            "email"      => $email,
            "last_name"  => $last_name,
            "first_name" => $first_name
        ));

        // If we find the user, return the full object, else create the user and return the new object
        // If nothing is found return false
        if($apiResponse !== false && intval($apiResponse->response->individuals['count']) > 0){

            $individual = $apiResponse->response->individuals->individual[0];

            if($campus_id) {

                // Update the user's campus
                try {
                    $update = $this->apiCall('update_individual', 
                        array(
                            "individual_id" => (int) $individual['id']
                        ), array(
                            "campus_id" => $campus_id
                        ), 'post');

                    $individual[0]->campus->attributes()->id  = $campus_id;

                } catch (Exception $e) {
                    $this->log(Logger::NOTICE, "[getIndividual]", [
                        'level' => Logger::NOTICE,
                        'message' => "Could not update user's campus. Error Message: ". $e->getMessage(),
                        'individual' => $individual['id'],
                        'campus_id' => $campus_id
                    ]);
                }
            }

            return $individual;

        } else {
            // create the new record in CCB
            try {
                $individual = $this->createIndividual($name, $phone, $email, $campus_id);
                return $individual;
            } catch (Exception $e) {
                $this->log(Logger::NOTICE, "[getIndividual]", [
                    'level' => Logger::NOTICE,
                    'message' => "Could not create user. Error Message:". $e->getMessage(),
                    'email' => $email
                ]);
                return false;
            }
        }

        return false;
    }

    public function getIndividualById($id) {
        if(!$id){
            return false;
        }

        $apiResponse = $this->apiCall('individual_profile_from_id', array(
            "individual_id" => $id
        ));

        if($apiResponse == false) {
            return false;
        }

        return $apiResponse->response->individuals->individual[0][0];
    }

    public function createIndividual($name, $phone, $email, $campus_id = 1) {
        try {
            //Split name
            $pieces = preg_split('/\s+/', trim($name));

            if(count($pieces) < 1){
                $this->log(Logger::NOTICE, "[createIndividual]", [
                    'level' => Logger::NOTICE,
                    'message' => "Name didn't explode correctly",
                    'email' => $email,
                    'name' => $name
                ]);
            }

            $last_name = count($pieces) > 1? end($pieces) : '(MISSING)';
            $first_name = $pieces[0];
            $individual = false;

            $apiResponse = $this->apiCall('create_individual', array(), array(
                "email" => $email,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "mobile_phone" => $phone,
                "contact_phone" => $phone,
                "campus_id" => $campus_id
            ), 'post');

            if($apiResponse !== false && intval($apiResponse->response->individuals['count']) > 0){
                return $apiResponse->response->individuals->individual[0];
            } else {
                $this->log(Logger::ERROR, "[createIndividual]", [
                    'level' => Logger::ERROR,
                    'message' => "API fail",
                    'name' => $name,
                    'email' => $email
                ]);
                return false;
            }

            return false;

        } catch (Exception $e) {

            $this->log(Logger::ERROR, "[createIndividual]", [
                'level' => Logger::ERROR,
                'message' => $e->getMessage(),
                'name' => $name,
                'email' => $email
            ]);

            return false;
        }
    }

    public function addIndividualToProcessQueue($individual_id, $queue_id, $note = "", $manager_id = null) {

        try {
            
            $params =  array(
                "individual_id" => $individual_id,
                "queue_id" => $queue_id,
                "note" => $note
            );

            if($manager_id !== null && $manager_id !== 0) {
                $params = array_merge($params, array("manager_id" => $manager_id));
            }

            $apiResponse = $this->apiCall('add_individual_to_queue', $params);
            $json = json_encode($apiResponse);
            $jsonResponse = json_decode($json,TRUE);
            
            if($apiResponse !== false && !array_key_exists('errors', $jsonResponse['response'])){
                return $apiResponse->response;
            } else {
                $this->log(Logger::WARNING, "[addIndividualToProcessQueue]", [
                    'level' => Logger::WARNING,
                    'message' => "Error adding to Process Queue",
                    'ccb_error' => $jsonResponse['response']['errors']['error'],
                    'individual_id' => $individual_id,
                    'queue_id' => $queue_id,
                    'note' => $note, 
                    'manager_id' =>$manager_id
                ]);
                return false;
            }

        } catch (Exception $e) {

            $this->log(Logger::ERROR, "[addIndividualToProcessQueue]", [
                'level' => Logger::ERROR,
                'message' => $e->getMessage(),
                'individual_id' => $individual_id,
                'queue_id' => $queue_id,
                'note' => $note, 
                'manager_id' =>$manager_id
            ]);

            return false;
        }

        return $apiResponse;
    }

    protected function apiCall($service_name, $params_array, $post_array = null, $method='get') {
        try {

            $params = (object) $params_array;
            $params->srv = $service_name;

            $client = new Client([
                'base_uri' => Config::APIURL,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERPWD => Config::APIUSER . ":" . Config::APIPASS,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLINFO_HEADER_OUT => 1
                ]
            ]);
    
            $response = null;
    
            if($method == 'post' || $method == 'POST') {
                $response = $this->client->request('POST', "?" . http_build_query($params), [
                    'form_params' => $post_array
                ]);
            } else {
                $response = $this->client->request($method, "?" . http_build_query($params));
            }

            $response_obj = new \SimpleXMLElement($response->getBody());

            return $response_obj;

        } catch(Exception $e){
            $this->log(Logger::CRITICAL, "[apiCall]", [
                'level' => Logger::CRITICAL,
                'message' => $e->getMessage(),
                'method' => $method,
                'response' => $response_raw,
                'params' => json_encode($params_array),
                'post_params' => json_decode($post_array)
            ]);
            return false;
        }
    }

    public function log($level, $message, $context = array()){

        $logger = new Logger('CCB');
        $filename = 'error';

        switch ($level) {
            case Logger::DEBUG:
                $filename = 'debug';
                break;
            case Logger::INFO:
                $filename = 'info';
                break;
            case Logger::NOTICE:
                $filename = 'notice';
                break;
            case Logger::WARNING:
                $filename = 'warning';
                break;
            case Logger::ERROR:
                $filename = 'error';
                break;
            case Logger::CRITICAL:
                $filename = 'critical';
                break;
            case Logger::ALERT:
                $filename = 'alert';
                break;
            case Logger::EMERGENCY:
                $filename = 'emergency';
                break;
            
            default:
                $filename = 'error';
                break;
        }

        $logger->pushHandler(new StreamHandler(__DIR__ .'/../logs/'. $filename .'.log', $level));
        $logger->log($level, $message, $context);

        return;
    }
}