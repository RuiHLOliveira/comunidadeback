<?php

namespace App\Controller;

use Exception;
use DateTimeImmutable;
use App\Entity\Comentario;
use PhpParser\JsonDecoder;
use App\Service\PostsService;
use App\Service\ComentariosService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ComentariosController extends AbstractController
{
    
    private $comentariosService;
    private $postsService;

    public function __construct(ComentariosService $comentariosService, PostsService $postsService)
    {
        $this->comentariosService = $comentariosService;
        $this->postsService = $postsService;
    }

    private function getFilters(Request $request)
    {
        $filters = [];
        $post = $request->query->get('post');
        if($post != ''){
            $filters['post'] = $post;
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
     * @Route("/comentarios", name="app_comentarios_list", methods={"GET", "HEAD"})
     */
    public function index(Request $request): Response
    {
        try {
            $usuario = $this->getUser();

            $filters = $this->getFilters($request);
            $orderBy = $this->getOrderBy($request);

            $entityList = $this->comentariosService->listaComentariosUseCase($filters, $orderBy);

            $properties = $this->getProperties($request);

            if(isset($properties['post']) && filter_var($properties['post'], FILTER_VALIDATE_BOOLEAN)) {
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
    
    private function validateCreateComentarioData($requestData) {
        if( !property_exists($requestData, 'conteudo') || $requestData->conteudo == ''){
            throw new BadRequestHttpException("Conteúdo não enviado.");
        }
        if( !property_exists($requestData, 'post') || $requestData->post == ''){
            throw new BadRequestHttpException("Post não enviado.");
        }
    }

    /**
     * @Route("/comentarios", name="app_comentarios_create", methods={"POST"})
     */
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            $this->validateCreateComentarioData($requestData);

            $post = $this->validatePostExiste($requestData->post, $usuario);

            if(property_exists($requestData, 'comentariopai') && $requestData->comentariopai != null) {
                $comentarioPai = $requestData->comentariopai;
                $comentarioPai = $this->validateComentarioExiste($comentarioPai, $usuario);
            } else {
                $comentarioPai = null;
            }

            $comentario = $this->comentariosService->factoryComentario($requestData->conteudo, $comentarioPai, $post, $usuario);
            $comentario = $this->comentariosService->createNewComentario($comentario);
            return new JsonResponse($comentario, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateUpdateComentarioData($requestData) {
        if( !property_exists($requestData, 'conteudo') || $requestData->conteudo == ''){
            throw new BadRequestHttpException("Conteúdo não enviado.");
        }
        // if( !property_exists($requestData, 'hora') || $requestData->hora == ''){
        //     throw new BadRequestHttpException("Hora não enviada.");
        // }
    }

    /**
     * @Route("/comentarios/{id}", name="app_comentarios_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            $this->validateUpdateComentarioData($requestData);

            $comentario = $this->validateComentarioExiste($id, $usuario);

            $comentario->setConteudo($requestData->conteudo);
            $this->comentariosService->atualizaComentariosUseCase($comentario);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    
    /**
     * @Route("/comentarios/{id}", name="app_comentarios_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());

            $comentario = $this->validateComentarioExiste($id, $usuario);

            $this->comentariosService->deleteComentarioUseCase($comentario, $usuario);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function validateComentarioExiste($id, $usuario)
    {
        $comentario = $this->comentariosService->find($id, $usuario);
        if($comentario == null) {
            throw new NotFoundHttpException('Comentario não encontrado.');
        }
        return $comentario;
    }
    
    private function validatePostExiste($id, $usuario)
    {
        $post = $this->postsService->findOne($usuario, $id);
        if($post == null) {
            throw new NotFoundHttpException('Post não encontrado.');
        }
        return $post;
    }

}
