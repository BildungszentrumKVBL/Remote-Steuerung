<?php

namespace App\Tests\Form\User;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class RegistrationFormTypeTest.
 */
class RegistrationFormTypeTest extends WebTestCase
{
    /**
     * @param Crawler $crawler
     *
     * @return string
     */
    private function extractCsrfToken(Crawler $crawler)
    {
        return $crawler->filter('form')->filter('input')->getNode(5)->getAttribute('value');
    }

    /**
     * @param Crawler $crawler
     *
     * @return Crawler
     */
    private function extractFormErrors(Crawler $crawler)
    {
        return $crawler->filter('.form-error-message');
    }

    public function testUserRelatedData()
    {
        $client     = static::createClient();
        $crawler    = $client->request('GET', '/register');
        $translator = $client->getContainer()->get('translator');

        // Fill form
        $formData = [
            'registration_form' => [
                'givenName'     => 'Max',
                'familyName'    => 'Mustermann',
                'email'         => 'test2@test.com',
                'plainPassword' => [
                    'plainPassword' => [
                        'first'  => 'MaxMustermannTestUser',
                        'second' => 'MaxMustermannTestUser',
                    ],
                ],
                '_token'        => $this->extractCsrfToken($crawler),
            ],
        ];

        $crawler = $client->request('POST', '/register', $formData);

        $errors = $this->extractFormErrors($crawler);

        $this->assertCount(3, $errors, '3 Errors should be detected when supplying `MaxMustermannTestUser` as password.');

        $this->assertEquals($translator->trans('user.password.no_numbers', [], 'validators'), $errors->getNode(0)->textContent, 'No number in password validation failed!');
        $this->assertEquals($translator->trans('user.password.family_name_in_password', [], 'validators'), $errors->getNode(1)->textContent, 'Family name in password validation failed!');
        $this->assertEquals($translator->trans('user.password.given_name_in_password', [], 'validators'), $errors->getNode(2)->textContent, 'Given name in password validation failed!');
    }

    public function testNoNumbersNoLowerAndNoUpperData()
    {
        $client     = static::createClient();
        $crawler    = $client->request('GET', '/register');
        $translator = $client->getContainer()->get('translator');

        // Fill form
        $formData = [
            'registration_form' => [
                'givenName'     => 'Max',
                'familyName'    => 'Mustermann',
                'email'         => 'test3@test.com',
                'plainPassword' => [
                    'plainPassword' => [
                        'first'  => '----------------',
                        'second' => '----------------',
                    ],
                ],
                '_token'        => $this->extractCsrfToken($crawler),
            ],
        ];

        $crawler = $client->request('POST', '/register', $formData);

        $errors = $this->extractFormErrors($crawler);

        $this->assertCount(3, $errors, '3 Errors should be detected when supplying `----------------` as password.');

        $this->assertEquals($translator->trans('user.password.no_numbers', [], 'validators'), $errors->getNode(0)->textContent, 'No number in password validation failed!');
        $this->assertEquals($translator->trans('user.password.no_lower_case_letters', [], 'validators'), $errors->getNode(1)->textContent, 'No lower letter in password validation failed!');
        $this->assertEquals($translator->trans('user.password.no_upper_case_letters', [], 'validators'), $errors->getNode(2)->textContent, 'No upper letter in password validation failed!');
    }
}
