<?php

namespace App\Tests\Command;

use App\Tests\AppTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class FixturesReloadCommandTest.
 */
class FixturesReloadCommandTest extends AppTestCase
{
    /**
     * @group ignore
     */
    public function testExecute()
    {
        $application = $this->getApplication();
        $command     = $application->find('doctrine:fixtures:reload');
        $tester      = new CommandTester($command);

        // TODO: Fix this in gitlab-ci.
        $tester->execute(['command' => $command->getName()]);

        $output = $tester->getDisplay();

        $this->assertContains('Reinstalling Fixtures', $output);
        $this->assertContains('Dropping database', $output);
        $this->assertContains('Creating database', $output);
        $this->assertContains('Running migrations', $output);
        $this->assertContains('Load fixtures', $output);
    }
}
