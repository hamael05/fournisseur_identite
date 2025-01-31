<?php

namespace App\Controller;

use App\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController
{
    #[Route('/student', name: 'app_student')]
    public function index(StudentRepository $repository): Response
    {
        try {
            // Récupérer tous les étudiants
            $students = $repository->findAll();

            // Structurer la réponse
            $response = [
                'status' => 'success',
                'erreur' => null,
                'data' => $students,
            ];
        } catch (\Exception $e) {
            // En cas d'erreur
            $response = [
                'status' => 'error',
                'erreur' => $e->getMessage(),
                'data' => null,
            ];
        }

        return $this->json($response);
    }
}
    