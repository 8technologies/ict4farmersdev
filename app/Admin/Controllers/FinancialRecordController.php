<?php

namespace App\Admin\Controllers;

use App\Models\FinancialRecord;
use App\Models\Garden;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class FinancialRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'My financial records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FinancialRecord());

        if (
            Admin::user()->isRole('administrator') ||
            Admin::user()->isRole('admin')
        ) {
            /*$grid->actions(function ($actions) {
                $actions->disableEdit();
            });*/
        } else {
            $grid->model()->where('administrator_id', Admin::user()->id);
            $grid->disableRowSelector();
        }

        $grid->filter(function ($filter) {
            $u = Auth::user();
            //$filter->equal('administrator_id', "Filter by user")->select(Administrator::all()->pluck('name', 'id'));
            $filter->select('garden_id', __('Select Enterprise'))
                ->options(
                    \App\Models\User::find($u->id)->enterprises->pluck('name', 'id')
                );
        });

        if (
            Admin::user()->isRole('administrator') ||
            Admin::user()->isRole('admin')
        ) {
            /*$grid->actions(function ($actions) {
                $actions->disableEdit();
            });*/
        } else {
            $grid->model()->where('administrator_id', Admin::user()->id);
            $grid->disableRowSelector();
        }

        $grid->column('id', __('#Id'))->sortable();
        $grid->column('type', __('Type'));
        $grid->column('created_at', __('Created'))->sortable();
        $grid->column('garden_id', __('Enterprise'))->display(function () {
            return $this->enterprise->name;
        })->sortable();

        $grid->column('administrator_id', __('Owner'))->display(function () {
            return $this->owner->name;
        })->sortable();


        $grid->column('created_by', __('Created by'))->display(function () {
            return $this->creator->name;
        })->sortable();

        $grid->column('description', __('Description'));
        $grid->column('amount', __('Amount'));

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
        $show = new Show(FinancialRecord::findOrFail($id));

        $show->field('id', __('Id'));
        $show->filed('type', __('Type'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('garden_id', __('Garden'))->as(function () {
            return $this->enterprise->name;
        });
        $show->field('administrator_id', __('Administrator'))->as(function () {
            return $this->owner->name;
        });
        $show->field('created_by', __('Created by'))->as(function () {
            return $this->creator->name;
        });
        $show->field('description', __('Description'));
        $show->field('amount', __('Amount'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //$form->number('administrator_id', __('Administrator id'));

        $form = new Form(new FinancialRecord());
        $u = Auth::user();
        $form->select('garden_id', __('Enterprise'))
            ->options(
                \App\Models\User::find($u->id)->enterprises->pluck('name', 'id')
            )->rules('required');
        $form->hidden('created_by', __('created_by'))->default($u->id)->value($u->id);
        $form->select('type', __('Type'))->options([
            'expense' => 'Expense',
            'income' => 'Income',
        ])->rules('required');
        $form->text('description', __('Description'))->rules('required');
        $form->text('amount', __('Amount'))
            ->rules('required| numeric ');
        return $form;
    }
}
