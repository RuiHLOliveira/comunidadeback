<?php

namespace App\Controller;

use Exception;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use App\Entity\Curso;
use App\Service\CursosService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CursosController extends AbstractController
{

    /**
     * @var CursosService
     */
    private $cursosService;

    public function __construct(
        CursosService $cursosService
    ) {
        $this->cursosService = $cursosService;
    }

    /**
     * @Route("/cursos", name="app_cursos_list", methods={"GET","HEAD"})
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $orderBy = [];
            if($request->query->get('orderBy') != null){
                $orderBy = $request->query->get('orderBy');
                $orderBy = explode(',', $orderBy);
                $orderBy = [$orderBy[0] => $orderBy[1]];
            }

            $filters = [];
            // if($request->query->get('situacao') != null){
            //     $value = $request->query->get('situacao');
            //     $filters['situacao'] = $value;
            // }
            // if($request->query->get('prioridade') != null){
            //     $value = $request->query->get('prioridade');
            //     $filters['prioridade'] = $value;
            // }

            $cursos = $this->cursosService->listaCursosUseCase($usuario, $filters, $orderBy);

            // $loadTarefas = $request->query->get('loadTarefas');
            // if(filter_var($loadTarefas, FILTER_VALIDATE_BOOLEAN)) {
            //     for ($i=0; $i < count($cursos); $i++) {
            //         $cursos[$i]->serializarTarefas();
            //     }
            // }
            // $loadCursosfotos = $request->query->get('loadCursosfotos');
            // if(filter_var($loadCursosfotos, FILTER_VALIDATE_BOOLEAN)) {
            //     for ($i=0; $i < count($cursos); $i++) {
            //         $cursos[$i]->serializarCursosfotos();
            //     }
            // }

            return new JsonResponse($cursos);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Error $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateCreate($request)
    {
        if(!property_exists($request, 'nome') || $request->nome == null || $request->nome == ''){
            throw new Exception('Nome não pode ser vazio.');
        }
    }

    /**
     * @Route("/cursos", name="app_cursos_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $requestContent = $request->getContent();
            $requestObj = json_decode($requestContent);
            $usuario = $this->getUser();

            $this->validateCreate($requestObj);
            
            $curso = $this->cursosService->factoryCreateCursoUsecase(
                $requestObj->nome,
            );

            $curso = $this->cursosService->createCursoUsecase($curso, $usuario);

            return new JsonResponse($curso, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Error $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateUpdate($request)
    {
        if($request->nome == null || $request->nome == ''){
            throw new Exception('Nome não pode ser vazio');
        }
    }

    private function fillUpdateCurso($request, Curso $curso)
    {
        $curso->setNome($request->nome);
        return $curso;
    }

    /**
     * @Route("/cursos/{id}", name="app_cursos_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $requestContent = $request->getContent();
            $requestObj = json_decode($requestContent);
            $usuario = $this->getUser();

            $this->validateUpdate($requestObj);
            $curso = $this->cursosService->findOne($usuario, $id);
            $curso = $this->fillUpdateCurso($requestObj, $curso);

            $curso = $this->cursosService->updateCurso($curso, $usuario);

            return new JsonResponse($curso, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Error $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/cursos/{id}", name="app_cursos_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $curso = $this->cursosService->findOne($usuario, $id);
            $curso = $this->cursosService->deleteCurso($curso, $usuario);
            return new JsonResponse($curso, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Error $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}