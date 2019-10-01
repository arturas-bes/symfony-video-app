<?php


namespace App\Utils;


use App\Twig\AppExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeFrontPage extends CategoryTreeAbstract
{
    /**
     * @var string
     */
    public $html_1 = '<ul>';
    /**
     * @var string
     */
    public $html_2 = '<li>';
    /**
     * @var string
     */
    public $html_3 = '<a href="';
    /**
     * @var string
     */
    public $html_4 = '" >';
    /**
     * @var string
     */
    public $html_5 = '</a>';
    /**
     * @var string
     */
    public $html_6 = '</li>';
    /**
     * @var string
     */
    public $html_7 = '</ul>';
    /**
     * @var AppExtension
     */
    public $slugger;
    /**
     * @var
     */
    public $mainParentName;
    /**
     * @var
     */
    public $mainParentId;
    /**
     * @var
     */
    public $currentCategoryName;


    public function getCategoryListAndParent(int $id): string
    {

        $this->slugger = new AppExtension; // App extension to slugify categories urls

        $parentData = $this->getMainParent($id);  // Main parent of subcategory
        // data for accessing the view in template
        $this->mainParentName = $parentData['name'];
        $this->mainParentId = $parentData['id'];
        
        // get current category name to put into template
        $key = array_search($id, array_column($this->categoriesArrayFromDb,'id'));
        $this->currentCategoryName = $this->categoriesArrayFromDb[$key]['name'];

        // builds array for generating nested html list
        $categories_array = $this->buildTree($parentData['id']);

        return $this->getCategoryList($categories_array);
    }

    /**
     * @param array $categories_array
     * @return mixed|string
     */
    public function getCategoryList(array $categories_array)
    {
        $this->categoryList .= $this->html_1;

        foreach ($categories_array as $value ) {
            // Add html for parents
            $catName = $this->slugger->slugify($value['name']); // slugifies name removes unnecessary symbols
            $url = $this->urlGenerator->generate('video_list',
                ['categoryName'=>$catName,'id'=>$value['id']]);
            $this->categoryList .= $this->html_2 .$this->html_3. $url.$this->html_4. $value['name'] . $this->html_5;
            // check if children is not empty
            if (!empty($value['children'])) {
            //                call method recursively and take children array as an argument to collect childs
                $this->getCategoryList($value['children']);
            }
            $this->categoryList .=$this->html_6;
        }

        $this->categoryList .=$this->html_7;

        return $this->categoryList;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getMainParent(int $id): array
    {
//        array column return array column values 'id' => 1 ...
//         array search gonna return position where there is a required id we are looking for position or array key which has a category  we want to find parent for
        $key = array_search($id, array_column($this->categoriesArrayFromDb, 'id'));
//       check db once again if given key ther eis a given key we recurively call method once again to check for parent
        if ($this->categoriesArrayFromDb[$key]['parent_id'] != null) {
            return $this->getMainParent($this->categoriesArrayFromDb[$key]['parent_id']);
        }
//        otherwise we return id and name we gonna use for main parent
        return [
            'id' => $this->categoriesArrayFromDb[$key]['id'],
            'name' => $this->categoriesArrayFromDb[$key]['name'],
        ];
    }
//    parent
//      child
//          child2
//              child3....

    public function getChildIds(int $parent): array
    {
        static $ids = []; // static needed so after every recursive iteration array wont be empty
        foreach ($this->categoriesArrayFromDb as $val) {

            if ($val['parent_id'] == $parent) {
                $ids[] = $val['id'].',';
                $this->getChildIds($val['id']);
            }
        }
        return $ids;
    }
}

