<?php

namespace App\Admin\Controllers;

use App\Models\Organisation;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Log;
use Encore\Admin\Admin;

class OrganisationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Organisations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Organisation());
        //sort by latest
        $grid->model()->latest();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->quickSearch('name', 'address', 'phone', 'email', 'website', 'details');
        // $grid->column('id', __('Id'));
        $grid->column('logo', __('Logo'))->image('', 100, 100)->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('details', __('Details'))->limit(30);
        $grid->column('address', __('Address'))->sortable();
        $grid->column('phone', __('Phone'))->sortable();
        $grid->column('email', __('Email'))->sortable();
        $grid->column('website', __('Website'))->sortable();
        $grid->column('created_at', __('Registered'))
            ->display(function ($created_at) {
                return date('d-m-Y', strtotime($created_at));
            })->sortable();
        $grid->column('updated_at', __('Updated at'))->hide();

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
        $show = new Show(Organisation::findOrFail($id));

        // $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('logo', __('Logo'))->image();
        $show->field('address', __('Address'));
        $show->field('phone', __('Phone'));
        $show->field('email', __('Email'));
        $show->field('website', __('Website'));
        $show->field('details', __('Details'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Organisation());
        $form->text('name', __('Name'))->required();
        $form->image('logo', __('Logo'))->rules('mimes:png,jpeg,jpg');
        $form->text('address', __('Address'))->required();
        $form->mobile('phone', __('Phone'));
        $form->email('email', __('Email'))->required();
        $form->text('website', __('Website'));
        $form->textarea('details', __('Details'));

        return $form;
    }
}
