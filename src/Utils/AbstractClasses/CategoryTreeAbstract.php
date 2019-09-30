<?php

namespace App\Utils\AbstractClasses;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class CategoryTreeAbstract {

    /**
     * @var array
     * Holds all categories
     */
    public $categoriesArrayFromDb;

    /**
     * @var
     * Holds html strings
     */
    public $categoryList;
    /**
     * @var
     * Makes sure that connection made only if needed
     */
    protected static $dbconnection;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UrlGeneratorInterface
     */
    public $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->categoriesArrayFromDb = $this->getCategories();
    }

    /**
     * @param array $categories_array
     * @return mixed
     */
    abstract public function getCategoryList(array $categories_array);


    /**
     * @param int|null $parent_id
     * Recursive function
     * @return array
     */
    public function buildTree(int $parent_id = null)
    {
        $subcategory = [];
        foreach ($this->categoriesArrayFromDb as $category) {

//            if the parent id from category equals to the parent id from the argument means category have a childs
            if ($category['parent_id'] == $parent_id) {
//                repeat same method recursively to find childs from another category
                $children = $this->buildTree($category['id']);

//                if tere is something in children
                if ($children) {
//                    add an element to my array
                    $category['children'] = $children;
                }
//                add elements to new array
                $subcategory[] = $category;
            }
        }
        return $subcategory;
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getCategories(): array
    {
        if (!self::$dbconnection) {
            $conn = $this->entityManager->getConnection();

            $sql = "SELECT * FROM `categories`";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            return self::$dbconnection = $stmt->fetchAll();
        }
        return self::$dbconnection;
    }

}