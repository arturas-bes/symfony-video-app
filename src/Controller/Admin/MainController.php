<?php


namespace App\Controller\Admin;


use App\Entity\Video;
use App\Utils\CategoryTreeOptionList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class MainController extends AbstractController
{
    /**
     * @Route("/", name="admin_main_page")
     */
    public function index()
    {
        return $this->render('admin/my_profile.html.twig');
    }

    /**
     * @Route("/videos", name="videos")
     */
    public function videos()
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            $videos = $this->getDoctrine()->getRepository(Video::class)->findAll();
        } else {
            $videos = $this->getUser()->getLikedVideos();
        }
        return $this->render('admin/videos.html.twig', [
            'videos' => $videos
        ]);
    }

    /**
     * @param CategoryTreeOptionList $categories
     * @param null $editedCategory
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllCategories(CategoryTreeOptionList $categories, $editedCategory = null)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $categories->getCategoryList($categories->buildTree());

        return $this->render('admin/helper/_all_categories_option_list.html.twig', [
            'categories' => $categories,
            'editedCategory' => $editedCategory

        ]);
    }
}