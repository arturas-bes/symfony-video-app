<?php


namespace App\Controller\Admin;


use App\Entity\User;
use App\Entity\Video;
use App\Form\UserType;
use App\Utils\CategoryTreeOptionList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/admin")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="admin_main_page")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param TranslatorInterface $translator
     * @return RedirectResponse|Response
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder, TranslatorInterface $translator)
    {
        $user = $this->getUser();
        // second argument fills up form with current data
        // third argument helps build api key field for admin users
        $form = $this->createForm(UserType::class, $user, ['user'=>$user]);
        $form->handleRequest($request);
        $is_invalid = null;

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $encoder->encodePassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $em->persist($user);
            $em->flush();
          //  $translated = $translator->trans('Your changes were saved!');
           $this->addFlash(
               'success',
               'Your changes were saved!'
           );

           return $this->redirectToRoute('admin_main_page');
        } elseif ($request->isMethod('POST')) {
            $is_invalid = 'is_invalid';
        }
            return $this->render('admin/my_profile.html.twig',[
            'subscription' => $this->getUser()->getSubscription(),
                'form' => $form->createView(),
                'is_invalid' => $is_invalid,
        ]);
    }

    /**
     * @Route({"en":"/videos","lt":"/vaizdo-irašai"}, name="videos")
     * @param CategoryTreeOptionList $categories
     * @return Response
     */
    public function videos(CategoryTreeOptionList $categories)
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $categories->getCategoryList($categories->buildTree());
            $videos = $this->getDoctrine()->getRepository(Video::class)->findBy([],['title' => 'ASC']);
        } else {
            $categories = null;
            $videos = $this->getUser()->getLikedVideos();
        }
        return $this->render('admin/videos.html.twig', [
            'videos' => $videos,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/cancel-plan", name="cancel_plan")
     */
    public function cancelPlan()
    {
        $user = $this->getUser();
        $subscribition = $user->getSubscription();
        $subscribition->setValidTo(new \DateTime());
        $subscribition->setPaymentStatus(null);
        $subscribition->setPlan('canceled');
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($subscribition);
        $em->flush();

        return $this->redirectToRoute('admin_main_page');
    }

    /**
     * @Route("/delete-account", name="delete_account")
     */
    public function deleteAccount()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($this->getUser());
        $em->remove($user);
        $em->flush();

        session_destroy();

        return $this->redirectToRoute('main_page');
    }
}
