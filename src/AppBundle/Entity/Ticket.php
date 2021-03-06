<?php

namespace AppBundle\Entity;

use AppBundle\TicketRepository;
use ArrayObject;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ticket
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity(repositoryClass="AppBundle\TicketRepository")
 */
class Ticket
{
    const WAITING = 1;
    const IN_PROGRESS = 2;
    const CLOSED = 3;
    const CANCELED = 4;

    static private $statusNames = [
        self::WAITING => 'nowe',
        self::IN_PROGRESS => 'w trakcie',
        self::CANCELED => 'odrzucone',
        self::CLOSED => 'zakończone',
    ];

    static private $allowedTransitions = [
        self::WAITING => [self::IN_PROGRESS, self::CANCELED],
        self::IN_PROGRESS => [self::CLOSED, self::CANCELED],
        self::CANCELED => [],
        self::CLOSED => [],
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var Location
     *
     * @ORM\Embedded(class="Location")
     */
    private $location;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="ticket")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="Attachment", mappedBy="ticket")
     */
    private $attachments;

    /**
     * @ORM\OneToOne(targetEntity="Notifier",  mappedBy="ticket", cascade={"all"}))
     */
    private $notifier;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Ticket constructor.
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Ticket
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     *
     * @return Ticket
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     *
     * @return Ticket
     */
    public function setUpdated(DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set status
     *
     * @param integer $newStatus
     *
     * @return Ticket
     */
    public function setStatus($newStatus)
    {
        if (self::isTransitionAllowed($this->status, $newStatus)) {
            $this->status = $newStatus;
        } else {
            throw new \InvalidArgumentException("This status change is not allowed.");
        }

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set location
     *
     * @param Location $location
     *
     * @return Ticket
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return Ticket
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        if (array_key_exists($this->getStatus(), self::$statusNames)) {
            return self::$statusNames[$this->getStatus()];
        } else {
            return 'nieznane';
        }
    }

    /**
     * Set comments
     *
     * @param ArrayCollection $comments
     *
     * @return Ticket
     */
    public function setComments(ArrayCollection $comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isWaiting() || $this->isInProgress();
    }

    /**
     * @return bool
     */
    public function isWaiting()
    {
        return $this->getStatus() === self::WAITING;
    }

    /**
     * @return bool
     */
    public function isInProgress()
    {
        return $this->getStatus() === self::IN_PROGRESS;
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->getStatus() === self::CLOSED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getStatus() === self::CANCELED;
    }

    /**
     * Set attachments
     *
     * @param ArrayCollection $attachments
     *
     * @return Ticket
     */
    public function setAttachments(ArrayCollection $attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments
     *
     * @return ArrayCollection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param Notifier $notifier
     * @return $this
     */
    public function setNotifier(Notifier $notifier)
    {
        $this->notifier = $notifier;

        return $this;
    }

    /**
     * @return Notifier
     */
    public function getNotifier()
    {
        return $this->notifier;
    }

    /**
     * Checks whether transition to a new status is allowed.
     *
     * @param int|null $oldStatus
     * @param int $newStatus
     *
     * @return bool
     */
    static public function isTransitionAllowed($oldStatus, int $newStatus)
    {
        if ($oldStatus === null) {
            if ($newStatus !== self::WAITING) {
                throw new \InvalidArgumentException('New tickets can only have status set to Waiting.');
            } else {
                return true;
            }
        }
        if (!array_key_exists($oldStatus, self::$allowedTransitions)) {
            throw new \InvalidArgumentException('Current status is invalid');
        }

        return in_array($newStatus, self::$allowedTransitions[$oldStatus], true);
    }
}
