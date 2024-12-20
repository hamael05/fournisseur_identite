<?
// src/Controller/ApiDocumentationController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ApiDocumentationController extends AbstractController
{

    #[Route('/swagger', name: 'swagger_index')]

    public function index()
    {
        return $this->render('swagger/index.html.twig');
    }
}
