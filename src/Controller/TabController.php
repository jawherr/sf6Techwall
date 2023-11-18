<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TabController extends AbstractController
{
    #[Route('/tab', name: 'app_tab')]
    public function index(): Response
    {
        return $this->render('tab/index.html.twig', [
            'controller_name' => 'TabController',
        ]);
    }
}
