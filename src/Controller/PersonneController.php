<?php

namespace App\Controller;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('personne')]
class PersonneController extends AbstractController
{
    #[Route('/', name:'personne.list')]
    public function index(ManagerRegistry $doctrine):Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }
    #[Route('/alls/{page?1}/{nbre?12}', name:'personne.list.alls')]
    public function indexAlls(ManagerRegistry $doctrine, $page, $nbre):Response {
        $repository = $doctrine->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        // 24
        $nbrePage = ceil($nbPersonne / $nbre);
        $personnes = $repository->findBy([], [], $nbre, ($page-1)*$nbre);

        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes,
            'isPaginated' => true,
            'nbrePage' => $nbrePage,
            'page' => $page,
            'nbre' => $nbre
        ]);
    }
    #[Route('/{id<\d+>}', name:'personne.detail')]
    public function detail(ManagerRegistry $doctrine, $id):Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        if(!$personne) {
            $this->addFlash('error', "La personne d'id $id n'existe pas ");
            return $this->redirectToRoute('personne.list');
        }
        return $this->render('personne/detail.html.twig', ['personne' => $personne]);
    }
    #[Route('/add', name: 'personne.add')]
    public function addPersonne(ManagerRegistry $doctrine): Response
    {
        //$this->getDoctrine() : Version Sf <= 5
        $entityManager = $doctrine->getManager();
        $personne = new Personne();
        $personne->setFirstname('Jawher');
        $personne->setName('Kallel');
        $personne->setAge('27');
        //Ajouter l'operation d'insertion de la personne dans ma transaction
        $entityManager->persist($personne);
        //Exécute la transaction
        $entityManager->flush();
        return $this->render('personne/detail.html.twig', [
            'personne' => $personne,
        ]);
    }

    #[Route('/delete/{id}', name: 'personne.delete')]
    public function deletePersonne(ManagerRegistry $doctrine, $id): RedirectResponse {
        // Récupérer la personne
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        if ($personne) {
            // Si la personne existe => le supprimer et retourner un flashMessage de succés
            $manager = $doctrine->getManager();
            // Ajouter la fonction de suppression dans la transaction
            $manager->remove($personne);
            // Exécuter la transaction
            $manager->flush();
            $this->addFlash('success', "La personne a été supprimé avec succés");
        } else {
            // Sinon retourner un flashMessage d'erreur
            $this->addFlash('error', "Personne innexistante");
        }
        return $this->redirectToRoute('personne.list.alls');
    }

    #[Route('/update/{id}/{name}/{firstname}/{age}', name: 'personne.update')]
    public function updatePersonnne(ManagerRegistry $doctrine, $id, $name, $firstname, $age) {
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        //Vérifier que la personne a mettre a jour existe
        if($personne){
            $personne->setName($name);
            $personne->setFirstname($firstname);
            $personne->setAge($age);
            $manager = $doctrine->getManager();
            $manager->persist($personne);
            $manager->flush();
            $this->addFlash('success', "La personne a été mise a jour avec succés");
        } else {
            $this->addFlash('error', "Personne innexistante");
        }
        return $this->redirectToRoute('personne.list.alls');
    }
}
