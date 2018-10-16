<?php

namespace App\Manager;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class SlackManager
{

    /**
     * @var string
     */
    private $slackToken;

    /**
     * @var string
     */
    private $apiEndpoint = 'https://slack.com/api/';


    /**
     * SlackManager constructor.
     * @param string $slackToken
     */
    public function __construct(string $slackToken)
    {
        $this->slackToken = $slackToken;
        $this->guzzle = new Client(['timeout' => 1000]);
    }


    /**
     * @param $method
     * @param array $args
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function call($method, $args = array())
    {
        $body = array_merge($args, array('token' => $this->slackToken));

        return $this->guzzle->request(
            'POST',
            sprintf(
                '%s/%s',
                $this->apiEndpoint,
                $method
            ),
            array(
                'headers' => array(),
                'form_params' => $body
            )
        );
    }

    public function getSlackUsersList()
    {
        return $this->call('users.list');
    }

    public function sendEphemeralMessage(string $to, string $text, string $userId)
    {
        return $this->call('chat.postEphemeral', array(
            'channel' => $to,
            'text' => $text,
            'user' => $userId,
            'as_user' => false
        ))->getBody()->getContents();
    }

    public function sendMessage(string $to, string $text, array $attachments = null)
    {
        return $this->call('chat.postMessage', array(
            'channel' => $to,
            'text' => $text,
            'attachments' => json_encode($attachments),
            'as_user' => false
        ))->getBody()->getContents();
    }

    public function respondToAction(string $respondUrl, string $text, array $attachments = null)
    {
        $json_payload = array(
            'text' => $text,
            'replace_original' => true,
            'response_type' => 'in_channel',
            'attachments' => $attachments,
        );

        return $this->guzzle->request(
            'POST',
            $respondUrl,
            [RequestOptions::JSON => $json_payload]
        )->getBody()->getContents();
    }

    public function updateChat(string $ts, string $to, string $text, array $attachments = null)
    {
        return $this->call('chat.update', array(
            'ts' => $ts,
            'channel' => $to,
            'text' => $text,
            'attachments' => json_encode($attachments),
            'as_user' => false
        ))->getBody()->getContents();
    }

    public function retrieveChannel(string $channelName)
    {
        $channels = json_decode($this->call('channels.list')->getBody()->getContents(), false);
        foreach ($channels as $channel) {
            if ($channel['name'] === $channelName) {
                return $channel['id'];
            }
        }
        return null;
    }
}
