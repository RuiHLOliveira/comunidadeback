<?php

namespace App\Controller;

use Exception;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
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
    private $logger;

    public function __construct(ComentariosService $comentariosService, PostsService $postsService, LoggerInterface $logger)
    {
        $this->comentariosService = $comentariosService;
        $this->postsService = $postsService;
        $this->logger = $logger;
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
        $properties = explode(',',$request->query->get('properties') ?? '');
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
            $this->logger->error('[ComentariosController] Erro ao listar comentários', [
                'user_id' => $this->getUser() ? $this->getUser()->getId() : 'anonymous',
                'exception' => $e
            ]);
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
            
            try {
                $this->validateCreateComentarioData($requestData);
            } catch (BadRequestHttpException $e) {
                $this->logger->warning('[ComentariosController] Falha na validação de criação de comentário', [
                    'user_id' => $usuario->getId(),
                    'data' => $requestData,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

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
        } catch (\Throwable $e) {
            $status = $e instanceof BadRequestHttpException || $e instanceof NotFoundHttpException ? Response::HTTP_BAD_REQUEST : Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($status >= 500) {
                $this->logger->error('[ComentariosController] Erro inesperado ao criar comentário', [
                    'user_id' => $usuario->getId(),
                    'exception' => $e
                ]);
            }
            return new JsonResponse(['message' => $e->getMessage()], $status);
        }
    }

    private function validateUpdateComentarioData($requestData) {
        if( !property_exists($requestData, 'conteudo') || $requestData->conteudo == ''){
            throw new BadRequestHttpException("Conteúdo não enviado.");
        }
    }

    /**
     * @Route("/comentarios/{id}", name="app_comentarios_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = json_decode($request->getContent());
            
            try {
                $this->validateUpdateComentarioData($requestData);
            } catch (BadRequestHttpException $e) {
                $this->logger->warning('[ComentariosController] Falha na validação de atualização de comentário', [
                    'comentario_id' => $id,
                    'user_id' => $usuario->getId(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }

            $comentario = $this->validateComentarioExiste($id, $usuario);

            $comentario->setConteudo($requestData->conteudo);
            $this->comentariosService->atualizaComentariosUseCase($comentario);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            $status = $e instanceof BadRequestHttpException || $e instanceof NotFoundHttpException ? Response::HTTP_BAD_REQUEST : Response::HTTP_INTERNAL_SERVER_ERROR;
            
            if ($status >= 500) {
                $this->logger->error('[ComentariosController] Erro inesperado ao atualizar comentário', [
                    'comentario_id' => $id,
                    'user_id' => $usuario->getId(),
                    'exception' => $e
                ]);
            }
            return new JsonResponse(['message' => $e->getMessage()], $status);
        }
    }

    
    /**
     * @Route("/comentarios/{id}", name="app_comentarios_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $comentario = $this->validateComentarioExiste($id, $usuario);

            $this->comentariosService->deleteComentarioUseCase($comentario, $usuario);

            return new JsonResponse();
            
        } catch (\Exception $e) {
            $status = $e instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : Response::HTTP_BAD_REQUEST;
            
            if ($status >= 500) {
                $this->logger->error('[ComentariosController] Erro inesperado ao deletar comentário', [
                    'comentario_id' => $id,
                    'user_id' => $usuario->getId(),
                    'exception' => $e
                ]);
            }
            return new JsonResponse(['message' => $e->getMessage()], $status);
        }
    }

    private function validateComentarioExiste($id, $usuario)
    {
        $comentario = $this->comentariosService->find($id, $usuario);
        if($comentario == null) {
            $this->logger->warning('[ComentariosController] Comentário não encontrado', [
                'comentario_id' => $id,
                'user_id' => $usuario->getId()
            ]);
            throw new NotFoundHttpException('Comentario não encontrado.');
        }
        return $comentario;
    }
    
    private function validatePostExiste($id, $usuario)
    {
        $post = $this->postsService->findOneBy($id, []);
        if($post == null) {
            $this->logger->warning('[ComentariosController] Post não encontrado ao criar comentário', [
                'post_id' => $id,
                'user_id' => $usuario->getId()
            ]);
            throw new NotFoundHttpException('Post não encontrado.');
        }
        return $post;
    }

}
