<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Post;
use Doctrine\Persistence\ManagerRegistry;

class PostsService
{
    private ManagerRegistry $doctrine;

    public function __construct(
        ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * @param array $orderBy
     * @return array
     */
    public function findAll(array $filter = [], array $orderBy = []): array
    {
        return $this->doctrine->getRepository(Post::class)->findBy($filter, $orderBy);
    }

    
    /**
     * @param User $usuario
     * @param array $orderBy
     * @return array
     */
    public function findAllByUsuario(User $usuario, array $filter = [], array $orderBy = []): array
    {
        $filter['usuario'] = $usuario;
        return $this->doctrine->getRepository(Post::class)->findBy($filter, $orderBy);
    }

    /**
     * @param User $usuario
     * @param integer $id
     * @return Post|null
     */
    public function findOneBy($id, $criteria): ?Post
    {
        $criteria['id'] = $id;
        $data = $this->doctrine->getRepository(Post::class)->findOneBy($criteria);
        return $data;
    }

    /**
     * @param array $filters
     * @param array $orderBy
     * @return array<Post>
     */
    public function listaPostsUseCase(array $filters = [], array $orderBy = []) : array
    {
        try {
            $posts = $this->findAll($filters, $orderBy);
            return $posts;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param User $usuario
     * @param array $filters
     * @param array $orderBy
     * @return array<Post>
     */
    public function listaPostsByUsuarioUseCase(User $usuario, array $filters = [], array $orderBy = []) : array
    {
        try {
            $posts = $this->findAllByUsuario($usuario, $filters, $orderBy);
            return $posts;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function factoryCreatePostUsecase($nome, $introducao, $conteudo)
    {
        $post = new Post();
        $post->setNome($nome);
        $post->setConteudo($conteudo);
        $post->setIntroducao($introducao);
        return $post;
    }

    public function createPostUseCase(Post $post, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            
            $this->baseCreate($post, $usuario);
            
            $entityManager->getConnection()->commit();
            return $post;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function baseCreate(Post $post, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            
            $post->setCreatedAt(new DateTimeImmutable());
            $post->setUsuario($usuario);

            $entityManager->persist($post);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $post;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function updatePost(Post $post, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            
            $post->setUpdatedAt(new DateTimeImmutable());

            $entityManager->persist($post);
            
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $post;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function deletePost(Post $post, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();

            $entityManager->remove($post);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $post;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}