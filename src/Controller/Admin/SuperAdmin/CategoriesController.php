<?php


namespace App\Controller\Admin\SuperAdmin;


use App\Entity\Category;
use App\Form\CategoryType;
use App\Utils\CategoryTreeAdminList;
use App\Utils\CategoryTreeOptionList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/su")
 */
class CategoriesController extends AbstractController
{

    /**
     * @param CategoryTreeOptionList $categories
     * @param null $editedCategory
     * @return Response
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

    /**
     * @Route("/categories", name="categories", methods={"GET", "POST"})
     * @param CategoryTreeAdminList $categories
     * @param Request $request
     * @return Response
     */
    public function categories(CategoryTreeAdminList $categories, Request $request)
    {
        // we dont use an argument because default category is null
        $categories->getCategoryList($categories->buildTree());
        //build category form
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;

        if ($this->saveCategory($category, $form, $request)) {

            return $this->redirectToRoute('categories');

        } elseif ($request->isMethod('post')) {
            $is_invalid = ' is_invalid';
        }

        return $this->render('admin/categories.html.twig',[
            'categories' => $categories->categoryList,
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }

    /**
     * @Route("/edit-category/{id}", name="edit_category", methods={"GET", "POST"})
     * @param Category $category
     * @param Request $request
     * @return Response
     */
    public function editCategory(Category $category, Request $request)
    {
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;

        if ($this->saveCategory($category, $form, $request)) {

            return $this->redirectToRoute('categories');

        } elseif ($request->isMethod('post')) {
            $is_invalid = ' is_invalid';
        }

        return $this->render('admin/edit_category.html.twig',[
            'category' => $category,
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }

    /**
     * @Route("/delete-category/{id}", name="delete_category")
     * We dont use direct id as an argument because Symfony direct param
     * converter is used it takes object and returns its id
     * @param Category $category
     * @return Response
     */
    public function deleteCategory(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('categories');
    }

    /**
     * @param $category
     * @param $form
     * @param $request
     * @return bool
     */
    private function saveCategory($category, $form, $request)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $category->setName($request->request->get('category')['name']);

            $repo = $this->getDoctrine()->getRepository(Category::class);
            $parent = $repo->find($request->request->get('category')['parent']);

            $category->setParent($parent);
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            return true;
        }
        return false;
    }
}