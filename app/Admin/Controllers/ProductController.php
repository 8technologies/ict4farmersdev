<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;


class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Products';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /* $p = Product::find(6);
        $pic = $p->pics[0];
         */

        $grid = new Grid(new Product());
 

        $grid->model()->where([
            'type' => 'product',
        ]);
        $grid->disableBatchActions();
        $grid->model()->orderBy('id', 'desc');


        //$grid->disableActions();
        //$grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            //$actions->disableDelete();
            //$actions->disableEdit();
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


        //$grid->column('updated_at', __('Updated at'));
        $grid->column('id', __('Photo'))->sortable(); 
        //feature_photo
        $grid->column('name', __('Product'))->image('', 40, 40);

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
            $cat = Category::find($category_id);
            if ($cat == null) {
                return 'N/A';
            }
            return $cat->name;
            //return $this->category->name;
        })->sortable();
    


        $grid->column('city_id', __('Location'))
            ->display(function ($city_id) {
                if ($this->location == null) {
                    return $city_id;
                }
                return $this->location->get_name();
            })->sortable();


        $grid->column('created_at', __('Posted'))->sortable();
        $grid->column('status', __('Status'))->display(function ($status) {
            $d = "";
            if ($status == 1) {
                $d = 'Active';
                return '<span class="label label-success">' . $d . '</span>';
            }
            $d =  'Inactive';
            return '<span class="label label-danger">' . $d . '</span>';
        })->filter([
            1 => 'Active',
            0 => 'Inactive',
        ])->sortable();

        //$grid->column('sub_category_id', __('Sub category id'));
        //$grid->column('fixed_price', __('Fixed price'));
        //$grid->column('attributes', __('Attributes'));
        //$grid->column('images', __('Images'));
        //$grid->column('quantity', __('Quantity'));
        //$grid->column('description', __('Description'));

        $grid->column('contact', __('Contact'))
            ->display(function ($id) {
                $u = Auth::user();
                $link = url('admin/chats/create?product_id=' . $this->id);
                $link .= "&sender=" . $u->id;
                $link .= "&receiver=" . $this->user_id;
                $call = '<a href="tel:0779755798" >Call 0779755798</a>';

                $data = $call;
                $link = 'https://play.google.com/store/apps/details?id=net.eighttechnologes.ict4farmers&hl=en&gl=US';
                $data .=  ' OR<br><a target="_blank" href="' . $link . '" >Download THE APP</a>';

                return $data;
            })->sortable();


        // if (Request::get('view') !== 'table') {
        //     $grid->setView('admin.grid.card');
        // }



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
        $u = Admin::user();



        $form = new Form(new Product());

        $form->radio('status', __('Status'))->options([
            1 => 'Active',
            0 => 'Inactive',
            2 => 'Pending for approval',
        ])->default(1);
        //type of product
        $form->radio('type', __('Type'))->options([
            'product' => 'Product',
            'service' => 'Service',
        ])->default('product')
            ->when('service', function (Form $form) {
                $form->hidden('metric')->default('unit');
                $form->hidden('nature_of_offer')->default('Service');
            })->when('product', function (Form $form) {
                $form->radio('nature_of_offer', __('Nature of offer'))->options([
                    'For sale' => 'For sale',
                    'For hire' => 'For hire',
                ])->default('For sale');

                $form->radio('metric', __('Unit of measure'))->options([
                    'kg' => 'Kilogram',
                    'g' => 'Gram',
                    'l' => 'Litre',
                    'ml' => 'Millilitre',
                    'unit' => 'Unit',
                ])->default('kg');
            });



        $form->text('name', __('Name of product'))->rules('required');
        //price

        $form->decimal('price', __('Price per Unit'))->rules('required')->required();
        $form->decimal('quantity', __('Available quantity'));

        $form->image('feature_photo', __('Main image'))->uniqueName();
        $form->select('sub_category_id', __('Product Category'))
            ->options(Category::get_subcategories())
            ->rules('required')->required();
        //city_id
        $form->select('city_id', __('Location'))
            ->options(Location::get_subcounties())
            ->rules('required')->required();

        if ($form->isCreating()) {
            $form->hidden('user_id')->default(Admin::user()->id);
        } else {
            //display the user_id field
            $form->display('user_id', __('Owner ID'));
        }

        $form->quill('description', __('Product Description'));
        //in_stock status



        /*			
	
	
		
	
local_id		
currency	
summary	
price_1	
price_2	
feature_photo	
rates	
user	
category	
sub_category	
supplier		
keywords	
p_type	
	
 
        */

        $form->hasMany('images', 'images', function ($f) {
            $u = Admin::user();
            $f->image('src', 'Image')->uniqueName();
            $f->hidden('user_id')->default($u->id);
            $f->hidden('parent_endpoint')->default('Product');
            $f->hidden('note')->default('Product');
            $f->hidden('p_type')->default('Product');
            $f->hidden('size')->default(10);

            /*
            name
            administrator_id		
	
	 
            */
        });

        return $form;
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

        $form->radio('in_stock', __('In stock'))->options([
            1 => 'Yes',
            0 => 'No',
        ])->default(1);


        return $form;
    }
}
