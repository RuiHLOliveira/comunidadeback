<?php

namespace App\Service;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Curso;
use Doctrine\Persistence\ManagerRegistry;

class CursosService
{
    private ManagerRegistry $doctrine;

    public function __construct(
        ManagerRegistry $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * @param User $usuario
     * @param array $orderBy
     * @return array
     */
    public function findAll(User $usuario, array $filter = [], array $orderBy = []): array
    {
        $filter['usuario'] = $usuario;
        return $this->doctrine->getRepository(Curso::class)->findBy($filter, $orderBy);
    }

    /**
     * @param User $usuario
     * @param integer $id
     * @return Curso
     */
    public function findOne(User $usuario, $id): Curso
    {
        $criteria['usuario'] = $usuario;
        $criteria['id'] = $id;
        return $this->doctrine->getRepository(Curso::class)->findOneBy($criteria);
    }

    /**
     * @param User $usuario
     * @param array $filters
     * @param array $orderBy
     * @return array<Curso>
     */
    public function listaCursosUseCase(User $usuario, array $filters = [], array $orderBy = []) : array
    {
        try {
            $Cursos = $this->findAll($usuario, $filters, $orderBy);
            return $Cursos;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function factoryCreateCursoUsecase($nome)
    {
        $Curso = new Curso();
        $Curso->setNome($nome);
        return $Curso;
    }

    public function createCursoUseCase(Curso $Curso, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $this->baseCreate($Curso, $usuario);
            $entityManager->getConnection()->commit();
            return $Curso;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function baseCreate(Curso $Curso, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $Curso->setCreatedAt(new DateTimeImmutable());
            $Curso->setUsuario($usuario);
            $entityManager->persist($Curso);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $Curso;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function updateCurso(Curso $Curso, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $Curso->setUpdatedAt(new DateTimeImmutable());
            $entityManager->persist($Curso);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $Curso;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function deleteCurso(Curso $Curso, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($Curso);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $Curso;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}