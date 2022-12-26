<?php

namespace App\Entity;

use App\Repository\ContaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContaRepository::class)]
class Conta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numero = null;

    #[ORM\Column]
    private ?float $saldo = null;

    #[ORM\ManyToOne(inversedBy: 'contas')]
    private ?User $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'contas')]
    private ?Agencia $agencia = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\ManyToOne(inversedBy: 'contas')]
    private ?TipoConta $tipo = null;

    #cascade
    #[ORM\OneToMany(mappedBy: 'destinatario', targetEntity: Transacao::class, cascade: ['remove'])]

    private Collection $transacaos;

    public function __construct()
    {
        $this->transacaos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getSaldo(): ?float
    {
        return $this->saldo;
    }

    public function setSaldo(float $saldo): self
    {
        $this->saldo = $saldo;

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(?User $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getAgencia(): ?Agencia
    {
        return $this->agencia;
    }

    public function setAgencia(?Agencia $agencia): self
    {
        $this->agencia = $agencia;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getTipo(): ?TipoConta
    {
        return $this->tipo;
    }

    public function setTipo(?TipoConta $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * @return Collection<int, Transacao>
     */
    public function getTransacaos(): Collection
    {
        return $this->transacaos;
    }

    public function addTransacao(Transacao $transacao): self
    {
        if (!$this->transacaos->contains($transacao)) {
            $this->transacaos->add($transacao);
            $transacao->setDestinatario($this);
        }

        return $this;
    }

    #debitar
    public function debitar(float $valor): self
    {
        $this->saldo -= $valor;

        return $this;
    }

    #creditar
    public function creditar(float $valor): self
    {
        $this->saldo += $valor;

        return $this;
    }
    #transferir
    public function transferir(float $valor, Conta $conta): self
    {
        $this->debitar($valor);
        $conta->creditar($valor);

        return $this;
    }

    public function removeTransacao(Transacao $transacao): self
    {
        if ($this->transacaos->removeElement($transacao)) {
            // set the owning side to null (unless already changed)
            if ($transacao->getDestinatario() === $this) {
                $transacao->setDestinatario(null);
            }
        }

        return $this;
    }
}
