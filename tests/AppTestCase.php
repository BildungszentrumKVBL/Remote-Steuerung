<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class AppTestCase.
 */
class AppTestCase extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $sharedClient;

    /**
     * @var Application
     */
    private $application;

    /**
     * Prepares environment for tests.
     *
     * @param bool|null $withDatabase
     *
     * @throws \Exception
     */
    public function setup(?bool $withDatabase = true, ?bool $withFixtures = true)
    {
        $this->sharedClient = static::createClient();
        $this->application  = new Application(static::$kernel);
        $this->application->setAutoExit(false);
        if ($withDatabase) {
            $this->runConsole('doctrine:database:create', ['--if-not-exists' => true]);
            $this->runConsole('doctrine:schema:drop', ['--force' => true]);
            $this->runConsole('doctrine:schema:create');
            if ($withFixtures) {
                $this->runConsole('doctrine:fixtures:load');
            }
        }
    }

    /**
     * Clear memory.
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->application = null;

        gc_collect_cycles();
    }

    /**
     * @param       $command
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function runConsole($command, array $options = [])
    {
        $options['-e'] = 'test';
        $options['-q'] = null;
        $options       = array_merge($options, ['command' => $command]);

        return $this->application->run(new ArrayInput($options));
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer()
    {
        return parent::$container;
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param string $username
     * @param string $password
     * @param bool   $strict   the test will fail, when login not successful
     *
     * @return bool returns true if successful, returns false if not successful
     */
    protected function login(string $username, string $password, bool $strict = true): bool
    {
        $client  = $this->sharedClient;
        $crawler = $client->request('GET', '/login');

        $form   = $crawler->filter('form');
        $inputs = $form->filter('input');

        // Basic assertion.
        $this->assertResponseIsSuccessful('Login page is not reachable!');
        $this->assertCount(4, $inputs, 'Login page does not contain the 4 inputs `_username`, `_password`, `_remember_me` and `_csrf_token`!');

        // Log in.
        $client->submitForm(
            'login-btn', [
                '_username'   => $username,
                '_password'   => $password,
                '_csrf_token' => $inputs->getNode(3)->getAttribute('value'), // 3: the 4. input is the token.
            ]
        );

        // It should always be a redirect.
        $this->assertResponseRedirects(null, null, 'Response from server after login was not a redirect to the main page nor to the login page!');
        $crawler = $client->followRedirect();

        if (false !== strpos($crawler->getUri(), '/login')) {
            if ($strict) {
                $this->fail(sprintf('Login failed for reason "%s"!', $crawler->filter('.errors')->getNode(0)->textContent));
            }

            return false;
        }

        return true;
    }
}
