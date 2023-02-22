<?php

namespace App\Admin\Controllers;

use App\Models\CropCategory;
use App\Models\Farm;
use App\Models\Garden;
use App\Models\Location;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;


class GardenController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Enterprise';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        if (!Utils::is_wizard_done(Auth::user()->id)) {
            return redirect(admin_url("/"));
        }

        $grid = new Grid(new Garden());
        $grid->model()->latest();
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

        $grid->column('created_at', __('Created'))->sortable();

        $grid->column('name', __('Enterprise Name'))->sortable();

        $grid->column('administrator_id', __('Owner'))->display(function() {
            return $this->farm ? $this->farm->owner->name : '';
        })->sortable();
        $grid->column('farm_id', __('Farm'))->display(function () {
            return $this->farm ? $this->farm->name : '';
        })->sortable()->link(function () {
            return admin_url('farms/' . $this->farm_id);
        });
        // $grid->column('size', __('Size (acres)'));
        $grid->column('crop_category_id', __('Sector'))->display(function () {
            return $this->sector->get_name();
        })->sortable();

        $grid->column('plant_date', __('Started'));

        $grid->column('location_id', __('Subcounty'))->display(function () {
            return $this->farm ? $this->farm->location->get_name() : '';
        })->sortable();

        // $grid->column('longitude', __('Longitude'));
        // $grid->column('latitude', __('Latitude'));

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


        $show = new Show(Garden::findOrFail($id));
        
        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('crop_category_id', __('Crop category id'));
        $show->field('location_id', __('Location id'))->as(function () {
            return $this->farm->location->get_name();
        });
        $show->field('name', __('Name'));
        // $show->field('size', __('Acres'));
        $show->field('image', __('Image'));
        $show->field('plant_date', __('Plant date'));
        $show->field('harvest_date', __('Harvest date'));
        $show->field('details', __('Details'));
        $show->field('latitude', __('Latitude'))->as(function () {
            return $this->farm->latitude;
        });
        $show->field('longitude', __('Longitude'))->as(function () {
            return $this->farm->longitude;
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
        $form = new Form(new Garden());

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();
        $form->disableCreatingCheck();

        $u = Auth::user();

        $form->select('farm_id', __('Farm'))
            ->help('Select a farm where this enterprise is')
            ->options(
                Farm::where('administrator_id', $u->id)->get()->pluck('name', 'id')
            )
            ->rules('required');

        $form->select('crop_category_id', __('Enterprise Sector'))
            ->options(CropCategory::get_subcategories())
            ->rules('required');

        $form->text('name', __('Enterprise Name'))
            ->help("E.g, your poultry project, your garden, your cattle herd, etc.")
            ->rules('required');
        $form->image('image', __('Image'));
        // $form->number('size', __('Size in Acres'))
        //     ->help("E.g, 1 acre, 2 acres, 3 acres, etc.")
        //     ->rules('required|numeric|min:1');

        $form->date('plant_date', __('Start date'))
            ->help("Select the date when this enterprise started.")->required()->rules('before_or_equal:today');
        


        $form->textarea('details', __('Details'))
            ->help("Write something about this enterprise.");

        return $form;
    }
}
