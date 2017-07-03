<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class CreateAdminCommand.
 */
class CreateAdminCommand extends ContainerAwareCommand
{
    /**
     * Configures the command, sets helptext and parameters.
     */
    protected function configure()
    {
        $this->setName('app:create:admin')->setDescription('Erstellt einen Admin Benutzer.')
            ->addOption('change-password', null, InputOption::VALUE_NONE, 'Ändert das Passwort des Admin Benutzers.');
    }

    /**
     * This is the entry-point when running the command from the CLI.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $io     = new SymfonyStyle($input, $output);
        $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User $admin */
        $admin   = $em->getRepository('AppBundle:User')->findOneBy(['username' => 'admin']);
        $encoder = $this->getContainer()->get('security.password_encoder');
        if ($admin) {
            if ($input->getOption('change-password')) { // Change Password
                $io->title('Admin Benutzer erstellen');
                $password = $this->askForPassword($input, $output, $helper);
                $admin->setPassword($encoder->encodePassword($admin, $password));
                $em->persist($admin);
                $em->flush($admin);
                $io->writeln('Das Passwort wurde geändert.');
            } else {
                $io->error('Admin existiert bereits.');

                return 1;
            }

            return 0;
        }
        $io->title('Admin Benutzer erstellen');

        $io->note('Verwenden Sie keine Informationen welche später per LDAP eingebunden werden könnten!');

        $email    = $this->askForEmail($input, $output, $helper);
        $password = $this->askForPassword($input, $output, $helper);

        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail($email);
        $admin->setEmailCanonical($email);
        $admin->setEnabled(true);
        $admin->setPassword($encoder->encodePassword($admin, $password));
        $admin->setFirstName('Admin');
        $admin->setLastName('istrator');
        $admin->setRoles(['ROLE_ADMIN']);

        $em->persist($admin);
        $em->flush();

        $io->writeln('Administrator wurde erstellt');

        return 0;
    }

    private function askForEmail(InputInterface $input, OutputInterface $output, $helper)
    {
        $emailQuestion = new Question('Bitte gib eine Email-Adresse an: ');
        $emailQuestion->setValidator(
            function($answer) {
                $violations = $this->getContainer()->get('validator')->validate($answer, [new Email()]);

                if (count($violations) === 0) {
                    $existingEmail = $this->getContainer()->get('doctrine.orm.entity_manager')
                        ->getRepository('AppBundle:User')->findOneBy(['email' => $answer]);
                    if (!$existingEmail) {
                        return $answer;
                    } else {
                        throw new \Exception(sprintf('Die Email-Adresse "%s" ist bereits vergeben!', $answer));
                    }
                } else {
                    throw new \Exception(sprintf('%s ist keine Email-Adresse!', $answer));
                }
            }
        );
        $emailQuestion->setMaxAttempts(5);

        return $helper->ask($input, $output, $emailQuestion);
    }

    private function askForPassword(InputInterface $input, OutputInterface $output, $helper)
    {
        $passwordQuestion = new Question('Bitte gib ein Passwort an: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);

        return $helper->ask($input, $output, $passwordQuestion);
    }
}
