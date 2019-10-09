<?php

namespace App\Controller;

use App\Controller\Traits\Likes;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    use Likes; // methods for like functionality were moved to this trait
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
     * @Route("/video-details/{video}", name="video_details")
     * @param VideoRepository $repository
     * @param $video
     * @return Response
     */
    public function videoDetails(VideoRepository $repository, $video)
    {

        return $this->render('front/video_details.html.twig',[
            'video' => $repository->videoDetails($video),
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
     * @Route("/payment", name="payment")
     */
    public function payment()
    {
        return $this->render('front/payment.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @return Response
     * @Route("/new-comment/{video}", name="new-comment")
     */
    public function newComment(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!empty(trim($request->request->get('comment')))) {
            $comment = new Comment();
            $comment->setContent($request->request->get('comment'));
            $comment->setUser($this->getUser());
            $comment->setVideo($video);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectToRoute('video_details',[
            'video' =>$video->getId()
        ]);
    }

    /**
     * @param Video $video
     * @param Request $request
     * @return Response
     * @Route("/video-list/{video}/like", name="like-video", methods={"POST"})
     * @Route("/video-list/{video}/dislike", name="dislike-video", methods={"POST"})
     * @Route("/video-list/{video}/unlike", name="undo-like-video", methods={"POST"})
     * @Route("/video-list/{video}/undodislike", name="undo-dislike-video", methods={"POST"})
     */
    public function toggleLikesAjax(Video $video, Request $request)
    {   $result = null;
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        switch ($request->get('_route'))
        {
            case 'like-video':
                $result = $this->likedVideo($video);
                break;
            case 'dislike-video':
                $result = $this->dislikeVideo($video);
                break;
            case 'undo-like-video':
                $result = $this->undolikeVideo($video);
                break;
            case 'undo-dislike-video':
                $result = $this->undoDislikeVideo($video);
                break;
        }
        return $this->json(['action' => $result, 'id' => $video->getId()]);
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
