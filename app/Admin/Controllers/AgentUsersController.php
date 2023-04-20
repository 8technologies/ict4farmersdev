<?php

namespace App\Admin\Controllers;

use App\Models\FarmersGroup;
use App\Models\FarmersGroupHasAgent;
use App\Models\Location;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentUsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Our Farmers';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $u = Auth::user();
        /* $group_id = 0;
        foreach (FarmersGroupHasAgent::all() as $key => $g) {
            if ($g->administrator_id == $u->id) {
                $group_id = $g->farmers_group_id;
            }
        }

        $grid->model()->where('group_id', $group_id); 
        */


        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();


            $filter->between('created_at', 'Filter by date joined')->datetime();
        });

        $grid->export(function ($export) {

            $export->filename('Farmers database.csv');

            $export->except([
                'facebook', 'division',
                'longitude',
                'latitude',
                'opening_hours',
                'cover_photo',
                'twitter',
                'whatsapp',
                'youtube',
                'instagram',
                'category_id',
                'country_id',
                'last_seen',
                'phone_number_verified',
                'verification_code',
                'completed_wizard',

            ]);
            /*             
            
            
            $export->only(['column3', 'column4' ...]);
            $export->originalValue(['column1', 'column2' ...]); 
            

 	
             
            
            status
            linkedin
            
            status_comment
            
            
            sub_county
            user_type
            location_id
            owner_id
            date_of_birth
            marital_status
            gender
            group_id
            group_text
            sector
            production_scale
            number_of_dependants
            user_role
            access_to_credit
            experience
            profile_is_complete
            phone_number_2
            district_text
            county_text
            sub_county_text
            education

            first_name

 
            
            */

            $export->column('column_5', function ($value, $original) {
                return $value;
            });
        });


        if($u->can('view-other-groups')){
            $grid->model()->orderBy('id', 'Desc');
        }else{
            $grid->model()->where('group_id', $u->group_id)->orderBy('id', 'Desc');
        }

        $grid->column('id', __('Id'))->sortable();
        //$grid->column('username', __('Username'));
        //$grid->column('password', __('Password'));
        //$grid->column('avatar', __('Avatar'));
        //$grid->column('created_at', __('Created at'));
        //$grid->column('company_name', __('Company'));


        $grid->column('created_at', __('Joined'))->hide();
        $grid->column('name', __('Name'))->sortable();
        $grid->column('last_name', __('Last name'))->sortable();
        $grid->column('email', __('Email'));
        $grid->column('phone_number', __('Phone number'));
        $grid->column('address', __('Address'));
        $grid->column('district', __('District'))
            ->display(function ($id) {

                $loc = Location::find($this->sub_county);
                if ($loc == null) {
                    return;
                }
                if ($loc->mother == null) {
                    return $this->district_text;
                }
                return $loc->mother->name;
            })
            ->sortable();
        $grid->column('sub_county', __('Sub county'))
            ->display(function ($id) {
                if ($id == null) {
                    return;
                }
                $loc = Location::find($id);
                if ($loc == null) {
                    return $id;
                }
                return $loc->name;
            })
            ->sortable();
        $grid->column('about', __('About'))->hide();
        $grid->column('services', __('Services'))->hide();
        $grid->column('longitude', __('Longitude'))->hide();
        $grid->column('latitude', __('Latitude'))->hide();
        $grid->column('division', __('Division'))->hide();
        $grid->column('opening_hours', __('Opening hours'))->hide();
        $grid->column('cover_photo', __('Cover photo'))->hide();
        $grid->column('twitter', __('Twitter'))->hide();
        $grid->column('whatsapp', __('Whatsapp'))->hide();
        $grid->column('youtube', __('Youtube'))->hide();
        $grid->column('instagram', __('Instagram'))->hide();
        $grid->column('last_seen', __('Last seen'))->hide();
        $grid->column('status', __('Status'))->hide();
        $grid->column('linkedin', __('Linkedin'))->hide();
        $grid->column('category_id', __('Category id'))->hide();
        $grid->column('status_comment', __('Status comment'))->hide();
        /* $grid->column('country_id', __('Country id'))->sortable()->hide(); */
        $grid->column('region', __('Region'))->sortable();
        $grid->column('sub_county', __('Sub county'))->hide();
        $grid->column('user_type', __('User type'))->hide();
        $grid->column('location_id', __('Location id'))->hide();
        $grid->column('owner_id', __('Owner id'))->hide();
        $grid->column('date_of_birth', __('Date of birth'));
        $grid->column('marital_status', __('Marital status'));
        $grid->column('gender', __('Gender'));
        // $grid->column('group_id', __('Group id'))->hide();
        if($u->can('view-other-groups')) {
            $grid->column('group_id', __('Group'))
            ->display(function ($id) {
                if ($id == null) {
                    return;
                }
                $loc = FarmersGroup::find($id);
                if ($loc == null) {
                    return $id;
                }
                return $loc->name;
            });
        }
        $grid->column('sector', __('Sector'));
        $grid->column('production_scale', __('Production scale'));
        $grid->column('number_of_dependants', __('Number of dependants'));
        //$grid->column('user_role', __('User role'));
        $grid->column('access_to_credit', __('Credit access'));
        $grid->column('experience', __('Experience'));
        //$grid->column('profile_is_complete', __('Profile is complete'));

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('username', __('Username'));
        $show->field('password', __('Password'));
        $show->field('name', __('Name'));
        $show->field('avatar', __('Avatar'));
        $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('last_name', __('Last name'));
        $show->field('company_name', __('Company name'));
        $show->field('email', __('Email'));
        $show->field('phone_number', __('Phone number'));
        $show->field('address', __('Address'));
        $show->field('about', __('About'));
        $show->field('services', __('Services'));
        $show->field('longitude', __('Longitude'));
        $show->field('latitude', __('Latitude'));
        $show->field('division', __('Division'));
        $show->field('opening_hours', __('Opening hours'));
        $show->field('cover_photo', __('Cover photo'));
        $show->field('facebook', __('Facebook'));
        $show->field('twitter', __('Twitter'));
        $show->field('whatsapp', __('Whatsapp'));
        $show->field('youtube', __('Youtube'));
        $show->field('instagram', __('Instagram'));
        $show->field('last_seen', __('Last seen'));
        $show->field('status', __('Status'));
        $show->field('linkedin', __('Linkedin'));
        $show->field('category_id', __('Category id'));
        $show->field('status_comment', __('Status comment'));
        $show->field('country_id', __('Country id'));
        $show->field('region', __('Region'));
        $show->field('district', __('District'));
        $show->field('sub_county', __('Sub county'));
        $show->field('user_type', __('User type'));
        $show->field('location_id', __('Location id'));
        $show->field('owner_id', __('Owner id'));
        $show->field('date_of_birth', __('Date of birth'));
        $show->field('marital_status', __('Marital status'));
        $show->field('gender', __('Gender'));
        $show->field('group_id', __('Group id'));
        $show->field('group_text', __('Group text'));
        $show->field('sector', __('Sector'));
        $show->field('production_scale', __('Production scale'));
        $show->field('number_of_dependants', __('Number of dependants'));
        $show->field('user_role', __('User role'));
        $show->field('access_to_credit', __('Access to credit'));
        $show->field('experience', __('Experience'));
        $show->field('profile_is_complete', __('Profile is complete'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $u = Auth::user();

        $group_id = $u->group_id;

        if (Admin::user()->isRole('agent') && $group_id < 1) {
            return "Your agent's account have not been assigned to any farmer's association yet. 
            Please contact system administrators to do so.";
        }

        $form = new Form(new User());


        $form->saving(function (Form $f) {
            if (!$f->isEditing()) {
                
                $u = new User();
                $u->name = $f->name;
                $u->email = $f->email;
                $u->longitude = '0.00';
                $u->latitude = '0.00';
                $u->address = $f->address;
                $u->username = $f->phone_number;
                $u->category_id = $f->sector;
                $u->sector = $f->sector;
                $u->region = $f->location_id;
                $u->location_id = $f->location_id;
                $u->date_of_birth = $f->date_of_birth;
                $u->user_type = $f->user_type;
                $u->avatar = 'no_image.jpg';
                $u->password = Hash::make(trim($f->password));
                $u->marital_status = $f->marital_status;
                $u->gender = $f->gender;
                $u->production_scale = $f->production_scale;
                $u->number_of_dependants = $f->number_of_dependants;
                $u->user_role = $f->user_role;
                $u->access_to_credit = $f->access_to_credit;
                $u->experience = $f->experience;
                $u->profile_is_complete = false;

                $farmergroup = FarmersGroup::find($f->group_id);
                $farmergroup->farmers()->save($u);
                return redirect(url('admin/agent-users'));
            } else {

                die("is editng...");
            }
        });




        /*  	


        */
        //$form->image('avatar', __('Avatar'));
        //$form->textarea('last_name', __('Last name'));
        //$form->textarea('company_name', __('Company name'));
        //$form->textarea('about', __('About'));
        //$form->textarea('services', __('Services'));
        // $form->textarea('longitude', __('Longitude'));
        // $form->textarea('latitude', __('Latitude'));
        // $form->textarea('division', __('Division'));
        // $form->textarea('status_comment', __('Status comment'));
        // $form->textarea('country_id', __('Country id'));
        // $form->textarea('region', __('Region'));
        // $form->textarea('district', __('District'));
        // $form->textarea('sub_county', __('Sub county'));
        // $form->textarea('opening_hours', __('Opening hours'));
        // $form->textarea('cover_photo', __('Cover photo')); 
        // $form->number('owner_id', __('Owner id'));

        $form->hidden('user_type', __('User type'))->default('Farmer');
        $form->hidden('id', __('id'));
        $form->text('phone_number', __('Phone number'))
            ->help('Phone number will act as username to  login.')
            ->required();
        $form->password('password', __('Password'))
            ->required();

        $form->divider();

        $form->text('name', __('Full Name'))
            ->required();

        $form->date('date_of_birth', __('Date of birth'))
            ->rules('before:-10 years')
            ->required();


        $form->select('gender', __('Gender'))
            ->options([
                'Male' => 'Male',
                'Female' => 'Female',
            ])
            ->required();


        $form->select('marital_status', __('Marital status'))
            ->options([
                'Single' => 'Single',
                'Married' => 'Married',
            ])
            ->required();


        $form->email('email', __('Email'));


        $form->select('location_id', __('Sub-county'))
            ->options(Location::get_subcounties())
            ->required();

        $form->text('address', __('Address'));


        $form->select('group_id', __('Farmer\'s association'))
            ->options(FarmersGroup::all()->pluck('name', 'id'))
            ->default($group_id)
            ->readOnly()
            ->required();


        $form->select('sector', __('Farming sector'))
            ->options([
                'Crop farming' => 'Crop farming',
                'Livestock farming' => 'Livestock farming',
                'Fisheries' => 'Fisheries',
            ])
            ->required();

        $form->text('experience', __('Experience (in years)'))->attribute('type', 'number')
            ->rules('min:0|max: 200')
            ->required();

        $form->select('production_scale', __('Production scale'))
            ->options([
                'Subsistence production' => 'Subsistence production',
                'Small Commercial Production' => 'Small Commercial Production',
                'Large Commercial Production' => 'Large Commercial Production',
            ])
            ->required();


        $form->text('number_of_dependants', __('Number of dependants'))->attribute('type', 'number')
            ->required();

        $form->select('access_to_credit', __('Credit access'))
            ->options([
                'No any access' => 'No any access',
                'SACCO' => 'SACCO',
                'Bank' => 'Bank',
                'VSLA' => 'VSLA',
                'Family' => 'Family',
            ])
            ->required();


        $form->hidden('user_role', __('User role'))->default('Farmer')->readonly();


        return $form;
    }
}
