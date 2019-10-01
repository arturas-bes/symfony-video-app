<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Video;
use App\Form\UserType;
use App\Utils\CategoryTreeFrontPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="main_page")
     */
    public function index()
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @Route("/video-list/category/{categoryName}/{id}/{page}",
     *     defaults={"page": "1"},
     *     name="video_list")
     * @param $id
     * @param $page
     * @param CategoryTreeFrontPage $categories
     * @param Request $request
     * @return Response
     */
    public function videoList($id, $page, CategoryTreeFrontPage $categories, Request $request)
    {
        $categories->getCategoryListAndParent($id);
        $ids = $categories->getChildIds($id);
        //using array push we add to an array the actual "parent" category
        array_push($ids, $id);

        $videos = $this->getDoctrine()
            ->getRepository(Video::class)
//            ->findAll();
        ->findByChildIds($ids, $page, $request->get('sortby'));

        return $this->render('front/video_list.html.twig', [
            'subcategories' => $categories,
            'videos' => $videos,
        ]);
    }

    /**
     * @Route("/video-details", name="video_details")
     */
    public function videoDetails()
    {

        return $this->render('front/video_details.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * without default value paginator will return error
     * @Route("/search-results/{page}", methods={"GET"},
     *      defaults={"page": "1"},
     *      name="search_results")
     * @param $page
     * @param Request $request
     * @return Response
     */
    public function searchResults($page, Request $request)
    {
        $videos = null;
        $query = null;

        if ($query = $request->get('query')) {
            $videos = $this->getDoctrine()
                ->getRepository(Video::class)
                ->findByTitle($query, $page, $request->get('sortby'));
            if (!$videos->getItems())
                $videos = null;
        }

        return $this->render('front/search_results.html.twig', [
            'videos' => $videos,
            'query' => $query
        ]);
    }
    /**
     * @Route("/pricing", name="pricing")
     */
    public function pricing()
    {
        return $this->render('front/pricing.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $encoder->encodePassword($user,
                $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $em->persist($user);
            $em->flush();

//            Handle set user token and redirect to admin panel
            $this->loginUserAutomatically($user, $password);
            return $this->redirectToRoute('admin_main_page');
        }

        return $this->render('front/register.html.twig', [
           'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/login", name="login")
     * Parameter stores errors when user provides incorrect data to login form
     * @param AuthenticationUtils $helper
     * @return Response
     */
    public function login(AuthenticationUtils $helper)
    {

        return $this->render('front/login.html.twig', [
           'error' => $helper->getLastAuthenticationError()
        ]);
    }

    private function loginUserAutomatically($user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main',
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
//        if token exists in the session it means that user is logged in in to the application
        $this->get('session')->set('_security_main', serialize($token));
    }

    /**
     * @throws \Exception
     * @Route("/logout", name="logout")
     */
    public function logout():void
    {
        throw new \Exception('This should never be reached!');
    }

    /**
     * @Route("/payment", name="payment")
     */
    public function payment()
    {
        return $this->render('front/payment.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    public function mainCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)
            ->findBy(['parent' => null], ['name'=>'ASC']);
        return $this->render('front/helper/_main_categories.html.twig',[
            'categories' => $categories
        ]);
    }
}
