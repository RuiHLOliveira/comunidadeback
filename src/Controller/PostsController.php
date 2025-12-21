<?php

namespace App\Controller;

use Exception;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use App\Entity\Post;
use App\Service\PostsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostsController extends AbstractController
{

    /**
     * @var PostsService
     */
    private $postsService;

    public function __construct(
        PostsService $postsService
    ) {
        $this->postsService = $postsService;
    }

    /**
     * @Route("/posts", name="app_posts_list", methods={"GET","HEAD"})
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

            $posts = $this->postsService->listaPostsUseCase($usuario, $filters, $orderBy);

            // $loadTarefas = $request->query->get('loadTarefas');
            // if(filter_var($loadTarefas, FILTER_VALIDATE_BOOLEAN)) {
            //     for ($i=0; $i < count($posts); $i++) {
            //         $posts[$i]->serializarTarefas();
            //     }
            // }
            // $loadPostsfotos = $request->query->get('loadPostsfotos');
            // if(filter_var($loadPostsfotos, FILTER_VALIDATE_BOOLEAN)) {
            //     for ($i=0; $i < count($posts); $i++) {
            //         $posts[$i]->serializarPostsfotos();
            //     }
            // }

            return new JsonResponse($posts);
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
        if(!property_exists($request, 'introducao') || $request->introducao == null || $request->introducao == ''){
            throw new Exception('Introducao não pode ser vazio.');
        }
        if(strlen($request->introducao) > 255){
            throw new Exception('Introducao deve ser menor que 255 caracteres.');
        }
        if(!property_exists($request, 'conteudo') || $request->conteudo == null || $request->conteudo == ''){
            throw new Exception('Conteúdo não pode ser vazio.');
        }
    }

    /**
     * @Route("/posts", name="app_posts_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $requestContent = $request->getContent();
            $requestObj = json_decode($requestContent);
            $usuario = $this->getUser();

            $this->validateCreate($requestObj);
            
            $post = $this->postsService->factoryCreatePostUsecase(
                $requestObj->nome,
                $requestObj->introducao,
                $requestObj->conteudo,
            );

            $post = $this->postsService->createPostUsecase($post, $usuario);

            return new JsonResponse($post, Response::HTTP_CREATED);
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
        if(!property_exists($request, 'introducao') || $request->introducao == null || $request->introducao == ''){
            throw new Exception('Introducao não pode ser vazio.');
        }
        if(strlen($request->introducao) > 255){
            throw new Exception('Introducao deve ser menor que 255 caracteres.');
        }
        if($request->conteudo == null || $request->conteudo == ''){
            throw new Exception('Conteúdo não pode ser vazio');
        }
    }

    private function fillUpdatePost($request, Post $post)
    {
        $post->setNome($request->nome);
        $post->setIntroducao($request->introducao);
        $post->setConteudo($request->conteudo);
        return $post;
    }

    /**
     * @Route("/posts/{id}", name="app_posts_update", methods={"PUT"})
     */
    public function update($id, Request $request): JsonResponse
    {
        try {
            $requestContent = $request->getContent();
            $requestObj = json_decode($requestContent);
            $usuario = $this->getUser();

            $this->validateUpdate($requestObj);
            $post = $this->postsService->findOne($usuario, $id);
            $post = $this->fillUpdatePost($requestObj, $post);

            $post = $this->postsService->updatePost($post, $usuario);

            return new JsonResponse($post, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Error $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/posts/{id}", name="app_posts_delete", methods={"DELETE"})
     */
    public function delete($id, Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $post = $this->postsService->findOne($usuario, $id);
            $post = $this->postsService->deletePost($post, $usuario);
            return new JsonResponse($post, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Error $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}