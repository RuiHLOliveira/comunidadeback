<?php

namespace App\Entity;

use App\Entity\Tag;
use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User extends SerializableEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    public function jsonSerialize(): array
    {
        $array = parent::jsonSerialize();
        $array['id'] = $this->getId();
        $array['email'] = $this->getEmail();
        $array['informacoes'] = $this->getInformacoes();
        return $array;
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $nome;
    
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $apelido;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $informacoes = [];

    /**
     * @ORM\OneToMany(targetEntity=Comentario::class, mappedBy="usuario")
     */
    private $comentarios;
    
    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="usuario")
     */
    private $posts;
    

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comentarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUsuario($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUsuario() === $this) {
                $post->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, comentario>
     */
    public function getComentarios(): Collection
    {
        return $this->comentarios;
    }

    public function addComentario(comentario $comentario): self
    {
        if (!$this->comentarios->contains($comentario)) {
            $this->comentarios[] = $comentario;
            $comentario->setUsuario($this);
        }

        return $this;
    }

    public function removeComentario(comentario $comentario): self
    {
        if ($this->comentarios->removeElement($comentario)) {
            // set the owning side to null (unless already changed)
            if ($comentario->getUsuario() === $this) {
                $comentario->setUsuario(null);
            }
        }

        return $this;
    }



    /**
     * Get the value of nome
     */ 
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set the value of nome
     *
     * @return  self
     */ 
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get the value of apelido
     */ 
    public function getApelido()
    {
        return $this->apelido;
    }

    /**
     * Set the value of apelido
     *
     * @return  self
     */ 
    public function setApelido($apelido)
    {
        $this->apelido = $apelido;

        return $this;
    }

    public function getInformacoes(): ?array
    {
        return $this->informacoes;
    }

    public function setInformacoes(?array $informacoes): self
    {
        $this->informacoes = $informacoes;

        return $this;
    }
}
