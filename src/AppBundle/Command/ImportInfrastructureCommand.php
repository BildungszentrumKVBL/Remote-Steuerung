<?php

namespace AppBundle\Command;

use AppBundle\Entity\Building;
use AppBundle\Entity\Computer;
use AppBundle\Entity\Room;
use AppBundle\Entity\Zulu;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ImportInfrastructureCommand.
 */
class ImportInfrastructureCommand extends ContainerAwareCommand
{
    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this->setName('app:import:infrastructure')->setDescription('Importer der Infrastruktur')
            ->addArgument('file', InputArgument::REQUIRED)
            ->addOption('y', null, null, 'Automatisches Handeln ohne Interaktion.');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Infrastruktur Importer');

        $filename = $input->getArgument('file');

        // Validate file
        if (substr($filename, 0, 1) !== DIRECTORY_SEPARATOR) {
            $filename = getcwd().DIRECTORY_SEPARATOR.$filename;
        }

        if (!file_exists($filename)) {
            $io->error(sprintf('File: "%s" does not exist', $filename));
            exit;
        }

        // Check for extension
        $fileType = strtolower(pathinfo($filename)['extension']);

        if ($fileType === 'yml') {
            $this->askDeleteInfrastructure($input, $output);
            $this->loadYml($filename);
            $io->write('Daten wurden geschrieben.');
        } elseif ($fileType === 'csv') {
            $this->askDeleteInfrastructure($input, $output);
            $this->loadCsv($filename);
            $io->write('Daten wurden geschrieben.');
        } elseif ($fileType === 'xml') {
            $this->askDeleteInfrastructure($input, $output);
            $this->loadXml($filename);
            $io->write('Daten wurden geschrieben.');
        } else {
            $io->error(sprintf('Filetype: "%s" is not supported', $fileType));
            return 1;
        }

        return 0;
    }

    /**
     * Loads data by yaml file.
     *
     * @param string $filename
     */
    public function loadYml(string $filename)
    {
        $manager  = $this->getContainer()->get('doctrine.orm.entity_manager');
        $fixtures = Yaml::parse(file_get_contents($filename));

        foreach ($fixtures as $buildingName => $data) {
            $building = new Building($buildingName);
            foreach ($data as $roomName => $roomContents) {
                $room     = new Room($roomName);
                $zulu     = new Zulu($roomContents['Zulu']);
                $computer = new Computer($roomContents['PC']['name']);
                $room->setZulu($zulu);
                $room->setComputer($computer);
                $building->addRoom($room);
                $manager->persist($computer);
                $manager->persist($room);
                $manager->persist($zulu);
            }
            $manager->persist($building);
        }
        $manager->flush();
    }

    /**
     * Loads data by csv file.
     *
     * @param string $filename
     *
     * @throws \Exception
     */
    public function loadCsv(string $filename)
    {
        $manager      = $this->getContainer()->get('doctrine.orm.entity_manager');
        $buildingRepo = $manager->getRepository('AppBundle:Building');
        $roomRepo     = $manager->getRepository('AppBundle:Room');
        $csv          = explode("\n", file_get_contents($filename));
        $label        = null;
        foreach ($csv as $line) {
            if ($line === '') { // Skip empty lines.
                continue;
            }
            if (substr($line, 0, 2) === '{{') {
                $label = $line;
            } else {
                if ($label === '{{Building}}') {
                    $building = new Building($line);
                    $manager->persist($building);
                    $manager->flush($building);
                } elseif ($label === '{{Rooms}}') {
                    list($buildingName, $roomName) = explode(';', $line);
                    $room = new Room($roomName);
                    /** @var Building $building */
                    $building = $buildingRepo->findOneBy(['name' => $buildingName]);
                    if ($building) {
                        $building->addRoom($room);
                        $manager->persist($building);
                        $manager->flush($building);
                    } else {
                        throw new \Exception(sprintf('Building: "%s" was not found. Did you make sure that the buildings are defined above the rooms in your CSV?', $buildingName));
                    }
                } elseif ($label === '{{PC}}') {
                    list($roomName, $pcName) = explode(';', $line);
                    /** @var Room $room */
                    $room = $roomRepo->findOneBy(['name' => $roomName]);
                    if ($room) {
                        $pc = new Computer($pcName);
                        $room->setComputer($pc);
                        $manager->persist($room);
                        $manager->flush($room);
                    } else {
                        throw new \Exception(sprintf('Room: "%s" was not found. Did you make sure that the rooms are defined above the computers in your CSV?', $roomName));
                    }
                } elseif ($label === '{{Zulu}}') {
                    list($roomName, $zuluIp) = explode(';', $line);
                    /** @var Room $room */
                    $room = $roomRepo->findOneBy(['name' => $roomName]);
                    if ($room) {
                        $zulu = new Zulu($zuluIp);
                        $room->setZulu($zulu);
                        $manager->persist($room);
                        $manager->flush($room);
                    } else {
                        throw new \Exception(sprintf('Room: "%s" was not found. Did you make sure that the rooms are defined above the zulus in your CSV?', $roomName));
                    }
                } else {
                    throw new \Exception('No label was set. Please consult the README.');
                }
            }
        }
    }

    /**
     * Loads data by xml file.
     *
     * @param string $filename
     */
    public function loadXml(string $filename)
    {
        $manager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $xml = simplexml_load_string(file_get_contents($filename)) or die("Error: Cannot create object by xml.");
        foreach ($xml as $buildingName => $roomNames) {
            $building = new Building($buildingName);
            foreach ($roomNames as $roomName => $data) {
                $room = new Room($roomName);
                $building->addRoom($room);
                if ($data->Zulu) {
                    $zulu = new Zulu($data->Zulu);
                    $room->setZulu($zulu);
                }
                if ($data->PC) {
                    $pc = new Computer($data->PC->name);
                    $room->setComputer($pc);
                }
            }
            $manager->persist($building);
            $manager->flush($building);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function askDeleteInfrastructure(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('y')) { // y Option skips interaction.
            /** @var QuestionHelper $helper */
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion('Die bestehende Infrastruktur wird gelöscht. Möchten Sie fortfahren? (N/y)', false);

            if (!$helper->ask($input, $output, $question)) {
                $io = new SymfonyStyle($input, $output);
                $io->error('Import wurde abgebrochen.');
                exit;
            }
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $pcs = $em->getRepository('AppBundle:Computer')->findAll();
        foreach ($pcs as $pc) {
            $em->remove($pc);
        }

        $zulus = $em->getRepository('AppBundle:Zulu')->findAll();
        foreach ($zulus as $zulu) {
            $em->remove($zulu);
        }

        $rooms = $em->getRepository('AppBundle:Room')->findAll();
        foreach ($rooms as $room) {
            $em->remove($room);
        }

        $buildings = $em->getRepository('AppBundle:Building')->findAll();
        foreach ($buildings as $building) {
            $em->remove($building);
        }

        $em->flush();
    }

}
