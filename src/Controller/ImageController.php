<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/image')]
class ImageController extends AbstractController
{
    #[Route('/{id}', name: 'app_image')]
    public function index(Product $product): Response
    {
        $image = new Image();
        $formImage=$this->createForm(ImageType::class, $image);

        return $this->render('image/index.html.twig', [
            'product' => $product,
            'formImage'=>$formImage
        ]);
    }

    #[Route('/admin/addproduct/{id}', name: 'add_image')]
    public function addImage(Product $product, Request $request, EntityManagerInterface $manager): Response
    {
        $routeName = $request->attributes->get("_route");

        $image = new Image();
        $formImage=$this->createForm(ImageType::class, $image);
        $formImage->handleRequest($request);
        if($formImage->isSubmitted() && $formImage->isValid()){

            $image->setProduct($product);


            $manager->persist($image);
            $manager->flush();
        }

        if($routeName == "add_image_profile"){
            return $this->redirectToRoute('app_profile');
        }

        return $this->redirectToRoute('app_image', [
            'id' => $product->getId()
        ]);
    }


    #[Route('/remove/{id}', name: 'remove_image')]
    public function remove(Image $image, EntityManagerInterface $manager): Response
    {

        $product=$image->getProduct();

        $manager->remove($image);
        $manager->flush();


        return $this->redirectToRoute('app_image', [
            'id' => $product->getId()
        ]);
    }
}