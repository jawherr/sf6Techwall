<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Form\PersonneType;
use App\Service\Helpers;
use App\Service\MailerService;
use App\Service\PdfService;
use App\Service\UploaderService;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[
    Route('personne'),
    IsGranted('ROLE_USER')
]
class PersonneController extends AbstractController
{
    public function __construct(private LoggerInterface $logger, private Helpers $helper){
    }

    #[Route('/', name:'personne.list')]
    public function index(ManagerRegistry $doctrine):Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }
    #[Route('/pdf/{id}', name: 'personne.pdf')]
    public function generatePdfPersonne($id, PdfService $pdfService, ManagerRegistry $doctrine){
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        $html = $this->render('personne/detail.html.twig', ['personne'=>$personne]);
        $pdfService->showPdfFile($html);
        exit;
    }
    #[Route('/alls/age/{ageMin}/{ageMax}', name:'personne.list.age')]
    public function personnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax):Response {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findPersonneByAgeIterval($ageMin, $ageMax);
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }
    #[Route('/stats/age/{ageMin}/{ageMax}', name:'personne.list.age')]
    public function statsPersonnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax):Response {
        $repository = $doctrine->getRepository(Personne::class);
        $stats = $repository->statsPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/stats.html.twig', [
            'stats' => $stats[0],
            'ageMin'=>$ageMin,
            'ageMax'=>$ageMax
        ]);
    }
    #[Route('/alls/{page?1}/{nbre?12}', name: 'personne.list.alls')]
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
    #[Route('/edit/{id?0}', name: 'personne.edit')]
    public function addPersonne($id,ManagerRegistry $doctrine,
                                Request $request,
                                UploaderService $uploaderService,
                                MailerService $mailerService
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $repository = $doctrine->getRepository(Personne::class);
        $personne = $repository->find($id);
        $new=false;
        //$this->getDoctrine() : Version Sf <= 5
        if(!$personne){
            $new = true;
            $personne = new Personne();
        }
        // $personne est l'image de notre formulaire
        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');
        // Mon formulaire va aller traiter le requete
        $form->handleRequest($request);
        //Est ce que le formulaire a été soumis
        if($form->isSubmitted() && $form->isValid()){
            //Si oui,
            // on va ajouter l'objet dans la base de données
            $photo = $form->get('photo')->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
                $directory = $this->getParameter('personne_directory');
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }
            //Afficher un message de succés
            if($new){
                $message = "a été ajouté avec succés";
                $personne->setCreatedBy($this->getUser());
            } else {
                $message = "a été mis a jour avec succés";
            }
            $manager = $doctrine->getManager();
            $manager->persist($personne);
            $manager->flush();
            //Mail not working google change the protocol of signIn
            //$mailMessage = $personne->getFirstname().' '.$personne->getName().' '.$message;
            $this->addFlash('success',$personne->getName().$message);
            //$mailerService->sendEmail(content: $mailMessage);
            // Rederiger verts la liste des personne
            return $this->redirectToRoute('personne.list');
        } else {
            //Sinon
            //On affiche notre formulaire
            return $this->render('personne/add-personne.html.twig', [
                'form' => $form->createView()
            ]);
        }
    }

    #[
        Route('/delete/{id}', name: 'personne.delete'),
        IsGranted('ROLE_ADMIN')
    ]
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
