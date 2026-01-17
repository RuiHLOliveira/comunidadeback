<?php

namespace App\Controller;

use Exception;
use DateTimeImmutable;
use App\Entity\Aula;
use PhpParser\JsonDecoder;
use App\Service\ModulosService;
use App\Service\AulasService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AulasController extends AbstractController
{
    
    private $aulasService;
    private $modulosService;

    public function __construct(AulasService $aulasService, ModulosService $modulosService)
    {
        $this->aulasService = $aulasService;
        $this->modulosService = $modulosService;
    }

    private function getFilters(Request $request)
    {
        $filters = [];
        $modulo = $request->query->get('modulo');
        if($modulo != ''){
            $filters['modulo'] = $modulo;
        }
        return $filters;
    }
    
    private function getOrderBy(Request $request)
    {
        $orderBy = null;
        if($request->query->get('orderBy') != null){
            $orderBy = $request->query->get('orderBy');
            $orderBy = explode(',', $orderBy);
            $orderBy = [$orderBy[0] => $orderBy[1]];
        }
        return $orderBy;
    }

    private function getProperties(Request $request)
    {
        $properties = explode(',',$request->query->get('properties'));
        foreach($properties as $key => $value) {
            $properties[$value] = true;
            unset($properties[$key]);
        }
        return $properties;
    }

    /**
     * @Route("/aulas", name="app_aulas_list", methods={"GET", "HEAD"})
     */
    public function index(Request $request): Response
    {
        try {
            $usuario = $this->getUser();

            $filters = $this->getFilters($request);
            $orderBy = $this->getOrderBy($request);

            $entityList = $this->aulasService->listaAulasUseCase($usuario, $filters, $orderBy);

            $properties = $this->getProperties($request);

            if(isset($properties['modulo']) && filter_var($properties['modulo'], FILTER_VALIDATE_BOOLEAN)) {
                $bp='';
                for ($i=0; $i < count($entityList); $i++) {
                    // serializar
                }
            }

            return new JsonResponse($entityList);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    private function validateCreateAulaData($requestData) {
        if( !property_exists($requestData, 'nome') || $requestData->nome == ''){
            throw new BadRequestHttpException("Nome não enviado.");
        }
        if( !property_exists($requestData, 'url') || $requestData->url == ''){
            throw new BadRequestHttpException("url não enviado.");
        }
        if( !property_exists($requestData, 'modulo') || $requestData->modulo == ''){
            throw new BadRequestHttpException("Modulo não enviado.");
        }
    }

    /**
     * @Route("/aulas", name="app_aulas_create", methods={"POST"})
     */
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            $this->validateCreateAulaData($requestData);

            $modulo = $this->validateModuloExiste($requestData->modulo, $usuario);

            // if(property_exists($requestData, 'aulapai') && $requestData->aulapai != null) {
            //     $aulaPai = $requestData->aulapai;
            //     $aulaPai = $this->validateAulaExiste($aulaPai, $usuario);
            // } else {
            //     $aulaPai = null;
            // }

            $aula = $this->aulasService->factoryAula($requestData->nome, $requestData->url, $modulo, $usuario);
            $aula = $this->aulasService->createNewAula($aula);
            return new JsonResponse($aula, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateUpdateAulaData($requestData) {
        if( !property_exists($requestData, 'nome') || $requestData->nome == ''){
            throw new BadRequestHttpException("Nome não enviado.");
        }
        if( !property_exists($requestData, 'url') || $requestData->url == ''){
            throw new BadRequestHttpException("Url não enviado.");
        }
        // if( !property_exists($requestData, 'hora') || $requestData->hora == ''){
        //     throw new BadRequestHttpException("Hora não enviada.");
        // }
    }

    /**
     * @Route("/aulas/{id}", name="app_aulas_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            $this->validateUpdateAulaData($requestData);

            $aula = $this->validateAulaExiste($id, $usuario);

            $aula->setNome($requestData->nome);
            $aula->setUrl($requestData->url);
            $this->aulasService->atualizaAulasUseCase($aula);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    
    /**
     * @Route("/aulas/{id}", name="app_aulas_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());

            $aula = $this->validateAulaExiste($id, $usuario);

            $this->aulasService->deleteAulaUseCase($aula, $usuario);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateAulaExiste($id, $usuario)
    {
        $aula = $this->aulasService->find($id, $usuario);
        if($aula == null) {
            throw new NotFoundHttpException('Aula não encontrado.');
        }
        return $aula;
    }
    
    private function validateModuloExiste($id, $usuario)
    {
        $modulo = $this->modulosService->find($id, $usuario);
        if($modulo == null) {
            throw new NotFoundHttpException('Modulo não encontrado.');
        }
        return $modulo;
    }

}
