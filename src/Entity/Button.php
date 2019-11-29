<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ReflectionClass;

/**
 * Class Button.
 *
 * This is the class that will be logically between the `View` and the `AbstractCommand`.
 *
 * It contains informations like the size that the command should be displayed.
 *
 * @ORM\Entity()
 * @ORM\Table(name="button")
 */
class Button
{
    /**
     * Value for a small button. This constant will be extracted into objects in a future-release.
     */
    public const SIZE_SMALL = 'small';

    /**
     * Value for a big button. This constant will be extracted into objects in a future-release.
     */
    public const SIZE_BIG = 'big';

    /**
     * This is the id that will be placed in the database after the persisting of this object.
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * The size of the button.
     *
     * Currently holds the value of one of the size constants in this class.
     *
     * @ORM\Column(name="size", type="string")
     *
     * @var string
     */
    private $size;

    /**
     * This is the command that this button will be triggering.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\AbstractCommand")
     * @ORM\JoinColumn(name="command_id", referencedColumnName="id")
     *
     * @var AbstractCommand
     */
    private $command;

    /**
     * One button can only be possessed by 1 view. Otherwise, when changing the button, it will be changed in every
     * usage of the button.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\View", inversedBy="buttons")
     * @ORM\JoinColumn(name="view_id", referencedColumnName="id")
     *
     * @var View
     */
    private $view;

    /**
     * Button constructor.
     *
     * @param null $size
     */
    public function __construct(AbstractCommand $command, $size = null)
    {
        $this->command = $command;
        $this->size    = self::SIZE_SMALL; // Set default, if $size is a random string.
        $this->setSize($size);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * TODO: Refactor, replace with static array.
     *
     * This method takes advantage of the `\ReflextionClass`. This allows the method to read how this class is
     * build up.
     *
     * In this case, it reads the constants of this class and filters them for constants that start with the name
     * "SIZE_".
     *
     * When the passed value is equal to one of those constants, the value will be set. This will ensure that there is
     * no invalid data in the `size`-field in this class.
     *
     * @see [PHP Magic Methods](http://php.net/manual/en/language.oop5.magic.php)
     */
    public function setSize(string $size): void
    {
        $rc        = new ReflectionClass(__CLASS__);
        $constants = $rc->getConstants();
        $sizes     = [];
        foreach ($constants as $name => $constant) {
            if ('SIZE_' === substr($name, 0, 5)) {
                $sizes[] = $constant;
            }
        }
        if (in_array($size, $sizes)) {
            $this->size = $size;
        }
    }

    /**
     * @return AbstractCommand
     */
    public function getCommand(): ?AbstractCommand
    {
        return $this->command;
    }

    public function setCommand(AbstractCommand $command): void
    {
        $this->command = $command;
    }

    /**
     * @return View
     */
    public function getView(): ?View
    {
        return $this->view;
    }

    public function setView(View $view): void
    {
        $this->view = $view;
    }
}
