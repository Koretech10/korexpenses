<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[UniqueEntity(
    fields: "name",
    message: "Ce compte bancaire existe déjà."
)]
class Account
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?float $balance = null;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'account', orphanRemoval: true)]
    private Collection $transactions;

    #[ORM\OneToMany(targetEntity: MonthlyTransaction::class, mappedBy: 'account', orphanRemoval: true)]
    private Collection $monthlyTransactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->monthlyTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setAccount($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getAccount() === $this) {
                $transaction->setAccount(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MonthlyTransaction>
     */
    public function getMonthlyTransactions(): Collection
    {
        return $this->monthlyTransactions;
    }

    public function addMonthlyTransaction(MonthlyTransaction $monthlyTransaction): static
    {
        if (!$this->monthlyTransactions->contains($monthlyTransaction)) {
            $this->monthlyTransactions->add($monthlyTransaction);
            $monthlyTransaction->setAccount($this);
        }

        return $this;
    }

    public function removeMonthlyTransaction(MonthlyTransaction $monthlyTransaction): static
    {
        if ($this->monthlyTransactions->removeElement($monthlyTransaction)) {
            // set the owning side to null (unless already changed)
            if ($monthlyTransaction->getAccount() === $this) {
                $monthlyTransaction->setAccount(null);
            }
        }

        return $this;
    }
}
