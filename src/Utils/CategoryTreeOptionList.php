<?php


namespace App\Utils;


use App\Utils\AbstractClasses\CategoryTreeAbstract;

class CategoryTreeOptionList extends CategoryTreeAbstract
{

    /**
     * @param array $categories_array
     * @param int $repeat
     * @return mixed
     */
    public function getCategoryList(array $categories_array, int $repeat = 0)
    {
        foreach ($categories_array as $value) {

            // get two --  dashes by default
            $this->categoryList[] = [
                'name' => str_repeat("-", $repeat).$value['name'],
                'id' => $value['id']
                ];

            if (!empty($value['children']))
            {
                // before repeating function make dashes 0 and look for children again
                $repeat = $repeat + 2;
                $this->getCategoryList($value['children'], $repeat);
                $repeat = $repeat - 2;
            }
        }
        return $this->categoryList;
    }
}