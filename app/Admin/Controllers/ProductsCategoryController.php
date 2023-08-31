<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Actions\Show;
use Encore\Admin\Layout\Content;

class ProductsCategoryController extends AdminController
{
    protected $title = 'All categories';

    /**
     * Index interface.
     *
     * @return Content
     */

    protected function grid()
    {
        $grid = new Grid(new Category());

        $grid->column('id', __('Id'))->sortable();
        $grid->column('image', __('thumnail'))
            ->display(function () {

                return '<img width="100" src="' . url('public/storage/' . $this->image) . '" >';
            })
            ->sortable();
        $grid->column('name', __('Title'))->sortable();
        $grid->column('parent', __('parent'))
            ->display(function () {
                $cat = Category::find($this->parent);
                if ($cat == null) {
                    return "-";
                }
                return $cat->name;
            })
            ->sortable();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Category());

        $parents = [];
        foreach (Category::all() as $key => $v) {
            $id = (int)($v->parent);
            if ($v->parent < 1) {
                $parents[$v->id] = $v->name;
            }
        }

        $form->select('parent', __('Parent category'))
            ->options($parents);

        $form->text('name', __('Name'))->required();

        $form->image('image', __('Image'));
        $form->list('attributes', __('Category Attributes'))->required();
        $form->image('image', __('Main Photo'))->required();
        $form->image('banner_image', __('Banner image'));

        $form->radio('show_in_banner', __('Show in banner'))->options(['Yes' => 'Yes', 'No' => 'No'])->required();
        $form->radio('show_in_categories', __('Show in categories'))->options(['Yes' => 'Yes', 'No' => 'No'])->required();
        $form->textarea('description', __('Details'));



        return $form;
    }

    protected function detail($id)
    {
        $show = new Show(Category::findOrFail($id));

        
        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('category', __('Category'));
        $show->field('status', __('Status'));
        $show->field('user', __('User'));
        $show->field('date_created', __('Date created'));
        $show->field('date_updated', __('Date updated'));
        $show->field('url', __('Url'));
        $show->field('default_amount', __('Default amount'));
        $show->field('image', __('Image'));
        $show->field('image_origin', __('Image origin'));
        $show->field('banner_image', __('Banner image'));
        $show->field('show_in_banner', __('Show in banner'));
        $show->field('show_in_categories', __('Show in categories'));

        return $show; 
    }
}
