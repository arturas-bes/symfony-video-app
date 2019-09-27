<?php


namespace App\Utils;


use App\Twig\AppExtension;
use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeAdminList extends CategoryTreeAbstract
{
    /**
     * @var string
     */
    public $html_1 = '<ul class="fa-ul text-left">';
    /**
     * @var string
     */
    public $html_2 = '<li><i class="fa-li fa fa-arrow-right"></i>';
    /**
     * @var string
     */
    public $html_3 = '<a href="';
    /**
     * @var string
     */
    public $html_4 = '">';
    /**
     * @var string
     */
    public $html_5 = '</a> <a onclick="return confirm(\'Are you sure?\');"
    href="';

    /**
     * @var string
     */
    public $html_6 = '">';
    /**
     * @var string
     */
    public $html_7 = '</a>';

    /**
     * @var string
     */
    public $html_8 = '</li>';

    /**
     * @var string
     */
    public $html_9 = '</ul>';

    /**
     * @var AppExtension
     */

    /**
     * @param array $categories_array
     * @return mixed
     */
    public function getCategoryList(array $categories_array)
    {

        $this->categoryList .= $this->html_1;

        foreach ($categories_array as $value ) {
            // Add html for parents

            $url_edit = $this->urlGenerator->generate('edit_category',['id' => $value['id']]);
            $url_delete = $this->urlGenerator->generate('delete_category',['id' => $value['id']]);

            $this->categoryList .= $this->html_2 . $value['name'] .
                $this->html_3 . $url_edit . $this->html_4 . ' Edit' .
                $this->html_5 . $url_delete . $this->html_6 . 'Delete' .
                $this->html_7;

            // check if children is not empty
            if (!empty($value['children'])) {
                //                call method recursively and take children array as an argument to collect childs
                $this->getCategoryList($value['children']);
            }
            $this->categoryList .=$this->html_8;
        }

        $this->categoryList .=$this->html_9;

        return $this->categoryList;
    }
}