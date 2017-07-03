<?php

namespace AppBundle\Tests\Command;

use AppBundle\Command\CleanDatabaseCommand;
use AppBundle\Entity\Log;
use AppBundle\Tests\AppTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CleanDatabaseCommandTest.
 *
 * @covers CleanDatabaseCommand
 */
class CleanDatabaseCommandTest extends AppTestCase
{
    /**
     * Tests execute function.
     */
    public function testExecute()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->getApplication()->add(new CleanDatabaseCommand());
        $command = $this->getApplication()->find('app:clean:database');

        $oldLog = new Log('test', Log::LEVEL_INFO);
        $oldLog->setDateTime(new \DateTime('-2 months'));
        $em->persist($oldLog);

        $newLog = new Log('test', Log::LEVEL_INFO);
        $em->persist($newLog);
        $em->flush();

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Datenbank Säuberungsroutine', $output);
        $this->assertContains('2 Logeinträge insgesamt gefunden.', $output);
        $this->assertContains('1 Logeinträge aus der Datenbank entfernt.', $output);
    }
}
