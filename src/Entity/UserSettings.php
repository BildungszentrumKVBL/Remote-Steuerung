<?php

namespace App\Entity;

use ReflectionClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserSettings.
 *
 * This entity represents the settings of an individual user.
 * It contains informations such as the theme and view.
 *
 * @ORM\Entity()
 * @ORM\Table(name="app_user_settings")
 * @ORM\HasLifecycleCallbacks()
 */
class UserSettings
{
    /**
     * Theme in the colors of the school-logo.
     */
    const THEME_ORIGINAL = 'original';

    /**
     * Theme for visually impaired people.
     */
    const THEME_DARK = 'dark';

    /**
     * Theme with based with the indigo-color.
     */
    const THEME_INDIGO = 'indigo';

    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     *
     * @var int $id
     */
    private $id;

    /**
     * The theme wich is currently active.
     *
     * @ORM\Column(name="theme", type="string")
     *
     * @var string $theme
     */
    private $theme;

    /**
     * The view of the controller in the web-application.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\View")
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     *
     * @var View $view
     */
    private $view;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool $usePush
     */
    private $usePush;

    /**
     * UserSettings constructor.
     */
    public function __construct()
    {
        $this->usePush = false;
        $this->setTheme(self::THEME_ORIGINAL);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTheme(): string
    {
        return $this->theme;
    }

    /**
     * @param string $theme
     *
     * @return $this
     */
    public function setTheme(string $theme)
    {
        if (in_array($theme, $this->getThemes())) {
            $this->theme = $theme;
        }

        return $this;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param View $view
     */
    public function setView(View $view)
    {
        $this->view = $view;
    }

    /**
     * This method takes advantage of the `\ReflextionClass`. This allows the method to read how this class is
     * build up.
     *
     * In this case, it reads the constants of this class and filters them for constants that start with the name
     * "THEME_".
     *
     * When the passed value is equal to one of those constants, the value will be set. This will ensure that there is
     * no invalid data in the `theme`-field in this class.
     *
     * @see [PHP Magic Methods](http://php.net/manual/en/language.oop5.magic.php)
     *
     * @return array
     */
    private function getThemes(): array
    {
        $rc        = new ReflectionClass(__CLASS__);
        $constants = $rc->getConstants();
        $themes    = [];
        foreach ($constants as $name => $constant) {
            if (substr($name, 0, 6) === 'THEME_') {
                $themes[] = $constant;
            }
        }

        return $themes;
    }

    /**
     * @return bool
     */
    public function isUsePush(): bool
    {
        return $this->usePush;
    }

    /**
     * @param bool $usePush
     */
    public function setUsePush(bool $usePush)
    {
        $this->usePush = $usePush;
    }
}
