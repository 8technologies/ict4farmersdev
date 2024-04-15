<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerWebController extends AdminController
{
    /** 
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Web Banners';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());
        //$grid->disableCreateButton(); 
        $grid->disableExport();

        $grid->model()->where([
            'section' => 'web',
        ]);

        $grid->column('id', __('Id'))->sortable();
        $grid->column('image', __('Image'))->image(url("/public/storage/"), 100, 100);
        $grid->column('name', __('Title'));
        $grid->column('sub_title', __('Sub-Title'));
        $grid->column('category_id', __('Category'));
        $grid->column('product_id', __('Product'));
        $grid->column('clicks', __('Clicks'));
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('section', __('Section'));
        $show->field('position', __('Position'));
        $show->field('name', __('Name'));
        $show->field('sub_title', __('Sub title'));
        $show->field('type', __('Type'));
        $show->field('category_id', __('Category id'));
        $show->field('product_id', __('Product id'));
        $show->field('image', __('Image'));
        $show->field('clicks', __('Clicks'));
        $show->field('parent_id', __('Parent id'));
        $show->field('order', __('Order'));
        $show->field('title', __('Title'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Banner());

        $form->hidden('section', __('Section'))->default('web')->value('web');


        $form->radio('type', 'Banner Type')
            ->options([
                1 => 'Category',
                2 => 'Product',
                3 => 'Link',
            ])->when(1, function (Form $form) {
                $form->select('category_id', 'Select Category')->options(
                    Category::get_subcategories()
                )->rules('required');
            })
            ->when(2, function (Form $form) {
                $form->select('product_id', 'Select Product')->options(
                    Product::all()->pluck('name', 'id')
                )->rules('required');
            })
            ->when(3, function (Form $form) {
                $form->text('position', __('Enter Link'))->rules('required');
            })->rules('required');




        $form->text('name', __('Title'));
        $form->text('sub_title', __('Sub title'));
        $form->image('image', __('Image'))->uniqueName();
        $form->hidden('clicks', __('Clicks'))->default(0);
        $form->hidden('parent_id', __('Clicks'))->default(0);
        $form->hidden('order', __('Clicks'))->default(0);
        $form->hidden('title', __('Clicks'))->default(0);


        return $form;
    }
}
