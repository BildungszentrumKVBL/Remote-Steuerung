<?php

namespace App\Tests\Controller\User\Security;

use App\Entity\User\User;
use App\Tests\AppTestCase;
use ReflectionProperty;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SecurityControllerTest.
 */
class SecurityControllerTest extends AppTestCase
{
    public function testLoginAction()
    {
        $this->sharedClient->request('GET', '/login');
        $response = $this->sharedClient->getResponse();

        $this->assertResponseIsSuccessful('Login page is not reachable!');
        $this->assertContains('_username', $response->getContent(), 'Missing username in login form!');
        $this->assertContains('_password', $response->getContent(), 'Missing password in login form!');
        $this->assertContains('_token', $response->getContent(), 'Missing csrf token in login form!');
    }

    public function testLogoutAction()
    {
        $this->sharedClient->request('GET', '/logout');
        $this->assertResponseRedirects(null, null, 'Anonymous user was not redirected when visiting logout page!');
    }

    public function testLogin()
    {
        $this->login('info@jkweb.ch', 'asdf123', true);
    }

    public function testRequestPasswordAction()
    {
        $this->sharedClient->request('GET', '/resetting');
        $response = $this->sharedClient->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), 'Password request page is not reachable!');
        $this->assertContains('email', $response->getContent(), 'Missing email in password request form!');
        $this->assertContains('_token', $response->getContent(), 'Missing csrf token in password request form!');
    }

    public function testRequestAndResettingPassword()
    {
        $translator = $this->getContainer()->get('translator');
        $this->resetPassword('info@jkweb.ch');

        /** @var Response $response */
        $response = $this->sharedClient->getResponse();

        // It should always be a 200.
        $this->assertResponseIsSuccessful('Response from server after requesting password did not return status code 200.');

        // Test if flash is set.
        $this->assertContains($translator->trans('info.user.password_requested', [], 'flash'), $response->getContent(), 'Flash was not set after valid email was passed to request password route.');

        // Test if Email was sent.
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $this->sharedClient->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount(), 'Email was not sent, when requested by a email of a valid user.');

        // Testing mail and clicking link.
        $this->testResettingEmail($mailCollector->getMessages()[0]);

        // Login with new credentials.
        $this->login('info@jkweb.ch', 'Test123456789', true);
    }

    /**
     * @throws \Exception
     */
    public function testRequestAndResettingPasswordAttackPrevention()
    {
        $this->resetPassword('info@jkweb.ch');

        // Test if Email was sent.
        /** @var MessageDataCollector $mailCollector */
        $mailCollector = $this->sharedClient->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount(), 'Email was not sent, when requested by a email of a valid user.');
        /** @var \Swift_Message $message */
        $message = $mailCollector->getMessages()[0];

        $mailCollector->reset();

        // Redo
        $this->resetPassword('info@jkweb.ch');

        $this->assertEquals(0, $mailCollector->getMessageCount(), 'Email was sent, despite being requested recently.');

        $this->testResettingEmailAfter2Days($message);
    }

    /**
     * @param string $email
     *
     * @throws \Exception
     */
    private function resetPassword(string $email)
    {
        $crawler = $this->sharedClient->request('GET', '/resetting');

        // Needed to check the sent email.
        $this->sharedClient->enableProfiler();

        $form   = $crawler->filter('form');
        $inputs = $form->filter('input');

        $this->assertCount(2, $inputs, 'Requesting password page should contain 2 inputs `email` and `_token`');

        // Fill form.
        $formData = [
            'password_request_form' => [
                'email'  => $email,
                '_token' => $inputs->getNode(1)->getAttribute('value'), // 1: the 2. input is the token.
            ],
        ];

        $this->sharedClient->request('POST', $form->getNode(0)->getAttribute('action'), $formData); // This fails when there are multiple forms.
    }

    /**
     * @group ignore
     *
     * @param \Swift_Message $message
     *
     * @throws \Exception
     */
    public function testResettingEmail($message)
    {
        $translator = $this->getContainer()->get('translator');

        // Asserting email data
        $this->assertTrue($message instanceof \Swift_Message, 'Registration email not type of \\Swift_Message');
        $this->assertEquals($message->getSubject(), $translator->trans('password.resetting.subject', [], 'emails'), 'Email subject does not match translation!');
        $this->assertArrayHasKey('info@jkweb.ch', $message->getTo(), 'User email not in recipients!');
        $this->assertNotEmpty($message->getBody());

        // Crawl email for link
        $crawler = new Crawler();
        $crawler->addHtmlContent($message->getBody());
        $links = $crawler->filter('a');

        $this->assertCount(3, $links, 'There was only 3 link expected.');

        $this->testActualResetting($links->getNode(1)->getAttribute('href'));
    }

    /**
     * @group ignore
     *
     * @param \Swift_Message $message
     *
     * @throws \Exception
     */
    public function testResettingEmailAfter2Days($message)
    {
        $em        = $this->getContainer()->get('doctrine.orm.entity_manager');
        /* @var User $user */
        $user      = $em->getRepository(User::class)->findOneBy(['email' => 'info@jkweb.ch']);
        $reflector = new ReflectionProperty(User::class, 'passwordRequestedAt');
        $reflector->setAccessible(true);
        $before2Days = new \DateTime();
        $before2Days->modify('-2 days');
        $reflector->setValue($user, $before2Days);
        $em->flush();

        $translator = $this->getContainer()->get('translator');

        // Asserting email data
        $this->assertTrue($message instanceof \Swift_Message, 'Registration email not type of \\Swift_Message');
        $this->assertEquals($message->getSubject(), $translator->trans('password.resetting.subject', [], 'emails'), 'Email subject does not match translation!');
        $this->assertArrayHasKey('info@jkweb.ch', $message->getTo(), 'User email not in recipients!');
        $this->assertNotEmpty($message->getBody());

        // Crawl email for link
        $crawler = new Crawler();
        $crawler->addHtmlContent($message->getBody());
        $links = $crawler->filter('a');

        $this->assertCount(3, $links, 'There was only 1 link expected. 0 or more than 1 are in the email!');

        $this->sharedClient->request('GET', $links->getNode(1)->getAttribute('href'));
        $this->assertResponseRedirects('/login', null, 'Visiting resetting page after 2 days, did not redirect to login page!');

        $crawler = $this->sharedClient->followRedirect();

        $flashes = $crawler->filter('.flashes')->first();

        $this->assertCount(1, $flashes, 'Not displaying 1 Flash after visiting resetting page after 2 days!');
        $this->assertContains($translator->trans('error.password.requested.too_long_ago', [], 'flash'), $flashes->text(), 'Error notice, visiting resetting page after 2 days is not beign displayed!');
    }

    /**
     * @group ignore
     *
     * @param string $link
     */
    public function testActualResetting($link)
    {
        $crawler = $this->sharedClient->request('GET', $link);
        $form    = $crawler->filter('form');
        $inputs  = $form->filter('input');

        $this->assertCount(3, $inputs, 'Resetting page does not contain the 3 inputs `plainPassword first`, `plainPassword second` and `_csrf_token`!');

        // Fill form.
        $formData = [
            'password_resetting_form' => [
                'plainPassword' => [
                    'first'  => 'Test123456789',
                    'second' => 'Test123456789',
                ],
                '_token'        => $inputs->getNode(2)->getAttribute('value'), // 2: The 3. input is the token.
            ],
        ];

        // Reset password.
        $this->sharedClient->request('POST', $form->getNode(0)->getAttribute('action'), $formData); // This fails when there are multiple forms.

        // When successful. You will be redirected to login.
        $this->assertResponseRedirects('/login', null, 'Not redirected after resetting the password successfully.');
    }

    public function testPasswordResetting()
    {
        $this->sharedClient->request('GET', '/resetting/2345');
        $this->assertResponseStatusCodeSame(404, 'Resetting password with invalid token did not return 404!');
    }
}
