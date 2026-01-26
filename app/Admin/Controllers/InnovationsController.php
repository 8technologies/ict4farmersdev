<?php

namespace App\Admin\Controllers;

use App\Models\Innovation;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class InnovationsController extends AdminController {
    protected $title = 'Innovations';
    protected function grid() {
        $grid = new Grid(new Innovation());
        // $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('status', __('Status'))->badge([
            'active' => 'success',
            'not_active' => 'danger',
            'in_progress' => 'warning',
        ])->sortable();
        $grid->column('created_at', __('Created At'))->sortable();
        $grid->column('updated_at', __('Updated At'))->sortable();
        return $grid;
    }

    protected function detail($id) {
         $show = new Show(Innovation::findOrFail($id));

            $show->field('name', __('Name'));
            $show->field('status', __('Status'));
            $show->field('description', __('Description'))->as(function ($description) {
                return nl2br(e($description));
            })->unescape();
            $show->field('attachments', __('Attachments'))->as(function ($attachments) {
                $links = [];
                foreach ($attachments as $attachment) {
                    $links[] = "<a href='{$attachment}' target='_blank'>View document</a>";
                }
                return implode(', ', $links);
            })->unescape();
            $show->field('created_at', __('Created At'));
            $show->field('updated_at', __('Updated At'));
            return $show;

    }

    protected function form() {
        $form = new Form(new Innovation());

        $form->text('name', __('Name'))->required();
        $form->select('status', __('Status'))->options([
            'active' => 'Active',
            'not_active' => 'Not Active',
            'in_progress' => 'In Progress',
        ])->default('not_active')->required();
        $form->textarea('description', __('Description'));
        $form->multipleFile('attachments', __('Attachments'));

        return $form;
    }
}