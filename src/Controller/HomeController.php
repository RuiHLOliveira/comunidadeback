<?php

namespace App\Controller;

use DateTime;
use DateTimeZone;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class HomeController extends AbstractController
{

    // /**
    //  * @Route("/", name="home")
    //  */
    // public function export(Request $request, ManagerRegistry $doctrine): JsonResponse
    // {
    //     try {
    //         return new JsonResponse(['hello world'], 200);
    //     } catch (\Exception $e) {
    //         return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
    //     }
    // }
}
