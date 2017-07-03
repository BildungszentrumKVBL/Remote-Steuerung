<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\UserSettings;
use AppBundle\Tests\AppTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserSettingsTest.
 */
class UserSettingsTest extends AppTestCase
{
    /**
     * Test getters and setters for UserSettings class.
     */
    public function testGettersAndSetters()
    {
        $em       = $this->getContainer()->get('doctrine.orm.entity_manager');
        $view     = $em->getRepository('AppBundle:View')->findOneBy(['name' => 'Cockpit']);
        $settings = new UserSettings();

        $this->assertNull($settings->getView());
        $settings->setView($view);

        $this->assertTrue($settings->getTheme() === UserSettings::THEME_ORIGINAL);
        $this->assertTrue($settings->getView() === $view);

        $this->assertNull($settings->getId());
        $settings->setTheme(UserSettings::THEME_DARK);
        $this->assertTrue($settings->getTheme() === UserSettings::THEME_DARK);
    }
}
