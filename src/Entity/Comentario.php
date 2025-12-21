<?php

namespace App\Entity;

use DateTime;
use Exception;
use JsonSerializable;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ComentarioRepository;

/**
 * @ORM\Entity(repositoryClass=ComentarioRepository::class)
 */
class Comentario extends JsonSerializableEntity
{

    public function jsonSerialize()
    {
        $array = parent::jsonSerialize();
        $array['conteudo'] = $this->getConteudo();
        $array['comentariopai'] = $this->getComentariopai() != null ? $this->getComentariopai()->getId() : null;
        return $array;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $conteudo;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected $updated_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    protected $deleted_at;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comentarios")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $usuario;

    /**
     * @ORM\Column(type="integer", nullable="true")
     */
    protected $prioridade;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity=Comentario::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=true)
     */
    private $comentariopai;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConteudo(): ?string
    {
        return $this->conteudo;
    }

    public function setConteudo(?string $conteudo): self
    {
        $this->conteudo = $conteudo;

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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

        return $this;
    }


    /**
     * Get the value of comentariopai
     */ 
    public function getComentariopai()
    {
        return $this->comentariopai;
    }

    /**
     * Set the value of comentariopai
     *
     * @return  self
     */ 
    public function setComentariopai($comentariopai)
    {
        $this->comentariopai = $comentariopai;

        return $this;
    }
}
