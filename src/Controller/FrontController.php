<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Utils\Interfaces\CacheInterface;

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
     * @param CacheInterface $cache
     * @return Response
     */
    public function videoList(
        $id,
        $page,
        CategoryTreeFrontPage $categories,
        Request $request,
        CacheInterface $cache
    )
    {
        $cache = $cache->cache;

        $video_list = $cache->getItem('video_list'.$id.$page.$request->get('sortby'));

        $video_list->expiresAfter(60);

        // if our list expired then we take data fron Db

        if (!$video_list->isHit()) {
            $ids = $categories->getChildIds($id);
            array_push($ids, $id);

            $videos = $this->getDoctrine()
                ->getRepository(Video::class)
                ->findByChildIds($ids, $page, $request->get('sortby'));

            $categories->getCategoryListAndParent($id);

            $response = $this->render('front/video_list.html.twig', [
                'subcategories' => $categories,
                'videos' => $videos,
            ]);

            $video_list->set($response);
            $cache->save($video_list);
        }

        return $video_list->get();
    }

    /**
     * @Route("/video-details/{video}", name="video_details")
     * @param VideoRepository $repository
     * @param $video
     * @return Response
     */
    public function videoDetails(VideoRepository $repository, $video)
    {

        return $this->render('front/video_details.html.twig',[
            'video' => $repository->findOneBy(['id' => $video]),

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
