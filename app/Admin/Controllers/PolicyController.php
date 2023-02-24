<?php

namespace App\Admin\Controllers;

use App\Models\Policy;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PolicyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Policy';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Policy());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('path', __('File'))->downloadable();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Policy::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('path', __('Path'));
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
        $form = new Form(new Policy());
        $form->text('name', __('Name'));
        $form->textarea('description', __('Description'))->rows();
        $form->file('path',__('Upload Document'))->uniqueName()->rules('mimes:doc,docx,pdf|max:5000', [
            'mimes' => 'Only doc, docx, pdf files are allowed',
            'max' => 'File size should be less than 5MB',
        ])
        ->downloadable();

        return $form;
    }
}
