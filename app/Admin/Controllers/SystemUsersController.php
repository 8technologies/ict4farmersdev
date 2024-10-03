<?php

namespace App\Admin\Controllers;

use App\Models\District;
use App\Models\Location;
use App\Models\Organisation;
use App\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class SystemUsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'System Users';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        // $user = User::find(18045);
        /* $user->cover_photo = rand(10000, 50000) . '.jpg';
        $user->save();
        dd('done updating'); */

        $grid = new Grid(new User());
        $u = Admin::user();
        //if is admin, show all users
        if ($u->user_type == 'admin') {
            $grid->model()->latest(); 
        } else { 
            $grid->model()->where('organisation_id', $u->organisation_id)->latest();
        }

        //filter
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name', 'Name');
            $filter->like('email', 'Email');
            $filter->like('phone_number', 'Phone number');
            $filter->like('username', 'Username');
            $filter->equal('status', 'Status')->select([
                'Pending' => 'Pending',
                'Active' => 'Active',
                'Suspended' => 'Suspended',
            ]);
            $filter->equal('district', 'District')->select(District::all()->pluck('name', 'id'));
            $filter->equal('sub_county', 'Sub county')->select(Location::get_subcounties());

            $orgs = Organisation::all()->pluck('name', 'id');
            $filter->equal('organisation_id', 'Organisation')->select($orgs);
        });
        $grid->model()->orderBy('id', 'desc');
        $grid->disableBatchActions();
        $grid->quickSearch('name', 'email', 'phone_number', 'username')->placeholder('Search by name, email, phone number, username');
        $grid->column('avatar', __('Photo'))->image('', 50, 50)->sortable();
        $grid->column('name', __('Name'))->display(function ($name) {
            if (strlen($this->first_name) > 2) {
                $name =  $this->first_name . ' ' . $this->last_name;
            }
            return $name;
        })->sortable();

        $grid->column('created_at', __('Joined'))
            ->display(function ($created_at) {
                return date('d M Y', strtotime($created_at));
            })->sortable()
            ->filter('range', 'date');
        $grid->column('company_name', __('Organization'))->sortable()->hide();
        $grid->column('email', __('Email'))->hide();
        $grid->column('phone_number', __('Phone number'))->sortable();
        $grid->column('address', __('Address'))->hide();
        $grid->column('about', __('About'))->hide();
        $grid->column('status', __('Status'))
            ->using([
                'Pending' => 'Pending',
                'Active' => 'Active',
                'Suspended' => 'Suspended',
            ], 'Active')->label([
                'Pending' => 'warning',
                'Active' => 'success',
                'Suspended' => 'danger',
            ], 'success')->sortable();
        $grid->column('district', __('District'))
            ->display(function ($district) {
                $dis = Location::find($district);
                if ($dis != null) {
                    $district = $dis->name;
                }
                if (strlen($district) < 3) {
                    $district = 'N/A';
                }
                return $district;
            })->sortable();
        $grid->column('sub_county', __('Sub county'))
            ->display(function ($sub_county) {
                $dis = Location::find($sub_county);
                if ($dis != null) {
                    $sub_county = $dis->name;
                }
                if (strlen($sub_county) < 3) {
                    $sub_county = 'N/A';
                }
                return $sub_county;
            })->sortable();
        $grid->column('user_type', __('User Type'))
            ->using([
                'farmer' => 'Farmer',
                'vendor' => 'Vendor',
                'admin' => 'Admin',
                'agent' => 'Agent',
                'worker' => 'Worker',
            ], 'Farmer')->label([
                'farmer' => 'warning',
                'vendor' => 'success',
                'agent' => 'success',
                'info' => 'info',
                'worker' => 'info',
            ], 'warning')
            ->filter([
                'farmer' => 'Farmer',
                'vendor' => 'Vendor',
                'admin' => 'Admin',
                'agent' => 'Agent',
            ])->sortable();
        $grid->column('date_of_birth', __('Date of birth'))->sortable()->hide();
        $grid->column('marital_status', __('Marital status'))->sortable()
            ->display(function ($marital_status) {
                if ($marital_status == null || strlen($marital_status) < 1) {
                    return 'N/A';
                }
                return ucfirst($marital_status);
            });
        $grid->column('gender', __('Gender'))
            ->filter([
                'Male' => 'Male',
                'Female' => 'Male',
            ])->sortable();

        $grid->column('sector', __('Sector'))->hide();
        $grid->column('production_scale', __('Production scale'))->hide();
        $grid->column('number_of_dependants', __('Number of dependants'))->hide();
        $grid->column('access_to_credit', __('Access to credit'))->hide();
        $grid->column('experience', __('Experience'))->hide();
        $grid->column('phone_number_2', __('Phone number 2'))->hide();
        $grid->column('education', __('Education'))->hide();
        $grid->column('vendor_status', __('Vendor Status'))
            ->using([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Requested' => 'Requested',
                'Rejected' => 'Rejected',
            ], 'Pending')->label([
                'Pending' => 'warning',
                'Approved' => 'success',
                'Requested' => 'info',
                'Rejected' => 'danger',
            ], 'success')->sortable()
            ->filter([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Requested' => 'Requested',
                'Rejected' => 'Rejected',
            ]);
        $grid->column('farmer_status', __('Farmer status'))
            ->using([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Requested' => 'Requested',
                'Rejected' => 'Rejected',
            ], 'Pending')->label([
                'Pending' => 'warning',
                'Approved' => 'success',
                'Requested' => 'info',
                'Rejected' => 'danger',
            ], 'success')->sortable()
            ->filter([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Requested' => 'Requested',
                'Rejected' => 'Rejected',
            ]);
        $grid->column('business_name', __('Business name'))->hide();
        $grid->column('business_address', __('Business address'))->hide();
        $grid->column('business_category', __('Business category'))->hide();
        $grid->column('business_phone_number', __('Business phone number'))->hide();
        //organisation_id
        $grid->column('organisation_id', __('Organisation'))->display(function ($organisation_id) {
            $org = Organisation::find($organisation_id);
            if ($org != null) {
                return $org->name;
            }
            return 'N/A';
        })->sortable();

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
        $show->field('phone_number_2', __('Phone number 2'));
        $show->field('district_text', __('District text'));
        $show->field('county_text', __('County text'));
        $show->field('sub_county_text', __('Sub county text'));
        $show->field('education', __('Education'));
        $show->field('phone_number_verified', __('Phone number verified'));
        $show->field('verification_code', __('Verification code'));
        $show->field('first_name', __('First name'));
        $show->field('completed_wizard', __('Completed wizard'));
        $show->field('vendor_status', __('Vendor status'));
        $show->field('farmer_status', __('Farmer status'));
        $show->field('business_name', __('Business name'));
        $show->field('business_address', __('Business address'));
        $show->field('business_category', __('Business category'));
        $show->field('business_phone_number', __('Business phone number'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());
        $u = Admin::user();
        
        $form->divider('BIO DATA');
        $form->text('first_name', __('First name'))->required();
        $form->text('last_name', __('Last name'))->required();
        $form->text('phone_number', __('Phone number'))->rules('required');
        $form->text('address', __('Address'));
        //last_seen now

        $dist = Location::get_subcounties();
        $form->select('location_id', __('District'))
            ->options($dist);

        $form->date('date_of_birth', __('Date of birth'));
        $form->radio('marital_status', __('Marital Status'))
            ->options([
                'Single' => 'Single',
                'Married' => 'Married',
                'Divorced' => 'Divorced',
                'Widowed' => 'Widowed',
            ])->default('Single');
        $form->radio('gender', __('Gender'))
            ->options([
                'Male' => 'Male',
                'Female' => 'Female',
            ])->required();
        $form->radio('education', __('Education Level'))
            ->options([
                'None' => 'None',
                'Primary' => 'Primary',
                'Secondary' => 'Secondary',
                'Tertiary' => 'Tertiary',
                'University' => 'University',
            ])->default('None');
        $form->text('phone_number_2', __('Phone number 2'));

        $form->radio('farmer_status', __('Farmer Verification status'))
            ->options([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Requested' => 'Requested',
                'Rejected' => 'Rejected',
            ])->default('Pending')->required();




        $form->radio('vendor_status', __('Vendor Verification Status'))
            ->options([
                'Pending' => 'Pending',
                'Approved' => 'Approved',
                'Requested' => 'Requested',
                'Rejected' => 'Rejected',
            ])->default('Pending')->required()
            ->when('Approved', function ($form) {
                $form->text('business_name', __('Business Name'));
                $form->text('business_address', __('Business address'));
                $form->text('business_category', __('Business category'));
                $form->text('business_phone_number', __('Business phone number'));
            });


        $form->ignore(['password_confirmation', 'change_password']);
        $form->saving(function (Form $form) {
            //forget change_password
            $form->forget('change_password');
            unset($form->change_password);
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });



        $form->divider('SYSTEM ACCOUNT');

        if ($u->id == 1 && $u->user_type != 'admin') {
            $u->user_type = 'admin';
            $u->save();
            $u = User::find($u->id);
        }

        $types = [
            'farmer' => 'Farmer',
            'vendor' => 'Vendor',
            'agent' => 'Agent',
            'worker' => 'Worker',
        ];

        $orgs = Organisation::all()->pluck('name', 'id');
        $isAdmin = false;
        if ($u->user_type == 'admin') {
            $types['admin'] =  'Admin';
            $isAdmin = true;
        }

        if (
            $u->user_type == 'admin' ||
            $u->user_type == 'agent'
        ) {
            $types['organisation'] = 'Organisation Admin';
        }

        if ($isAdmin) {
            //organisation_id picker
            $form->select('organisation_id', __('Organisation'))->options($orgs)->required();
        } else {
            $form->hidden('organisation_id')->default($u->organisation_id);
        }
        return $form;

        $form->radio('user_type', __('Main user role'))
            ->options($types)->default('farmer')
            ->required();

        $form->radio('status', __('Status'))
            ->options([
                'Pending' => 'Pending',
                'Active' => 'Active',
                'Suspended' => 'Suspended',
            ])->default('Pending');


        $form->text('email', 'Email address')
            ->creationRules(["unique:users"]);

        if ($form->isCreating()) {
            $form->text('password', __('Password'))->required();
        } else {
            $form->radio('change_password', 'Change Password')
                ->options([
                    'Change Password' => 'Change Password',
                    'Dont Change Password' => 'Dont Change Password'
                ])->when('Change Password', function ($form) {
                    $form->password('password', trans('admin.password'))->rules('confirmed');
                    $form->password('password_confirmation', trans('admin.password_confirmation'))
                        ->default(function ($form) {
                            return $form->model()->password;
                        });
                });
        }

        $form->image('avatar', trans('admin.avatar'))->uniqueName();

        return $form;
    }
}
