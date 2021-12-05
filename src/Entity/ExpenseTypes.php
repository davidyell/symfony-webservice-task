<?php

namespace App\Entity;

use App\Repository\ExpenseTypesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseTypesRepository::class)
 */
class ExpenseTypes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Expenses::class, mappedBy="type")
     */
    private $expense;

    public function __construct()
    {
        $this->expense = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Expenses[]
     */
    public function getExpense(): Collection
    {
        return $this->expense;
    }

    public function addExpense(Expenses $expense): self
    {
        if (!$this->expense->contains($expense)) {
            $this->expense[] = $expense;
            $expense->setType($this);
        }

        return $this;
    }

    public function removeExpense(Expenses $expense): self
    {
        if ($this->expense->removeElement($expense)) {
            // set the owning side to null (unless already changed)
            if ($expense->getType() === $this) {
                $expense->setType(null);
            }
        }

        return $this;
    }
}
