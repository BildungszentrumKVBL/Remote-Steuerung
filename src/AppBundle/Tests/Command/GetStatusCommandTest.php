<?php

namespace AppBundle\Tests\Command;

use AppBundle\Command\GetStatusCommand;
use AppBundle\Tests\AppTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GetStatusCommandTest.
 *
 * @covers GetStatusCommand
 */
class GetStatusCommandTest extends AppTestCase
{
    public function testExecute()
    {
        $this->getApplication()->add(new GetStatusCommand());
        $command = $this->getApplication()->find('app:status:get');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'room'    => 'A11',
            ]
        );

        $output = $commandTester->getDisplay();

        $data = json_decode($output);

        // API not available.
        // TODO: Mock Zulu API.
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
        $this->assertTrue(is_object($data), 'HINWEIS: Das Fehlschlagen dieses Tests ist auf der Entwicklungsumgebung OK.');

        $commandTester->execute(
            [
                'command' => $command->getName(),
                'room'    => 'abcd',
            ]
        );

        $output = $commandTester->getDisplay();

        $data = json_decode($output);

        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
        $this->assertNull($data);
    }
}
