<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\services\CurlService;
use App\services\CaptchaService;
use App\services\MaillerService;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ContactController extends AbstractController
{
    /**
     * Permet d'envoyer une demande contacte
     * @Route("/audit-gratuit", name="contact")
     */
    
    public function newContact(
        Request $req, 
        EntityManagerInterface $em,
        MaillerService $mailer,
        FlashyNotifier $flashy,
        CaptchaService $helper,
        CurlService $client,
        SeoPageInterface $seoPage
        ): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->add("captcha", HiddenType::class, [
            "constraints"=>[
                new NotNull(),
                new NotBlank()
            ]
        ]);
        
        $form->handleRequest($req);

        if($form->isSubmitted() and $form->isValid()){
            
            $url = "https://www.google.com/recaptcha/api/siteverify?secret=6Lc96AYfAAAAAEP84ADjdx5CBfEpgbTyYqgemO5n&response={$_POST['contact']["captcha"]}";

            $response = $client->curlManager($url);
            
            if(empty($response) || is_null($response)){
                
                $flashy->warning("Something wrong!",'');
                return $this->redirectToRoute('contact');
            }
            else{
                
                $data = json_decode($response);

                if($data->success){  
                    $em->persist($contact);
                    $em->flush();
                    $flashy->success("Votre demande a ??t?? bien prise en compte vous serez recontactez dans les prochaines 24h!",'');
                    return $this->redirectToRoute('contact');
                }else{
                    
                    $flashy->error("Confirm you are not robot!",'');
                    return $this->redirectToRoute('contact');
                }

            }     
            
        }
        $description = "Envoyez-nous un message pour ??tablir ensemble votre strat??gie digitale et obtenir un AUDIT GRATUIT, de votre site.";
        $seoPage
                ->setTitle("Audit-gratuit | Logone")
                ->addMeta('name', 'description', $description)
                ->addMeta('property', 'og:title', "Audit-gratuit | Logone")
                ->addMeta('property', 'og:type', 'blog')
                ->addMeta('property', 'og:description', $description)
               
            ;
        return $this->renderForm('frontoffice/contact.html.twig', compact('form'));
    }

    /**
     * Permet d'afficher la liste des contact
     * @param ContactRepository $rep
     * @Route("/admin/contact_list", name="contact_list")
     */
    public function contactList(ContactRepository $rep):Response{
       
        return $this->render('backoffice/contact/contactList.html.twig', [
            'contacts'=>$rep->findBy([], ["createdAt" => "ASC"])
        ]);
    }

    /**
     * Permet de supprimer un contact
     * @param $id
     * @param ContactRepository $rep
     * @param EntityManagerInterface $em
     * @return Response
     * @Route("/admin/contact_delete/{id}", name="contact_delete")
     */
    public function deleteContact(
        Contact $conctact , 
        EntityManagerInterface $em,
        FlashyNotifier $flashy
        ):Response{
        $em->remove($conctact);
        $em->flush();
        $flashy->success("Delete successfully","");
        return $this->redirectToRoute('contact_list');
    }

}
