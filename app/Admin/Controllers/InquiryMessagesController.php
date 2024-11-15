<?php

namespace App\Admin\Controllers;

use App\Models\InquiryMessage;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class InquiryMessagesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'InquiryMessage';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new InquiryMessage());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('customer_id', __('Customer id'));
        $grid->column('customer_name', __('Customer name'));
        $grid->column('customer_email', __('Customer email'));
        $grid->column('customer_phone', __('Customer phone'));
        $grid->column('subject', __('Subject'));
        $grid->column('message', __('Message'));
        $grid->column('response', __('Response'));
        $grid->column('status', __('Status'));

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
        $show = new Show(InquiryMessage::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('customer_id', __('Customer id'));
        $show->field('customer_name', __('Customer name'));
        $show->field('customer_email', __('Customer email'));
        $show->field('customer_phone', __('Customer phone'));
        $show->field('subject', __('Subject'));
        $show->field('message', __('Message'));
        $show->field('response', __('Response'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new InquiryMessage());

        $form->textarea('customer_id', __('Customer id'));
        $form->textarea('customer_name', __('Customer name'));
        $form->textarea('customer_email', __('Customer email'));
        $form->textarea('customer_phone', __('Customer phone'));
        $form->textarea('subject', __('Subject'));
        $form->textarea('message', __('Message'));
        $form->textarea('response', __('Response'));
        $form->text('status', __('Status'))->default('pending');

        return $form;
    }
}
