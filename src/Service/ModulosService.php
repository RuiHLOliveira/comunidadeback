<?php

namespace App\Service;

use DateTimeImmutable;
use DateInterval;
use DateTime;
use LogicException;
use App\Entity\Curso;
use App\Entity\User;
use App\Entity\Modulo;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ModulosService
{
    private $doctrine;
    private $encoder;

    public function __construct(ManagerRegistry $doctrine,  UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    /**
     * @param User $usuario
     * @param array $orderBy
     * @return array
     */
    public function findAll(User $usuario, array $filters = [], array $orderBy = null): array
    {
        $filters['usuario'] = $usuario;
        return $this->doctrine->getRepository(Modulo::class)->findBy($filters, $orderBy);
    }

    /**
     * @param string $idModulo
     * @param User $usuario
     */
    public function find(string $idModulo, User $usuario): Modulo
    {
        $modulo = $this->doctrine->getRepository(Modulo::class)->findOneBy([
            'id' => $idModulo,
            'usuario' => $usuario
        ]);
        return $modulo;
    }

    /**
     * @param User $usuario
     * @param array $orderBy
     * @return array<Modulo>
     */
    public function listaModulosUseCase(User $usuario, array $filters = [], array $orderBy = null): array
    {
        try {
            $modulos = $this->findAll($usuario, $filters, $orderBy);
            return $modulos;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * @param Modulo $modulo
     * @return Modulo
     */
    public function atualizaModulosUseCase(Modulo $modulo): Modulo
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $modulo->setUpdatedAt(new DateTimeImmutable());
            $entityManager->persist($modulo);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $modulo;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function deleteModuloUseCase(Modulo $modulo, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($modulo);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $modulo;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }


    /**
     * @param string $descricao
     * @param string $motivo
     * @param int $cursoId
     * @param string $datahora
     * @param User $usuario
     * @return Modulo
     */
    public function factoryModulo($nome, Curso $curso, User $usuario)
    {
        $modulo = new Modulo();
        $modulo->setUsuario($usuario);
        $modulo->setNome($nome);
        $modulo->setCurso($curso);
        return $modulo;
    }

    public function createNewModulo(Modulo $modulo)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $modulo->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($modulo);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $modulo;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}