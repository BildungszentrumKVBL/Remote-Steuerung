<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Tests\AppTestCase;

/**
 * Class AppControllerTest.
 */
class AppControllerTest extends AppTestCase
{
    /**
     * Test indexAction.
     */
    public function testIndex()
    {
        $client = static::createClient();

        // Requests website
        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $normalSize = strlen($client->getResponse()->getContent());

        // Requests website with AJAX.
        $client->request(
            'GET', '/', [], [], [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );
        $ajaxSize = strlen($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Is AJAX response smaller?
        $this->assertTrue($normalSize > $ajaxSize);
    }

    /**
     * Test jsonManifestAction
     */
    public function testJsonManifestAction()
    {
        $client = static::createClient();

        $client->request('GET', '/assets/manifest.json');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        // Content is JSON
        json_decode($content);
        $this->assertEquals(json_last_error(), JSON_ERROR_NONE);
    }

    /**
     * Test controllerAction.
     */
    public function testController()
    {
        $client = static::createClient();

        $client->request('GET', '/controller');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Test chooseRoomAction.
     */
    public function testChooseRoom()
    {
        $client = static::createClient();

        $client->request('GET', '/chooseRoom');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

    }
}
