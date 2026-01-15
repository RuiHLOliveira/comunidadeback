<?php

namespace App\Controller;

use Exception;
use DateTimeImmutable;
use App\Entity\Modulo;
use PhpParser\JsonDecoder;
use App\Service\CursosService;
use App\Service\ModulosService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ModulosController extends AbstractController
{
    
    private $modulosService;
    private $cursosService;

    public function __construct(ModulosService $modulosService, CursosService $cursosService)
    {
        $this->modulosService = $modulosService;
        $this->cursosService = $cursosService;
    }

    private function getFilters(Request $request)
    {
        $filters = [];
        $curso = $request->query->get('curso');
        if($curso != ''){
            $filters['curso'] = $curso;
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
     * @Route("/modulos", name="app_modulos_list", methods={"GET", "HEAD"})
     */
    public function index(Request $request): Response
    {
        try {
            $usuario = $this->getUser();

            $filters = $this->getFilters($request);
            $orderBy = $this->getOrderBy($request);

            $entityList = $this->modulosService->listaModulosUseCase($usuario, $filters, $orderBy);

            $properties = $this->getProperties($request);

            if(isset($properties['curso']) && filter_var($properties['curso'], FILTER_VALIDATE_BOOLEAN)) {
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
    
    private function validateCreateModuloData($requestData) {
        if( !property_exists($requestData, 'nome') || $requestData->nome == ''){
            throw new BadRequestHttpException("Nome não enviado.");
        }
        if( !property_exists($requestData, 'curso') || $requestData->curso == ''){
            throw new BadRequestHttpException("Curso não enviado.");
        }
    }

    /**
     * @Route("/modulos", name="app_modulos_create", methods={"POST"})
     */
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            $this->validateCreateModuloData($requestData);

            $curso = $this->validateCursoExiste($requestData->curso, $usuario);

            // if(property_exists($requestData, 'modulopai') && $requestData->modulopai != null) {
            //     $moduloPai = $requestData->modulopai;
            //     $moduloPai = $this->validateModuloExiste($moduloPai, $usuario);
            // } else {
            //     $moduloPai = null;
            // }

            $modulo = $this->modulosService->factoryModulo($requestData->nome, $curso, $usuario);
            $modulo = $this->modulosService->createNewModulo($modulo);
            return new JsonResponse($modulo, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateUpdateModuloData($requestData) {
        if( !property_exists($requestData, 'nome') || $requestData->nome == ''){
            throw new BadRequestHttpException("Nome não enviado.");
        }
        // if( !property_exists($requestData, 'hora') || $requestData->hora == ''){
        //     throw new BadRequestHttpException("Hora não enviada.");
        // }
    }

    /**
     * @Route("/modulos/{id}", name="app_modulos_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            $this->validateUpdateModuloData($requestData);

            $modulo = $this->validateModuloExiste($id, $usuario);

            $modulo->setNome($requestData->nome);
            $this->modulosService->atualizaModulosUseCase($modulo);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    
    /**
     * @Route("/modulos/{id}", name="app_modulos_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());

            $modulo = $this->validateModuloExiste($id, $usuario);

            $this->modulosService->deleteModuloUseCase($modulo, $usuario);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateModuloExiste($id, $usuario)
    {
        $modulo = $this->modulosService->find($id, $usuario);
        if($modulo == null) {
            throw new NotFoundHttpException('Modulo não encontrado.');
        }
        return $modulo;
    }
    
    private function validateCursoExiste($id, $usuario)
    {
        $curso = $this->cursosService->findOne($usuario, $id);
        if($curso == null) {
            throw new NotFoundHttpException('Curso não encontrado.');
        }
        return $curso;
    }

}
