<?php

namespace App\Controller;

use DateInterval;
use App\Entity\Images;
use App\Entity\CategoryService;
use App\Form\CategoryServiceType;
use App\services\CategoryServices;
use App\Repository\DevisRepository;
use App\Repository\ArticleRepository;
use App\Repository\ServiceRepository;
use App\services\ImageManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\CategoryServiceRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryServiceController extends AbstractController
{
    /**
     * @Route("/admin/category_services", name="category_service_list")
     */
    public function index(CategoryServiceRepository $rep): Response
    {
        $catgs = $rep->findAll();

        return $this->render('backoffice/category/category_services_list.html.twig', [
            'catgs' => $catgs,
        ]);
    }

    /**
     * @param CategoryServiceRepository $rep
     * @return Response
     * @Route("/categories-services", name="categorie_service_all")
     */
    public function allCategoriesService(
        CategoryServices $categoryService, 
        Request $req, 
        CacheInterface $cache,
        SeoPageInterface $seoPage
        ):Response{

         $categoriesService = $cache->get("categorie-service-page", function(ItemInterface $item) use ($categoryService, $req){
             $item->expiresAfter(DateInterval::createFromDateString('3 hour'));   
            return $categoryService->getAllCategoryService($req);
        });

        //seo
        $description = "Nous sommes une agence de marketing et de communication experte dans les principaux 
                        canaux de communication digitale et de la gestion de la relation client De la cr??ation de votre site internet en 
                        passant par la mise en passant d???une strat??gie digitale pour augmenter votre visibilit??, 
                        ventes et notori??t?? sur le web, nous vous accompagnons dans le d??veloppement de votre business. ";
        $seoPage
                ->setTitle("Services | Logone")
                ->addMeta('name', 'description', $description)
                ->addMeta('property', 'og:title', "A propos | Logone")
                ->addMeta('property', 'og:type', 'services')
                ->addMeta('property', 'og:description', $description) 
            ;
        return $this->render('frontoffice/category_services.html.twig', [
            'catgs' => $categoriesService,
        ]);
    }

    /**
     * @param $id
     * @param CategoryServiceRepository $rep
     * @param EntityManagerInterface $em
     * @return Response
     * @Route("/admin/delete_category_service/{slug}", name="category_service_delete")
     */
    public function deleteCategoryService(
        CategoryService $catg,
        EntityManagerInterface $em,
        FlashyNotifier $flashy,
        CacheInterface $cache,
        ServiceRepository $serviceRepo,
        DevisRepository $devisRepo,
        ArticleRepository $articleRepo
        ):Response
    {
        try {
            if(count($serviceRepo->findByCategoryService($catg->getId())) == 0 ||
             count($devisRepo->findAllDevisByCategoryService($catg->getId())) ||
             count($articleRepo->findAllByCategoryService($catg->getId())) 
             ){

            $em->remove($catg);
            $em->flush();
            $cache->delete("categorie-service");
            $cache->delete("categorie-service-page");
            $flashy->success("Deleted successfully","");
            return $this->redirectToRoute('category_service_list');
        }
        } catch (\Exception $e) {
            $flashy->error("impossible de supprimez ce cat??gorie car elle est ratach??e ?? un devis , un article ou ?? un service","");
            return $this->redirectToRoute('category_service_list');
        }
        // $flashy->error("impossible de supprimez ce cat??gorie car elle est ratach??e ?? un devis ou ?? un service","");
        // return $this->redirectToRoute('category_service_list');
        
    }

    /**
     * @param EntityManagerInterface $em
     * @param Request $req
     * @return Response
     * @Route("/admin/add_category_service", name="category_service_add", methods={"GET","POST"})
     */
    public function addCategoryService(
        EntityManagerInterface $em, 
        Request $req,
        FlashyNotifier $flashy,
        CacheInterface $cache
    ):Response{
        $catg = new CategoryService();
        $form = $this->createForm(CategoryServiceType::class, $catg);
        $form->add('imageFile', VichImageType::class,[
                'label'=>false,
                 'required'=>false,
                 'allow_delete'=>true,
                 'download_uri' => false,
                 'image_uri' => true,
                 "constraints"=>[
                     new NotNull(),
                     new Image([
                         "maxSize" => "1024k"
                     ])
                 ]
                 ]);
        $form->handleRequest($req);
        if($form->isSubmitted() and $form->isValid()){
            $em->persist($catg);
            $em->flush();
            $cache->delete("categorie-service");
            $cache->delete("categorie-service-page");
            $flashy->success("Added successfully !","");
            return $this->redirectToRoute('category_service_list');
        }
        return $this->render('backoffice/category/add_category.html.twig',[
            'form'=>$form->createView(),
        ]);
    }

    /**
     * @param $idCatg
     * @param EntityManagerInterface $em
     * @param CategoryServiceRepository $rep
     * @param Request $req
     * @return Response
     * @Route("/admin/edit_category_service/{slug}", name="category_service_edit")
     */
    public function modifierCategoryService(
        CategoryService $catg, 
        EntityManagerInterface $em, 
        Request $req,
        FlashyNotifier $flashy,
        CacheInterface $cache
    ):Response
    {
        $form = $this->createForm(CategoryServiceType::class, $catg);
        $form->add('imageFile', VichImageType::class,[
                'label'=>false,
                 'required'=>false,
                 'allow_delete'=>true,
                 'download_uri' => false,
                 'image_uri' => true,
        ]);
        $form->handleRequest($req);
        if($form->isSubmitted() and $form->isValid()){
            $em->flush();
            $flashy->success("Edited successfully !","");
            $cache->delete("categorie-service");
            $cache->delete("categorie-service-page");
            return $this->redirectToRoute('category_service_list');
        }
        return $this->render('backoffice/category/modify_category_service.html.twig',[
            'form'=>$form->createView(),
            'categories'=>$catg
        ]);
    }

}
