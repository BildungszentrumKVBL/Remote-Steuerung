<?php

namespace App\Tests\Controller\User;

use App\Tests\AppTestCase;
use Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RegistrationControllerTest.
 */
class RegistrationControllerTest extends AppTestCase
{
    public function testRegistrationAction()
    {
        $this->sharedClient->request('GET', '/register');
        $response = $this->sharedClient->getResponse();

        $this->assertResponseIsSuccessful('Registration page is not reachable!');
        $this->assertContains('registration_form[givenName]', $response->getContent(), 'Missing given name in registration form!');
        $this->assertContains('registration_form[familyName]', $response->getContent(), 'Missing family name in registration form!');
        $this->assertContains('registration_form[email]', $response->getContent(), 'Missing email in registration form!');
        $this->assertContains('registration_form[plainPassword][plainPassword][first]', $response->getContent(), 'Missing password in registration form!');
        $this->assertContains('registration_form[plainPassword][plainPassword][second]', $response->getContent(), 'Missing password confirmation in registration form!');
        $this->assertContains('registration_form[_token]', $response->getContent(), 'Missing _token confirmation in registration form!');
    }

    public function testVerificationAction()
    {
        $this->sharedClient->request('GET', '/verify/123143423');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testRegistration()
    {
        $crawler = $this->sharedClient->request('GET', '/register');

        // Needed to check the sent email.
        $this->sharedClient->enableProfiler();

        $form   = $crawler->filter('form');
        $inputs = $form->filter('input');

        // Fill form.
        $formData = [
            'registration_form' => [
                'givenName'     => 'Max',
                'familyName'    => 'Mustermann',
                'email'         => 'test@test.com',
                'plainPassword' => [
                    'plainPassword' => [
                        'first'  => 'UltimateTest123',
                        'second' => 'UltimateTest123',
                    ],
                ],
                '_token'        => $inputs->getNode(5)->getAttribute('value'), // 5: the 6. input is the token.
            ],
        ];

        // Send the data.
        $this->sharedClient->request('POST', $form->getNode(0)->getAttribute('action'), $formData); // This fails when there are multiple forms.

        // Test if Email was sent.
        /* @var MessageDataCollector $mailCollector */
        $mailCollector = $this->sharedClient->getProfile()->getCollector('swiftmailer');
        $this->assertEquals(1, $mailCollector->getMessageCount(), 'Registration email was not sent!');

        /* @var Response $response */
        $this->assertResponseRedirects('/check-email', null, 'Not redirected to /check-email after registration!');

        // Run follow-up test
        $this->testFailLoginAfterRegistration();

        // Verify email
        $this->testVerifyEmail($mailCollector->getMessages()[0]);

        // Try again.
        $this->login('test@test.com', 'UltimateTest123');
    }

    /**
     * @group ignore
     */
    public function testFailLoginAfterRegistration()
    {
        $this->assertFalse($this->login('test@test.com', 'UltimateTest123', false), 'Login was successful without verifying email!');

        $crawler = $this->sharedClient->getCrawler();

        $error = $crawler->filter('.flashes')->filter('.alert.alert-danger')->filter('.flash-text-wrapper')->getNode(0);
        $this->assertNotNull($error, 'No .error was found after failed login, after registration.');

        $translator = $this->getContainer()->get('translator');
        $this->assertEquals($error->textContent, $translator->trans('Account is locked.', [], 'security'));
    }

    /**
     * @group ignore
     *
     * @param \Swift_Message $message
     *
     * @throws \Exception
     */
    public function testVerifyEmail($message)
    {
        $translator = $this->getContainer()->get('translator');

        // Asserting email data
        $this->assertTrue($message instanceof \Swift_Message, 'Registration email not type of \\Swift_Message');
        $this->assertEquals($message->getSubject(), $translator->trans('email.verification.subject', [], 'emails'), 'Email subject does not match translation!');
        $this->assertArrayHasKey('test@test.com', $message->getTo(), 'User email not in recipients!');
        $this->assertNotEmpty($message->getBody());

        // Crawl email for link
        $crawler = new Crawler();
        $crawler->addHtmlContent($message->getBody());
        $links = $crawler->filter('a');

        $this->assertCount(3, $links, 'There was only 1 link expected. 0 or more than 1 are in the email!');

        // Follow link
        $target = $links->getNode(1)->getAttribute('href');

        // Test first visit
        $this->sharedClient->request('GET', $target);
        $response = $this->sharedClient->getResponse();

        $this->assertContains('/login', $response->headers->get('Refresh'), 'Verification does not redirect to /login!');
        $this->assertContains($translator->trans('verification.login'), $response->getContent(), 'Verification does not contain information about login!');
        $this->assertContains($translator->trans('verification.thanks'), $response->getContent(), 'Verification does not thank user for verification!');

        // Test second visit
        $this->sharedClient->request('GET', $target);

        $this->assertResponseRedirects('/', null, 'Second time verifying email did not redirect to "/"!');
    }
}
