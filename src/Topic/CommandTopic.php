<?php

namespace App\Topic;

use Gos\Bundle\WebSocketBundle\Router\WampRequest;
use Gos\Bundle\WebSocketBundle\Topic\PushableTopicInterface;
use Gos\Bundle\WebSocketBundle\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampConnection;

/**
 * Class CommandTopic.
 *
 * This topic handles the communication for the `command_topic` websocket channel.
 */
class CommandTopic implements TopicInterface, PushableTopicInterface
{
    /**
     * This will receive any subscription requests for this topic.
     *
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        /* @var WampConnection $connection */
        $topic->broadcast(json_encode(['msg' => sprintf('%s has joined %s', $connection->resourceId, $topic->getId())]));
    }

    /**
     * This will receive any unsubscription requests for this topic.
     *
     * @return void
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        /* @var WampConnection $connection */
        //this will broadcast the message to ALL subscribers of this topic.
        $topic->broadcast(json_encode(['msg' => sprintf('%s has left %s', $connection->resourceId, $topic->getId())]));
    }

    /**
     * This will receive any publish requests for this topic.
     *
     * @param $event
     *
     * @return mixed|void
     */
    public function onPublish(
        ConnectionInterface $connection,
        Topic $topic,
        WampRequest $request,
        $event,
        array $exclude,
        array $eligible
    ) {
        $topic->broadcast(json_encode(['msg' => $event]));
    }

    /**
     * RPC-like channel prefix.
     *
     * @see [Remote Precedure Call](http://searchmicroservices.techtarget.com/definition/Remote-Procedure-Call-RPC)
     */
    public function getName(): string
    {
        return 'app.command.topic';
    }

    /**
     * This function runs when the data will be published.
     *
     * @param array|string $data
     * @param string       $provider The name of pusher who push the data
     */
    public function onPush(Topic $topic, WampRequest $request, $data, $provider)
    {
        $topic->broadcast($data);
    }
}
