<?php

namespace AppBundle\Composer;

use Composer\Script\Event;

/**
 * Class ScriptHandler.
 */
class ScriptHandler extends \Sensio\Bundle\DistributionBundle\Composer\ScriptHandler
{
    /**
     * @param Event $event
     */
    public static function install(Event $event)
    {
        $io = $event->getIO();
        $io->write("WICHTIG: Die Composer-Scripts sind durchgelaufen.");
        $io->write("         Nun können Sie Ihre Infrastruktur in die Datenbank laden,");
        $io->write("         und einen Administrator erstellen.");
        $io->write("         Konsultieren Sie dazu das README.md, oder");
        $io->write("         führen Sie folgenden Befehle aus:\n");
        $io->write("    php app/console app:import:infrastructure --help");
        $io->write("    php app/console app:create:admin --help");
    }
}
