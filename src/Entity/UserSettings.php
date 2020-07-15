<?php

namespace App\Entity;

use App\Entity\Traits\Id;
use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;

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
    public const THEME_ORIGINAL = 'original';

    /**
     * Theme for visually impaired people.
     */
    public const THEME_DARK = 'dark';

    /**
     * Theme with based with the indigo-color.
     */
    public const THEME_INDIGO = 'indigo';

    use Id;

    /**
     * The theme wich is currently active.
     *
     * @ORM\Column(name="theme", type="string")
     *
     * @var string
     */
    private $theme;

    /**
     * The view of the controller in the web-application.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\View")
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     *
     * @var View
     */
    private $view;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
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

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): void
    {
        if (in_array($theme, $this->getThemes())) {
            $this->theme = $theme;
        }
    }

    public function getView(): ?View
    {
        return $this->view;
    }

    public function setView(View $view): void
    {
        $this->view = $view;
    }

    /**
     * TODO: Replace with static array.
     *
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
     */
    private function getThemes(): array
    {
        $rc        = new ReflectionClass(__CLASS__);
        $constants = $rc->getConstants();
        $themes    = [];
        foreach ($constants as $name => $constant) {
            if ('THEME_' === substr($name, 0, 6)) {
                $themes[] = $constant;
            }
        }

        return $themes;
    }

    public function isUsePush(): bool
    {
        return $this->usePush;
    }

    public function setUsePush(bool $usePush)
    {
        $this->usePush = $usePush;
    }
}
