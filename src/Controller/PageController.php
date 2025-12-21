<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PageController extends AbstractController
{

    public function __construct()
    {
    }

    private function isLoggedIn(){
        $loggedIn = false;
        return $loggedIn;
        if(!$loggedIn){
            return $this->redirectToRoute('app_page_login');
        }
        return null;
    }

    private function getHtmlFromTemplate($templateName){
        $file = new File('./../templates/'.$templateName);
        $html = $file->getContent();
        return $html;
    }


    /**
     * @Route("/app", name="app_page_root", methods={"GET","HEAD"})
     */
    public function root(Request $request): Response
    {
        try {
            if(!$this->isLoggedIn()) return $this->redirectToRoute('app_page_login');
            $this->redirectToRoute('app_page_home');
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    /**
     * @Route("/app/register", name="app_page_register", methods={"GET","HEAD"})
     */
    public function register(Request $request): Response
    {
        try {
            return new Response($this->getHtmlFromTemplate('auth/register.html'));
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
    
    /**
     * @Route("/app/login", name="app_page_login", methods={"GET","HEAD"})
     */
    public function login(Request $request): Response
    {
        try {
            return new Response($this->getHtmlFromTemplate('auth/login.html'));
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/app/home", name="app_page_home", methods={"GET","HEAD"})
     */
    public function home(Request $request): Response
    {
        try {
            if(!$this->isLoggedIn()) return $this->redirectToRoute('app_page_login');
            return new Response($this->getHtmlFromTemplate('home/home.html'));
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

}