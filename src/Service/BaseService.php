<?php

namespace App\Service;

use App\Entity\Dia;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Atividade;
use App\Entity\Configuracao;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BaseService
{
    protected $className;
    protected $doctrine;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(string $className, ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $this->doctrine->getManager();
        $this->className = $className;
    }

    protected function getRepository()
    {
        return $this->doctrine->getRepository($this->className);
    }

    protected function beginTransaction()
    {
        $this->entityManager->getConnection()->beginTransaction();
    }
    protected function commit()
    {
        $this->entityManager->getConnection()->commit();
    }
    protected function rollback()
    {
        $this->entityManager->getConnection()->rollback();
    }

}