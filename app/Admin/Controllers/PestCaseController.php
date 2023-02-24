<?php

namespace App\Admin\Controllers;

use App\Models\Pest;
use App\Models\PestCase;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class PestCaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'PestCase';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PestCase());
        $grid->model()->latest();
        $grid->disableCreateButton();
        // $grid->column('id', __('Id'));
        $grid->column('created_at', __('Remoted'))->display(function() {
            //return in dd-mm-yyyy HH:mm:ss
            return $this->created_at->format('d-m-Y H:i:s');
        });
        // $grid->column('updated_at', __('Updated at'));
        $grid->column('garden_id', __('Garden'))->display(function(){
            return $this->garden ? $this->garden->name : '';
        });
        $grid->column('pest_id', __('Pest'))->display(function(){
            return $this->pest ? $this->pest->name : '';
        });
        $grid->column('administrator_id', __('Reporter'))->display(function(){
            return $this->reporter ? $this->reporter->name : '';
        });
        $grid->column('description', __('Description'));

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
        $show = new Show(PestCase::findOrFail($id));

        $show->field('created_at', __('Reported on'))->as(function () {
            return $this->created_at->format('d-m-Y H:i:s');
        }); 
        // $show->field('updated_at', __('Updated at'));
        $show->field('garden_id', __('Garden'))->as(function() {
            return $this->garden ? $this->garden->name : '';
        });
        $show->field('pest_id', __('Pest'))->as(function() {
            return $this->pest ? $this->pest->name : '';
        });
        $show->field('administrator_id', __('Reported By'))->as(function() {
            return $this->reporter ? $this->reporter->name : '';
        });
        $show->field('description', __('Description'));
        $show->field('images', __('Images'))->image();

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PestCase());
        //update the admin id on storing
        $form->saving(function (Form $form) {
            $form->administrator_id = Auth::user()->id;
        });
        //get the gardens the agent is associated with
        $organisation = \App\Models\Organisation::find(Auth::user()->group_id);
        $farmer_groups = \App\Models\FarmersGroup::where('organisation_id', $organisation->id)->pluck('id')->toArray();
        $farmers = User::whereIn('group_id', $farmer_groups)->pluck('id')->toArray();
        $farms = \App\Models\Farm::whereIn('administrator_id', $farmers)->pluck('id')->toArray();
        $gardens = \App\Models\Garden::whereIn('farm_id', $farms)->pluck('name','id');
        $form->select('garden_id', __('Garden'))->options($gardens)->required();
        $form->select('pest_id', __('Pest'))->options(Pest::all()->pluck('name', 'id'))->required();
        $form->textarea('description', __('Description'));
        $form->multipleImage('images', __('Images'))->removable()->uniqueName()->help('Please upload images of the pest. You can upload multiple images at once.');
        $form->hidden('administrator_id', __('Administrator id'));
        return $form;   
    }
}
