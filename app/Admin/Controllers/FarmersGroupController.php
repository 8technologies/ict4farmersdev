<?php

namespace App\Admin\Controllers;

use App\Models\FarmersGroup;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Form\NestedForm;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FarmersGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Farmers associations';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FarmersGroup());

        $grid->model()->orderBy('id', 'desc');   // order by latest to be recorded
        $grid->column('name', __('Name'));

        $grid->column('organisation_id', __('Organisation'))->display(function () {
            return $this->organisation->name;
        });
        $grid->column('website', __('Website'));

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
        $show = new Show(FarmersGroup::findOrFail($id));

        $show->field('created_at', __('Created at'));
        $show->field('name', __('Name'));
        $show->field('organisation_id', __('Organisation'))->as(function () {
            return $this->organisation->name;
        });
        $show->field('website', __('Website'));
        $show->field('acronym', __('Acronym'));
        $show->field('details', __('Details'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FarmersGroup());

        $form->text('name', __('Group Name'))->required();
        $form->select('organisation_id', __('Organisation'))->options(
            \App\Models\Organisation::all()->pluck('name', 'id')
        );
        $form->text('website', __('Website'))->required();
        $form->text('acronym', __('Acronym'))->required();
        $form->textarea('details', __('Details'))->required();
        return $form;
    }
}
