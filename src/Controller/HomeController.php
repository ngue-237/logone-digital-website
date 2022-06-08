<?php

namespace App\Controller;
use DateInterval;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\CategoryArticleRepository;
use App\Repository\CategoryServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{
    
    /**
     * @Route("/", name="home")
     */
    public function index(
        Request $req, 
        PaginatorInterface $paginator,
        CategoryServiceRepository $catgServiceRepo,
        CategoryArticleRepository $categoryArtRepo,
        CacheInterface $cache,
        SeoPageInterface $seoPage
    ): Response
    {
        
        //$cache->delete("categorie-service");
        $categoriesService = $cache->get("categorie-service", function(ItemInterface $item) use ($catgServiceRepo,$paginator, $req){
             $item->expiresAfter(DateInterval::createFromDateString('3 hour'));   
             
            return $paginator->paginate($catgServiceRepo->findAllByDate(), $req->query->getInt('page', 1), 4);
        });

        $catgoriesArticle = $cache->get("categorie-article-home", function(ItemInterface $item) use($paginator, $req,$categoryArtRepo){
             $item->expiresAfter(DateInterval::createFromDateString('3 hour'));
            return $paginator->paginate($categoryArtRepo->findAllByDate(), $req->query->getInt('page', 1), 3);
        });

        //seo


        $description = "LOGONE est une agence de communication digital qui vous accompagne dans votre stratégie digitale, e-réputation et l'augmentation de votre chiffre d'affaires. 
                        Notre expertise s'étend aux leviers majeurs liés au développement de votre visibilité digitale et à la gestion de la relation client, à savoir: la création de votre site internet, 
                        l'optimisation de votre visibilité Google à travers le référencement naturel (SEO) et référencement payant (Google Ads et Google Shopping), 
                        l'amélioration de votre présence sur les réseaux sociaux, et une assistance virtuelle de vos client.";
        $seoPage
                ->setTitle("Accueil | Logone")
                ->addMeta('name', 'description', $description)
                ->addMeta('property', 'og:title', "Accueil | Logone")
                ->addMeta('property', 'og:type', 'home')
                ->addMeta('property', 'og:description', $description)
               
            ;

        return $this->render('frontoffice/index.html.twig', [
           'categoriesService'=>$categoriesService,
           "catgoriesArticle" => $catgoriesArticle,
        ]);
    }

    /**
     * Undocumented function
     *@Route("/mentions-legales", name="app_mention_legale")
     * @return Response
     */
    public function mentionLegale():Response{
        return $this->render("frontoffice/mentions_legales.html.twig");
    }

    /**
     * @Route("/entreprise", name="about")
     */
    public function about(SeoPageInterface $seoPage){
        //seo
        $description = "Jeune, dynamique et animée par une grande ambition, LOGONE est une équipe de 
                        personnes d'origine et de cultures différentes. Notre savoir réside dans notre capacité à 
                        allier talents, nouvelles technologies et efficacité afin d'offrir 
                        des services et des compétences adaptées aux besoins nos clients et partenaires.";
        $seoPage
                ->setTitle("A propos | Logone")
                ->addMeta('name', 'description', $description)
                ->addMeta('property', 'og:title', "A propos | Logone")
                ->addMeta('property', 'og:type', 'about')
                ->addMeta('property', 'og:description', $description)
               
            ;
        return $this->render('frontoffice/about.html.twig');
    }

    /**
     * Undocumented function
     *
     * @return Response
     * @Route("/prendre-rendez-vous", name="app_rdv", methods={"GET"})
     */
    public function rdv():Response{
        return $this->render("frontoffice/rdv.html.twig");
    }

    /**
     * @Route("/portfolio", name="portforlio")
     */
    public function portefolio(){
        return $this->render('frontoffice/portfolio.html.twig', [
            
        ]);
    }

    /**
     * @Route("/portfolio_detail", name="portforlio_details")
     */
    public function portefolioDetail(){
        return $this->render('frontoffice/portfolio-details.html.twig', [
            
        ]);
    }

}
