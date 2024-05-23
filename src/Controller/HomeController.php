<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $tabItem = $entityManager->getRepository(Produit::class)->findLastFiveItems();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tabItem' => $tabItem 
        ]);
    }

    #[Route('/products', name: 'app_products')]
    public function products(EntityManagerInterface $entityManager): Response
    {
        $tabProduct = $entityManager->getRepository(Produit::class)->findAll();

        return $this->render('products/products.html.twig', [
            'tabProduct' => $tabProduct 
        ]);
    }

    #[Route('/product/{id}', name: 'my_product')]
    public function findProduct(EntityManagerInterface $entityManager, int $id, Request $request): Response
    {

        

        $myProduct = $entityManager->getRepository(Produit::class)->findById($id);
        if (empty ($myProduct)) {
            $myProduct = null;
        } else {

            $myProduct = $myProduct[0];
            $commentaire = new Commentaire();
            $commentaire->setProduit($myProduct);

            $form = $this->createFormBuilder($commentaire)
                ->add('titre', TextType::class, ['required' => true])
                ->add('contenu', TextType::class)
                ->add('submit', submitType::class, ['label' => 'Envoyer mon commentaire'])
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()){
                $commentaire = $form->getData();
                $entityManager->persist($commentaire);
                $entityManager->flush();

                $myProduct->addCommentaire($commentaire);

                return new RedirectResponse($this->generateUrl('my_product', ['id' => $id]));

            }
        }
        return $this->render('myProduct/myProduct.html.twig', [
            'myProduct' => $myProduct ,
            'formulaireCommmentaire' => $form
            ]
        );
    }

    #[Route('/admin/viewProduct', name: 'view_product')]
    public function viewProduct(EntityManagerInterface $entityManager, Request $request): Response
    {
        $myProducts = $entityManager->getRepository(Produit::class)->findAll();
        return $this->render('admin/viewProduct.html.twig', [
            'myProducts' => $myProducts,
        ]);
    }

    #[Route('/admin/addProduct/{id}', name: 'add_product')]
    public function addProduct(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {   
        $message = "";
        if($id == 0) {
            $product = new Produit();
        } else {
            $product = $entityManager->getRepository(Produit::class)->findById($id)[0];
        }

        $form = $this->createFormBuilder($product)
            ->add('nom', TextType::class, ['required' => true]) 
            ->add('description', TextType::class)
            ->add('stock', IntegerType::class)
            ->add('image', TextType::class)
            ->add('submit', SubmitType::class, ['label' => 'Envoyer mon produit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $product = $form->getData();

            $entityManager->persist($product);
            $entityManager->flush();
            $message = 'Le produit a bien été ajouté';
        
        }
        return $this->render('admin/addProduct.html.twig', [
            'formulaireProduit' => $form->createView(),
            'message' => $message
        ]);
    }

    #[Route('/admin/deleteProduct/{id}', name: 'delete_product')]
    public function deleteProduct(EntityManagerInterface $entityManager, Request $request, int $id): Response
    {
        $myProductToDelete = $entityManager->getRepository(Produit::class)->findById($id)[0];
        $entityManager->remove($myProductToDelete);
        $entityManager->flush();

        $myProducts = $entityManager->getRepository(Produit::class)->findAll();
        return $this->redirectToRoute('view_product');
    }

    #[Route('/admin/comments', name: 'admin_comments')]
    public function adminCommentaires(EntityManagerInterface $entityManager): Response
{
    $commentaires = $entityManager->getRepository(Commentaire::class)->findAll();

    return $this->render('admin/viewComment.html.twig', [
        'commentaires' => $commentaires,
    ]);
}


#[Route('/admin/deleteCommentaire/{id}', name: 'delete_commentaire')]
public function deleteCommentaire(EntityManagerInterface $entityManager, Request $request, int $id): Response
{
    $commentaireToDelete = $entityManager->getRepository(Commentaire::class)->find($id);
    if (!$commentaireToDelete) {
        throw $this->createNotFoundException('Commentaire non trouvé');
    }

    $entityManager->remove($commentaireToDelete);
    $entityManager->flush();

    return $this->redirectToRoute('admin_comments');
}


}