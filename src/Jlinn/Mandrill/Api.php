<?php
/**
 * User: Joe Linn
 * Date: 9/12/13
 * Time: 4:46 PM
 */

namespace Jlinn\Mandrill;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\ServerException;
use Jlinn\Mandrill\Exception\APIException;

abstract class Api{
    const BASE_URL = 'https://mandrillapp.com/api/1.0/';

    /**
     * @var string Mandrill API key
     */
    protected $apiKey;

    /**
     * @var string Used to store an alternative base url for the API. Typically used only for testing.
     */
    protected $baseUrl;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey){
        $this->apiKey = $apiKey;
    }

    /**
     * Set an alternative base url for the API.  Typically used for testing purposes.
     * @param string $url
     */
    public function setBaseUrl($url){
        $this->baseUrl = $url;
    }

    /**
     * Set and Handlerstack for testing purposes.
     * @param string $url
     */
    public function setHandler(HandlerStack $handler){
        $this->handler = $handler;
    }

    /**
     * Send a request to Mandrill. All requests are sent via HTTP POST.
     * @param string $url
     * @param array $body
     * @throws Exception\APIException
     * @return array
     */
    protected function request($url, array $body = array()){
        $baseUrl = self::BASE_URL;
        if(isset($this->baseUrl)){
            $baseUrl = $this->baseUrl;
        }

        if(isset($this->handler)) {
            $client = new Client(array('handler' => $this->handler));
        } else {
            $client = new Client(array('base_uri' => $baseUrl));
        }

        $body['key'] = $this->apiKey;
        $section = explode('\\', get_called_class());
        $section = strtolower(end($section));

        $request = new Request('POST', $section.'/'.$url.'.json', array(), json_encode($body));

        try{
            $response = $client->send($request);
            $response = $response->getBody();
        }
        catch(ServerException $e){
            $response = json_decode($e->getResponse()->getBody(), true);
            throw new APIException($response['message'], $response['code'], $response['status'], $response['name'], $e);
        }
        return json_decode($response, true);
    }
}