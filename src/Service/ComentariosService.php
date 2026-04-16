<?php

namespace App\Service;

use DateTimeImmutable;
use DateInterval;
use DateTime;
use LogicException;
use Psr\Log\LoggerInterface;
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
    private $logger;

    public function __construct(ManagerRegistry $doctrine, UserPasswordEncoderInterface $encoder, LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->logger = $logger;
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
            $this->logger->error('[ComentariosService] Erro ao listar comentários', [
                'exception' => $e,
                'filters' => $filters
            ]);
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

            $this->logger->info('[ComentariosService] Comentário atualizado com sucesso', [
                'comentario_id' => $comentario->getId(),
                'user_id' => $comentario->getUsuario()->getId()
            ]);

            return $comentario;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            $this->logger->error('[ComentariosService] Erro ao atualizar comentário', [
                'comentario_id' => $comentario->getId(),
                'exception' => $th
            ]);
            throw $th;
        }
    }

    public function deleteComentarioUseCase(Comentario $comentario, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        $comentarioId = $comentario->getId();
        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($comentario);
            $entityManager->flush();
            $entityManager->getConnection()->commit();

            $this->logger->info('[ComentariosService] Comentário deletado com sucesso', [
                'comentario_id' => $comentarioId,
                'user_id' => $usuario->getId()
            ]);

            return $comentario;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            $this->logger->error('[ComentariosService] Erro ao deletar comentário', [
                'comentario_id' => $comentarioId,
                'user_id' => $usuario->getId(),
                'exception' => $th
            ]);
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

            $this->logger->info('[ComentariosService] Novo comentário criado', [
                'comentario_id' => $comentario->getId(),
                'user_id' => $comentario->getUsuario()->getId(),
                'post_id' => $comentario->getPost()->getId()
            ]);

            return $comentario;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            $this->logger->error('[ComentariosService] Erro ao criar comentário', [
                'user_id' => $comentario->getUsuario()->getId(),
                'post_id' => $comentario->getPost()->getId(),
                'exception' => $th
            ]);
            throw $th;
        }
    }

}
