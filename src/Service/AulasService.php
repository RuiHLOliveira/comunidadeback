<?php

namespace App\Service;

use DateTimeImmutable;
use DateInterval;
use DateTime;
use LogicException;
use App\Entity\Modulo;
use App\Entity\User;
use App\Entity\Aula;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AulasService
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
        return $this->doctrine->getRepository(Aula::class)->findBy($filters, $orderBy);
    }

    /**
     * @param string $idAula
     * @param User $usuario
     */
    public function find(string $idAula, User $usuario): Aula
    {
        $aula = $this->doctrine->getRepository(Aula::class)->findOneBy([
            'id' => $idAula,
            'usuario' => $usuario
        ]);
        return $aula;
    }

    /**
     * @param User $usuario
     * @param array $orderBy
     * @return array<Aula>
     */
    public function listaAulasUseCase(User $usuario, array $filters = [], array $orderBy = null): array
    {
        try {
            $aulas = $this->findAll($usuario, $filters, $orderBy);
            return $aulas;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * @param Aula $aula
     * @return Aula
     */
    public function atualizaAulasUseCase(Aula $aula): Aula
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $aula->setUpdatedAt(new DateTimeImmutable());
            $entityManager->persist($aula);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $aula;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function deleteAulaUseCase(Aula $aula, User $usuario)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($aula);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $aula;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }


    /**
     * @param string $descricao
     * @param string $motivo
     * @param int $moduloId
     * @param string $datahora
     * @param User $usuario
     * @return Aula
     */
    public function factoryAula($nome, $url, Modulo $modulo, User $usuario)
    {
        $aula = new Aula();
        $aula->setUsuario($usuario);
        $aula->setNome($nome);
        $aula->setUrl($url);
        $aula->setModulo($modulo);
        return $aula;
    }

    public function createNewAula(Aula $aula)
    {
        $entityManager = $this->doctrine->getManager();
        try {
            $entityManager->getConnection()->beginTransaction();
            $aula->setCreatedAt(new DateTimeImmutable());
            $entityManager->persist($aula);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $aula;
        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

}