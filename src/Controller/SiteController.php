<?php

namespace App\Controller;

use App\Form\SiteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DiDom\Document;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class SiteController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SiteType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            return $this->redirectToRoute('searchImages', $form->getData());
        }

        return $this->render('default/index.html.twig', [
            'controller_name' => 'SiteController',
            'form' => $form->createView()
        ]);
    }

    #[Route('/searchImages', name: 'searchImages')]
    public function searchImagesAction(Request $request): Response
    {
        $url = $request->query->get('site');

        $document = new Document($url, true);

        $sum_size = 0;
        $imgs = $document->find('img');    
        foreach($imgs as $key=>$img){
            if((str_starts_with($img->src, "https://")) or (str_starts_with($img->src, "http://"))){
                $headers = get_headers($img->src);
    
                if($headers != false){
                    foreach($headers as $header){
                        if(str_contains($header, 'Content-Length')){
                            $sum_size += (int)explode('Content-Length: ', $header)[1];
                        }
                    }
                }
            } else {
                unset($imgs[$key]);
            }
        }

        $imgs = array_chunk(array_values($imgs), 4);

        $sum_size = (float)($sum_size / (1024 * 1024));

        return $this->render('default/searchImages.html.twig', [
            'controller_name' => 'SiteController',
            'size' => $sum_size,
            'imgs_all' => $imgs
        ]);
    }
}
