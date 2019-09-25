<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/video-list", name="video_list")
     */
    public function videoList()
    {
        return $this->render('front/video_list.html.twig', [
            'controller_name' => 'FrontController',
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
     * @Route("/search-results", methods={"POST"}, name="search_results")
     */
    public function searchResults()
    {
        return $this->render('front/search_results.html.twig', [
            'controller_name' => 'FrontController',
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
     */
    public function register()
    {
        return $this->render('front/register.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('front/login.html.twig', [
            'controller_name' => 'FrontController',
        ]);
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

}
