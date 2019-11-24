<?php

namespace App\Tests\Controller\App;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class AppControllerTest.
 */
class AppControllerTest extends WebTestCase
{
    public function staticPageProvider()
    {
        yield ['/'];
        yield ['/terms'];
        yield ['/imprint'];
        yield ['/privacy-policy'];
    }

    /**
     * @dataProvider staticPageProvider
     *
     * @group        minimal
     *
     * @param string $url
     */
    public function testAvailability(string $url)
    {
        $client = $this->createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful('Static Page "'.$url.'" is not reachable.');
    }
}
