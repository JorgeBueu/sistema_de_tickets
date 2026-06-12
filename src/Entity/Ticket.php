<?php

namespace App\Entity;

use App\Enum\TicketStatus;
use App\Repository\TicketRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Debe introducir un título.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'El título debe tener al menos {{ limit }} caracteres',
        maxMessage: 'El título no debe tener mas de {{ limit }} carácteres'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(
        message: 'Debe introducir una descripción.'
    )]
    #[Assert\Length(
        min: 2,
        max: 2000,
        minMessage: 'La descripción debe tener al menos {{ limit }} caracteres',
        maxMessage: 'La descripción no debe tener mas de {{ limit }} carácteres'
    )]
    private ?string $description = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'createdTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\Column(enumType: TicketStatus::class)]
    private ?TicketStatus $status = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'ticket')]
    private Collection $comments;

    public function __construct()
    {
        // Cada vez que creamos un ticket se le setea la fecha y hora de creacion.
        $this->createdAt = new DateTimeImmutable();
        // Los tickets se crean con estado abierto
        $this->status = TicketStatus::OPEN;
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        // Hago un trim en el set por que no quiero que el usuario introduzca espacios al principio o al final.
        $this->title = trim($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = trim($description);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getStatus(): ?TicketStatus
    {
        return $this->status;
    }

    public function setStatus(TicketStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTicket($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTicket() === $this) {
                $comment->setTicket(null);
            }
        }

        return $this;
    }
}
