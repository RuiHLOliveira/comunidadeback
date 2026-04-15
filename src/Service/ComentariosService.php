<?php

namespace App\Service;

use DateTimeImmutable;
use DateInterval;
use DateTime;
use LogicException;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Comentario;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ComentariosService
{
    
    private $doctrine;
    private $encoder;

    public function __construct(ManagerRegistry $doctrine,  UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    /**
     * @param array $orderBy
     * @return array
     */
    public function findAll(array $filters = [], array $orderBy = null): array
    {
        return $this->doctrine->getRepository(Comentario::class)->findBy($filters, $orderBy);
    }

    /**
     * @param string $idComentario
     */
    public function find(string $idComentario): ?Comentario
    {
        return $this->doctrine->getRepository(Comentario::class)->find($idComentario);
    }

    /**
     * @param array $orderBy
     * @return array<Comentario>
     */
    public function listaComentariosUseCase(array $filters = [], array $orderBy = null): array
    {
        try {
            $comentarios = $this->findAll($filters, $orderBy);
            return $comentarios;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * @param Comentario $comentario
     * @return Comentario
     */
    public function atualizaComentariosUseCase(Comentario $comentario): Comentario
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $comentario->setUpdatedAt(new DateTimeImmutable());
            $entityManager->persist($comentario);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $comentario;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function deleteComentarioUseCase(Comentario $comentario, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($comentario);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $comentario;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }


    /**
     * @param string $descricao
     * @param string $motivo
     * @param int $postId
     * @param string $datahora
     * @param User $usuario
     * @return Comentario
     */
    public function factoryComentario($conteudo, $comentarioPai, Post $post, User $usuario) {

        $comentario = new Comentario();
        $comentario->setUsuario($usuario);
        $comentario->setConteudo($conteudo);
        $comentario->setComentariopai($comentarioPai);
        $comentario->setPost($post);
        return $comentario;
    }

    public function createNewComentario(Comentario $comentario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $comentario->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($comentario);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $comentario;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}