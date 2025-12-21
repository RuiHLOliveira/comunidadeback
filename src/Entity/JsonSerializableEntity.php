<?php

namespace App\Entity;

use JsonSerializable;
use App\Repository\HoraRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

abstract class JsonSerializableEntity implements JsonSerializable
{
    public function jsonSerialize()
    {
        $createdAt = $this->getCreatedAt() != null ? $this->getCreatedAt()->format('Y-m-d H:i:sO') : null;
        $updatedAt = $this->getUpdatedAt() != null ? $this->getUpdatedAt()->format('Y-m-d H:i:sO') : null;
        $deletedAt = $this->getDeletedAt() != null ? $this->getDeletedAt()->format('Y-m-d H:i:sO') : null;
        $array = [
            'id' => $this->getId(),
            'createdAt' => $createdAt,
            'updatedAt' => $updatedAt,
            'deletedAt' => $deletedAt,
        ];
        
        if(!($this instanceof User)) {
            $array['usuario'] = [
                'id' => $this->getUsuario()->getId(),
                'email' => $this->getUsuario()->getEmail(),
                'nome' => $this->getUsuario()->getNome(),
                'apelido' => $this->getUsuario()->getApelido(),
            ];
        }

        return $array;
    }

    protected $id;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;
    protected $usuario;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): self
    {
        $this->deleted_at = $deleted_at;

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

}
