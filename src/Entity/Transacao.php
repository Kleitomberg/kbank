<?php

namespace App\Entity;

use App\Repository\TransacaoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransacaoRepository::class)]
class Transacao
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $descricao = null;

    #[ORM\Column(length: 255)]
    private ?string $valor = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $data = null;

    #[ORM\ManyToOne(inversedBy: 'transacaos')]
    private ?Conta $destinatario = null;

    #[ORM\ManyToOne(inversedBy: 'transacaos')]
    private ?Conta $remetente = null;

    public function __construct()
    {
        $this->data = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getValor(): ?string
    {
        return $this->valor;
    }

    public function setValor(string $valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getData(): ?\DateTimeInterface
    {
        return $this->data;
    }

    public function setData(\DateTimeInterface $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getDestinatario(): ?Conta
    {
        return $this->destinatario;
    }

    public function setDestinatario(?Conta $destinatario): self
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    public function getRemetente(): ?Conta
    {
        return $this->remetente;
    }

    public function setRemetente(?Conta $remetente): self
    {
        $this->remetente = $remetente;

        return $this;
    }
}
