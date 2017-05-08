<?php

namespace App\Http\Helpers;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\Exceptions\HttpResponseException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Exception\LogicException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Psr7\StreamWrapper;

/**
 * TravisApi
 * A handy helper for connecting with travis api.
 *
 * @package App\Http\Helpers
 * @author  Darren Ethier
 * @since   1.0.0
 */
class TravisApi
{
    /**
     * The HTTP client instance.
     * @var HttpClient
     */
    protected $http;


    /**
     * Just a temporary object cache which is indexed by the id.
     * @var array
     */
    protected $object_cache = array();


    public function __construct(HttpClient $http)
    {
        $this->http = $http;
        $this->token = env('TRAVIS_TOKEN');

        //if no token then can't do nothing
        if (empty($this->token)) {
            throw new LogicException(
                'TravisApi helper cannot be used because there is no token.  Please define a TRAVIS_TOKEN in your .env file'
            );
        }
    }



    public function getBuildObject($build_id)
    {
        if (! isset($this->object_cache[$build_id])) {
            $this->object_cache[$build_id] = $this->handleResponse(
                $this->request(
                    "build/$build_id",
                    'GET'
                )
            );
        }
        return $this->object_cache[$build_id];
    }


    public function getBuildJobObjects($build_id)
    {
        if (! isset($this->object_cache['jobs'][$build_id]))
        {
            $jobs_object = $this->handleResponse(
                $this->request(
                    "build/$build_id/jobs",
                    'GET'
                )
            );
            $this->object_cache['jobs'][$build_id] = $jobs_object->jobs;
            foreach ($jobs_object->jobs as $job) {
                $this->object_cache[$job->id] = $job;
            }
        }
        return $this->object_cache['jobs'][$build_id];
    }


    public function getJobObject($job_id)
    {
        if (! isset($this->object_cache[$job_id])) {
            $this->object_cache[$job_id] = $this->handleResponse(
                $this->request(
                    "job/$job_id",
                    'GET'
                )
            );
        }
        return $this->object_cache[$job_id];
    }



    protected function request($endpoint, $type = 'POST', $package = array())
    {
        $url = 'https://api.travis-ci.org/' . $endpoint;
        $request_options = [
            'headers' => array(
                'Travis-API-Version' => 3,
                'Authorization' => 'token ' . $this->token,
                'User-Agent' => 'EE Artifacts Viewer',
                'Accept' => 'application/vnd.travis-ci.2+json',
                /*'Content-Type' => 'application/json'*/
            ),
            'stream' => false
        ];
        if (! empty($package)) {
            $request_options['json'] = $package;
        }
        return $this->http->request(
            $type,
            $url,
            $request_options
        );
    }


    protected function handleResponse(ResponseInterface $response)
    {
        $body = json_decode($response->getBody()->getContents());

        if (in_array($response->getStatusCode(), $this->getErrorStatusCodes())) {
            Log::error($body->error_message);
            throw new HttpResponseException(
                $body->error_message
            );
        }
        return $body;
    }



    protected function getErrorStatusCodes()
    {
        return [
            409,
            400,
            403,
            405,
            404,
            501,
            429,
            500,
            422
        ];
    }
}