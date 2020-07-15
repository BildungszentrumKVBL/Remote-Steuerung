<?php

namespace App\Tests\Controller\Admin\User;

use App\Entity\User\User;
use App\Tests\AppTestCase;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends AppTestCase
{
    /**
     * @group minimal
     */
    public function testIndexUnauthenticated()
    {
        $this->sharedClient->request('GET', '/admin/user/');
        $this->assertResponseRedirects('http://localhost/login', 302, 'Not being redirected to login when trying to access admin page while not logged in!');
    }

    /**
     * @group minimal
     */
    public function testIndexUnauthorized()
    {
        $this->login('joe@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/user/');

        $this->assertResponseStatusCodeSame(403, 'Admin page is accessible by none admin users!');
    }

    /**
     * @group minimal
     */
    public function testIndexAuthorized()
    {
        $this->login('info@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/user/');

        $this->assertResponseIsSuccessful('Admin page is not working for admin users!');
    }

    public function testEditUnauthenticated()
    {
        $joe = $this->getJoe();
        $this->sharedClient->request('GET', '/admin/user/'.$joe->getId().'/edit');
        $this->assertResponseRedirects('http://localhost/login', 302, 'Not being redirected to login when trying to access admin page while not logged in!');
    }

    public function testEditUnauthorized()
    {
        $joe = $this->getJoe();
        $this->login('joe@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/user/'.$joe->getId().'/edit');

        $this->assertResponseStatusCodeSame(403, 'Admin page is accessible by none admin users!');
    }

    public function testEditAuthorized()
    {
        $this->login('info@jkweb.ch', 'asdf123');
        $joe = $this->getJoe();

        $crawler = $this->sharedClient->request('GET', '/admin/user/'.$joe->getId().'/edit');
        $form    = $crawler->filter('form');
        $this->assertEquals(1, $form->count(), 'None or more than 1 forms found when requesting the edit of a page in the admin interface.');
        $inputs = $form->filter('input');

        // Fill form
        $formData = [
            'user_form' => [
                'givenName'  => 'Joestar',
                'familyName' => 'Average',
                'email'      => 'joe@jkweb.ch',
                'roles'      => [],
                'active'     => true,
                '_token'     => $inputs->getNode(7)->getAttribute('value'),
                // 7. input is _token. Accommodate for roles.
            ],
        ];

        $this->sharedClient->request('PUT', $form->getNode(0)->getAttribute('action'), $formData);

        $this->assertResponseRedirects('/admin/user/', 302, 'Admin is not redirected after successfully updating a user!');

        $this->assertNotNull($this->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['givenName' => 'Joestar']));
    }

    public function testDeleteUnauthenticated()
    {
        $joe = $this->getJoe();
        $this->sharedClient->request('GET', '/admin/user/'.$joe->getId().'/delete');

        $this->assertResponseRedirects('http://localhost/login', 302, 'Not being redirected to login when trying to access admin page while not logged in!');
    }

    public function testDeleteUnauthorized()
    {
        $joe = $this->getJoe();
        $this->login('joe@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/user/'.$joe->getId().'/delete');

        $this->assertResponseStatusCodeSame(403, 'Admin page is accessible by none admin users!');
    }

    public function testDeleteAuthorized()
    {
        $this->login('info@jkweb.ch', 'asdf123');
        $joe = $this->getJoe();

        $crawler = $this->sharedClient->request('GET', '/admin/user/'.$joe->getId().'/delete');

        $form   = $crawler->filter('form');
        $inputs = $form->filter('input');

        // Fill form
        $formData = [
            'prompt' => [
                '_token' => $inputs->getNode(1)->getAttribute('value'), // 2. input is _token
            ],
        ];

        // Delete the entry.
        $this->sharedClient->xmlHttpRequest('DELETE', $form->getNode(0)->getAttribute('action'), $formData);
        $response = $this->sharedClient->getResponse();

        $this->assertResponseIsSuccessful('Did not receive 200 after correctly filling in delete user form!');
        $responseData = json_decode($response->getContent());

        $this->assertEquals(JSON_ERROR_NONE, json_last_error(), 'The received response from delete a new page is not JSON: '.json_last_error_msg());
        $this->assertEquals($responseData->status, 'success', 'Did not receive success after correctly filling in new page form!');

        $this->assertNull($this->getJoe(), 'Page was not deleted from database after deleting it using the admin page!');
    }

    /**
     * @return User
     */
    private function getJoe(): ?User
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class)->findOneBy(['email' => 'joe@jkweb.ch']);
    }
}
