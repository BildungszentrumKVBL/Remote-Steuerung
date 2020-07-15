<?php

namespace App\Tests\Controller\Admin\Page;

use App\Entity\Page\Page;
use App\Entity\Page\PageNewChildrenSortingPreferenceEnum;
use App\Entity\Page\PageStatusEnum;
use App\Tests\AppTestCase;

/**
 * Class PageControllerTest.
 */
class PageControllerTest extends AppTestCase
{
    /**
     * @group minimal
     */
    public function testIndexUnauthenticated()
    {
        $this->sharedClient->request('GET', '/admin/page');
        $response = $this->sharedClient->getResponse();

        $this->assertTrue($response->isRedirect('http://localhost/login'), 'Not being redirected to login when trying to access admin page while not logged in!');
        $this->assertEquals($response->getStatusCode(), 302, 'No 302 status code set when redirecting to login page!');
    }

    /**
     * @group minimal
     */
    public function testIndexUnauthorized()
    {
        $this->login('joe@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/page');
        $response = $this->sharedClient->getResponse();

        $this->assertEquals($response->getStatusCode(), 403, 'Admin page is accessible by none admin users!');
    }

    /**
     * @group minimal
     */
    public function testIndexAuthorized()
    {
        $this->login('info@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/page');
        $response = $this->sharedClient->getResponse();

        $this->assertEquals($response->getStatusCode(), 200, 'Admin page is not working for admin users!');
    }

    public function testNewUnauthenticated()
    {
        $root = $this->getRootPage();
        $this->sharedClient->request('GET', '/admin/page/'.$root->getId().'/new');
        $response = $this->sharedClient->getResponse();

        $this->assertTrue($response->isRedirect('http://localhost/login'), 'Not being redirected to login when trying to access admin page while not logged in!');
        $this->assertEquals($response->getStatusCode(), 302, 'No 302 status code set when redirecting to login page!');
    }

    public function testNewUnauthorized()
    {
        $root = $this->getRootPage();
        $this->login('joe@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/page/'.$root->getId().'/new');
        $response = $this->sharedClient->getResponse();

        $this->assertEquals($response->getStatusCode(), 403, 'Admin page is accessible by none admin users!');
    }

    public function testNewAuthorized()
    {
        $root = $this->getRootPage();
        $this->login('info@jkweb.ch', 'asdf123');
        $this->sharedClient->request('GET', '/admin/page/'.$root->getId().'/new');
        $response = $this->sharedClient->getResponse();

        $this->assertEquals($response->getStatusCode(), 200, 'Admin page is not working for admin users!');
    }

    public function testNew()
    {
        $rootPage = $this->getRootPage();

        $this->login('info@jkweb.ch', 'asdf123');
        $url = '/admin/page/'.$rootPage->getId().'/new';

        $crawler  = $this->sharedClient->request('GET', $url);
        $response = $this->sharedClient->getResponse();

        $form   = $crawler->filter('form');
        $inputs = $form->filter('input');

        $beforeCount = $this->getPageCount();

        $formData = [
            'new_page_form' => [
                'name'     => 'Beispielseite',
                'template' => 'root',
                '_token'   => $inputs->getNode(1)->getAttribute('value'), // 2. input is _token
            ],
        ];

        $this->sharedClient->request('POST', $form->getNode(0)->getAttribute('action'), $formData);
        $response = $this->sharedClient->getResponse();

        $this->assertEquals(302, $response->getStatusCode(), 'Did not receive 302 after correctly filling in new page form!');
        $this->assertTrue($response->isRedirect('/admin/page'), 'Not being redirected to admin page index!');

        $this->assertEquals($beforeCount, $this->getPageCount() - 1, 'Did not insert new page after successfully filling form and sending it to the server!');
    }

    public function testEdit()
    {
        $this->login('info@jkweb.ch', 'asdf123');

        $rootPage = $this->getRootPage();

        $crawler = $this->sharedClient->request('GET', '/admin/page/'.$rootPage->getId());
        $form    = $crawler->filter('form');
        $this->assertEquals(1, $form->count(), 'None or more than 1 forms found when requesting the edit of a page in the admin interface.');

        $token = $form->filter('#edit_page_form__token')->getNode(0);

        // Fill form
        $formData = [
            'edit_page_form' => [
                'name'                            => 'Startseite new Title',
                'status'                          => PageStatusEnum::PUBLISHED,
                'new_children_sorting_preference' => PageNewChildrenSortingPreferenceEnum::TOP,
                '_token'                          => $token,
            ],
        ];

        $this->sharedClient->request('PUT', $form->getNode(0)->getAttribute('action'), $formData);

        $response = $this->sharedClient->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), 'Did not receive 200 after correctly filling in new page form!');

        $newRootPage = $this->getRootPage();
        $this->assertEquals('Startseite new Title', $newRootPage->getName());
        $this->assertEquals(PageStatusEnum::PUBLISHED, $newRootPage->getStatus());
        $this->assertEquals(PageNewChildrenSortingPreferenceEnum::TOP, $newRootPage->getNewChildrenSortingPreference());
    }

    public function testDelete()
    {
        $this->login('info@jkweb.ch', 'asdf123');
        $rootPage = $this->getRootPage();

        $crawler = $this->sharedClient->request('GET', '/admin/page/'.$rootPage->getId().'/delete');

        $form = $crawler->filter('form');
        $this->assertEquals(1, $form->count(), 'None or more than 1 forms found when requesting the deletion of a page in the admin interface.');

        $inputs = $form->filter('input');
        $this->assertEquals(2, $inputs->count(), 'The delete form for pages should contain the `_method` and the `prompt[token]`!');

        // Fill form
        $formData = [
            'prompt' => [
                '_token' => $inputs->getNode(1)->getAttribute('value'), // 2. input is _token
            ],
        ];

        // Delete the entry.
        $this->sharedClient->request('DELETE', $form->getNode(0)->getAttribute('action'), $formData);
        $response = $this->sharedClient->getResponse();

        $this->assertEquals(302, $response->getStatusCode(), 'Did not receive 200 after correctly filling in delete page form!');
        $this->assertTrue($response->isRedirect('/admin/page'), 'Not being redirected to admin page index!');

        $rootPage = $this->getRootPage();
        $this->assertNull($rootPage, 'Root Page has not been deleted');
    }

    /**
     * @return Page|null
     */
    private function getRootPage(): ?Page
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Page::class)->findRootPage();
    }

    /**
     * @return int
     *
     * @throws \Exception
     */
    private function getPageCount(): int
    {
        return count($this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Page::class)->findAll());
    }
}
