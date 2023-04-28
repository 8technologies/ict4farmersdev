<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MyProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'My products';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->model()->where([
            'user_id' => Admin::user()->id,
        ]);

        //$grid->disableActions();
        //$grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            //$actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->filter(function ($filter) {
            //$u = Auth::user();
            $filter->disableIdFilter();
            $filter->like('name', 'Searh by keyword');
            $filter->equal('nature_of_offer', __('Filter by nature of offer'))
                ->select(
                    [
                        'For sale',
                        'For hire',
                        'Service',
                    ]
                );
            $filter->equal('user_id', 'Search by owner')->select(url('api/users'));

            $filter->equal('category_id', __('Filter by category'))
                ->select(
                    Category::get_subcategories()
                );

            $filter->equal('city_id', __('Filter by locations'))
                ->select(
                    Location::get_subcounties()
                );
        });


        //$grid->column('id', __('Id'));
        //$grid->column('updated_at', __('Updated at'));
        $grid->column('id', __('Photo'))->display(function ($id) {
            return '<img width="40" src="' . $this->get_thumbnail() . '" ?>';
        })->sortable();

        $grid->column('name', __('Product'))->display(function ($id) {
            return $this->get_name_short();
        })->sortable();

        $grid->column('price', __('Price'))->display(function ($category_id) {
            return $this->price_text;
        })->sortable();

        $grid->column('nature_of_offer', __('Nature of offer'));

        $grid->column('user_id', __('Offer by'))->display(function () {
            return $this->seller_name;
        })->sortable();


        $grid->column('category_id', __('Category'))->display(function ($category_id) {
            if ($this->category == null) {
                return $category_id;
            }
            return $this->category->name;
        })->sortable();


        $grid->column('city_id', __('Location'))
            ->display(function ($city_id) {
                if ($this->location == null) {
                    return $city_id;
                }
                return $this->location->get_name();
            })->sortable();


        $grid->column('created_at', __('Posted'));

        //$grid->column('sub_category_id', __('Sub category id'));
        //$grid->column('fixed_price', __('Fixed price'));
        //$grid->column('attributes', __('Attributes'));
        //$grid->column('images', __('Images'));
        //$grid->column('quantity', __('Quantity'));
        //$grid->column('description', __('Description'));
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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('category_id', __('Category id'));
        $show->field('user_id', __('User id'));
        $show->field('country_id', __('Country id'));
        $show->field('city_id', __('City id'));
        $show->field('price', __('Price'));
        $show->field('slug', __('Slug'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
        $show->field('quantity', __('Quantity'));
        $show->field('images', __('Images'));
        $show->field('thumbnail', __('Thumbnail'));
        $show->field('attributes', __('Attributes'));
        $show->field('sub_category_id', __('Sub category id'));
        $show->field('fixed_price', __('Fixed price'));
        $show->field('nature_of_offer', __('Nature of offer'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {

        return admin_error(
            'Coming soon...',
            'At the moment, the web system does not support adding product. Download ICT4Farmers mobile App from google playstore to post your farm products.'
        );

        $form = new Form(new Product());
        $form->text('name', __('Name'));
        $form->number('category_id', __('Category id'));
        $form->number('user_id', __('User id'));
        $form->number('country_id', __('Country id'))->default(1);
        $form->number('city_id', __('City id'));
        $form->text('price', __('Price'));
        $form->text('slug', __('Slug'));
        $form->text('status', __('Status'));
        $form->textarea('description', __('Description'));
        $form->text('quantity', __('Quantity'));
        $form->textarea('images', __('Images'));
        $form->textarea('thumbnail', __('Thumbnail'));
        $form->textarea('attributes', __('Attributes'));
        $form->number('sub_category_id', __('Sub category id'));
        $form->text('fixed_price', __('Fixed price'))->default('Negotiable');
        $form->text('nature_of_offer', __('Nature of offer'))->default('For sale');

        return $form;
    }
}
