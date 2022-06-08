<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryArticleRepository;
use App\Repository\CategoryServiceRepository;
use App\Repository\CommentsRepository;
use App\Repository\OffreEmploiRepository;
use App\Repository\ServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="app_sitemap", defaults={"_format"="xml"})
     */
    public function index(
        Request $req,
        CategoryServiceRepository $categoryServiceRepo,
        ServiceRepository $serviceRepo,
        OffreEmploiRepository $offreEmploiRepo,
        CategoryArticleRepository $catgArticleRepo,
        ArticleRepository $articleRepo,
        CommentsRepository $commentsRepo
        ): Response
    {
        $hostname =  $req->getSchemeAndHttpHost();

        //initialisation d'un tableau pour la liste d'url
        $urls = [];

        //Ajout des urls statisques
        $urls []= ['loc'=> $this->generateUrl("home"),"priority"=>0.8,
                "changefreq"=>"monthly",];
        $urls []= ['loc'=> $this->generateUrl("about"),"priority"=>0.8,
                "changefreq"=>"monthly",];
        $urls []= ['loc'=> $this->generateUrl("contact"),"priority"=>0.8,
                "changefreq"=>"monthly",];
        $urls []= ['loc'=> $this->generateUrl("categorie_service_all"),"priority"=>0.8,
                "changefreq"=>"monthly",];
        $urls []= ['loc'=> $this->generateUrl("blog"),"priority"=>0.8,
                "changefreq"=>"monthly",];
        $urls []= ['loc'=> $this->generateUrl("jobslist_front"),"priority"=>0.8,
                "changefreq"=>"monthly",];
        
        //ajout des urls dynamique
         foreach($categoryServiceRepo->findAll() as $catgService){
            $images = [
                "loc" => "/public/uploads/images/categories_service/".$catgService->getImage(),
                "priority"=>0.8,
                "changefreq"=>"monthly",
                "title"=>$catgService->getSlug()
            ];
            $urls []= [
                "loc"=>$this->generateUrl("services", [
                    "slug"=>$catgService->getSlug()
                ]),
                "priority"=>0.8,
                "changefreq"=>"monthly",
                "images" => $images,
            ];
        }   
        foreach($offreEmploiRepo->findAll() as $offreEmploi){
            $urls []= [
                "loc"=>$this->generateUrl("jobslist_front", [
                    "slug"=>$offreEmploi->getSlug()
                ]),
                "priority"=>0.8,
                "changefreq"=>"monthly",
                // "lastmod" =>$service->getUpdatedAt()->format('Y-m-d')
            ];
        }
        foreach($catgArticleRepo->findAll() as $catgArticle){
            $images = [
                "loc" => "/public/uploads/images/categories_article/".$catgArticle->getImage(),
                "title"=>$catgService->getSlug()
            ];
            $urls []= [
                "loc"=>$this->generateUrl("blog", [
                    "slug"=>$catgArticle->getSlug()
                ]),
                "priority"=>0.8,
                "changefreq"=>"monthly",
                "lastmod" =>$catgArticle->getUpdatedAt()->format('Y-m-d')
            ];
        }
        foreach($articleRepo->findAll() as $article){
            $images = [
                "loc" => "/public/uploads/images/categories_article/".$article->getImage(),
                "title"=>$catgService->getSlug()
            ];
            $urls []= [
                "loc"=>$this->generateUrl("blog", [
                    "slug"=>$article->getSlug()
                ]),
                 "priority"=>0.8,
                 "changefreq"=>"monthly",
                "lastmod" =>$article->getCreatedAt()->format('Y-m-d')
            ];
        }
        foreach($commentsRepo->findAll() as $comment){
            
            $urls []= [
                "loc"=>$this->generateUrl("blog", [
                    "slug"=>$comment->getContent(),
                    "priority"=>0.8,
                    "changefreq"=>"monthly",
                ]),
                "lastmod" =>$comment->getCreatedAt()->format('Y-m-d')
            ];
        }
        $response = new Response(
            $this->renderView('sitemap/index.html.twig', compact(
            "hostname",
            "urls"
            )),200
        );

        $response->headers->set("content-type", "text/xml");
        return $response;
    }
}
