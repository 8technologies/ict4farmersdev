<?php

namespace App\Admin\Controllers;

use App\Models\Garden;
use App\Models\GardenProductionRecord;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class GardenProductionRecordController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'My production records';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GardenProductionRecord());

        $grid->model()->where('administrator_id', Admin::user()->id)->orderBy('updated_at', 'desc');

        //$grid->column('id', __('Id'))->sortable();
        $grid->column('created_at', __('Created'))->sortable();

        $grid->column('garden_id', __('Enterprise'))->display(function () {
            return $this->enterprise->name;
        })->sortable();


        $grid->column('administrator_id', __('Owner'))->display(function () {
            return $this->owner->name;
        })->sortable();

        $grid->column('description', __('Description'))->display(function ($data) {
            return  Str::substr($data, 0, 100) . "....";
        })->sortable();


        // $grid->column('images', __('Images'));
        // $grid->column('created_by_id', __('Created by'));
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
        $show = new Show(GardenProductionRecord::findOrFail($id));

        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('garden_id', __('Garden'))->as(function () {
            return $this->enterprise->name;
        });
        $show->field('description', __('Description'));
        $show->images()->image();
        $show->field('created_by_id', __('Created by'))->as(function () {
            return $this->owner->name;
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GardenProductionRecord());
        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();

        $u = Auth::user();
        $form->select('garden_id', __('Select Enterprise'))
            ->options(
                User::find($u->id)->enterprises->pluck('name', 'id')
            )
            ->rules('required');

        $form->hidden('created_by_id', __('created_by_id'))->default($u->id)->value($u->id);
        $form->textarea('description', __('Record Description'))->rules('required');

        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);
        $form->multipleImage('images', __('Images'))->removable(); 


        return $form;
    }
}
