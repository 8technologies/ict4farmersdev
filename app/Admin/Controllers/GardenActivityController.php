<?php

namespace App\Admin\Controllers;

use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\User;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;

class GardenActivityController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Enterprise activies';


    public function calender_activity_edit()
    {
        $id = ((int)($_GET['id']));
        $act = GardenActivity::find($id);
        if ($act == null) {
            die("Activity not found.");
        }
        $act->done_status = $_GET['done_status'];
        $act->done_details = $_GET['done_details'];
        $act->is_done = 1;
        $act->save();
        header('Location: ' . admin_url('calendar'));
        die();
    }
    public function calendar(Content $content)
    {

        $events = Utils::prepare_calendar_events(Admin::user()->id);

        return $content
            ->view("admin.farmer.calendar", [
                'events' => $events
            ]);
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new GardenActivity());
        $grid->model()->orderBy('updated_at', 'desc');
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

        //$grid->column('id', __('Id'));
        //$grid->column('details', __('Details'));
        //$grid->column('administrator_id', __('Administrator id'));
        //$grid->column('is_generated', __('Is generated'));
        //$grid->column('done_by', __('Submitted'));
        //$grid->column('is_done', __('Submitted'));
        //$grid->column('done_details', __('Done details'));
        //$grid->column('position', __('Position'));
        //$grid->column('done_images', __('Done images'));
        //$grid->column('garden_id', __('Enterprise'));
        //$grid->column('garden_production_record_id', __('Garden production record id'));

        $grid->column('created_at', __('Created'));
        $grid->column('garden_id', __('Enterprise'))->display(function () {
            return $this->enterprise->name;
        })->sortable();
        $grid->column('name', __('Activity'));

        $grid->column('person_responsible', __('Assigned to'))->display(function () {
            return $this->assigned_to->name;
        })->sortable();

        $grid->column('due_date', __('To be done before'));

        $grid->column('done_status')->radio([
            1 => 'Done',
            0 => 'Not Done',
        ]);
        $grid->column('done_details', __('Comment'))->editable();

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
        $show = new Show(GardenActivity::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('due_date', __('Due date'));
        $show->field('details', __('Details'));
        $show->field('administrator_id', __('Administrator id'));
        $show->field('person_responsible', __('Person responsible'));
        $show->field('done_by', __('Done by'));
        $show->field('is_generated', __('Is generated'));
        $show->field('is_done', __('Is done'));
        $show->field('done_status', __('Done status'));
        $show->field('done_details', __('Done details'));
        $show->field('done_images', __('Done images'));
        $show->field('position', __('Position'));
        $show->field('garden_id', __('Garden id'));
        $show->field('garden_production_record_id', __('Garden production record id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new GardenActivity());

        $u = Auth::user();

        $form->disableReset();
        $form->disableViewCheck();
        $form->disableEditingCheck();

        $workers = [];
        $workers[$u->id] = "Me";
        foreach (User::where(['owner_id' => $u->id])->get() as $w) {
            $workers[$w->id] = $w->name;
        }

        $form->text('name', __('Activity title'))->rules('required');

        $form->select('garden_id', 'Enterprise')
            ->options(
                User::find($u->id)->enterprises->pluck('name', 'id')
            )
            ->rules('required');


        $form->select('person_responsible', 'Assign to')
            ->options($workers)
            ->rules('required');

        $form->date('due_date', __('Activity date'))->rules('required');
        $form->hidden('administrator_id', __('Administrator id'))->default($u->id);

        $form->textarea('details', __('Activity Description'));




        $form->hidden('done_by', __('Done by'))->default(0);
        $form->hidden('is_generated',)->default(0);
        $form->hidden('is_done',)->default(0);
        $form->hidden('done_status',)->default(0);
        $form->hidden('done_details',)->default('');
        $form->hidden('done_images',)->default('[]');
        $form->hidden('position',)->default(0);
        $form->hidden('garden_production_record_id',)->default(0);


        return $form;
    }
}
