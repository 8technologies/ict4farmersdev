<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FinancialRecord;
use App\Models\Garden;
use App\Models\GardenActivity;
use App\Models\GardenProductionRecord;
use App\Models\User;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function stats(Content $content)
    {

        Admin::css(url('/assets/css/css.css'));
        Admin::css(url('/assets/css/bootstrap.css'));
        return $content
            ->title('Statistics')
            ->row(function (Row $row) {
                $row->column(3, function (Column $column) {

                    $exp = FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->where('amount', '>', 0)
                        ->sum('amount');

                    $inc = (FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->where('amount', '<', 0)
                        ->sum('amount'));
                    $bal = $exp + $inc;

                    $inc = number_format($inc);
                    $exp = number_format($exp);

                    $box  = view('widgets.box-3', [
                        'title' => "Finance",
                        'icon' => url('assets/images/admin/money.gif'),
                        'count' => 'UGX. ' . number_format($bal),
                        'link' => 'financial-records',
                        'sub_title' => "UGX $exp total Expense, UGX $inc  Total income.",
                    ]);
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {

                    $pending = GardenActivity::where('administrator_id', Admin::user()->id)
                        ->where('is_done', '!=', true)
                        ->count('id');
                    $submited = GardenActivity::where('administrator_id', Admin::user()->id)
                        ->where('is_done', '=', true)
                        ->count('id');

                    $tot = GardenActivity::where('administrator_id', Admin::user()->id)
                        ->count('id');

                    $box  = view('widgets.box-3', [
                        'title' => "Scheduled Activities",
                        'icon' => url('assets/images/admin/tasks.png'),
                        'count' => $tot,
                        'link' => admin_url('garden-activities'),
                        'sub_title' => "$submited Activities submitted, $pending Pending activities.",
                    ]);
                    $column->append($box);
                });

                $row->column(3, function (Column $column) { 
                    //get all gardens
                    $gardens = User::find(Admin::user()->id)->enterprises;

                    //Group gardens by name and count eg 2 poultry, 3 piggery
                    $garden_names = $gardens->groupBy('name')->map(function ($item, $key) {
                        return count($item);
                    })->map(function ($item, $key) {
                        return $item . ' ' . Str::plural($key, $item);
                    })->implode(', ');
                    
                    $box  = view('widgets.box-3', [
                        'title' => "My Enterprises",
                        'icon' => url('assets/images/admin/enterprise.png'),
                        'count' => count($gardens),
                        'link' => admin_url('gardens'),
                        'sub_title' => $garden_names
                    ]);
                    $column->append($box);
                });


                $row->column(3, function (Column $column) {
                    $box  = view('widgets.box-3', [
                        'title' => "E-Academy",
                        'icon' => url('assets/icons/2.png'),
                        'count' => '24',
                        'link' => 'https://academy.unffeict4farmers.org/course/',
                        'sub_title' => 'All people who viewed your farm products.',
                    ]);
                    $column->append($box);
                });
            })
            ->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $data = [];
                    $lables = [];
                    $income = [];
                    $expense = [];
                    $days_ago = 30;
                    for ($i = 0; $i < $days_ago; $i++) {

                        $min_day = new Carbon();
                        $max_day = new Carbon();

                        $min_day->subDays($days_ago);
                        $max_day->subDays(($days_ago - 1));

                        $min_day->addDays($i);
                        $max_day->addDays($i);
                        $min = $min_day->format('Y-m-d H:i:s');
                        $max = $max_day->format('Y-m-d H:i:s');
                        $lables[] = $max_day->format('d M-Y');
                        $_income = 0;
                        $_expense = 0;
                        $recs = FinancialRecord::whereBetween('created_at', [$min, $max])->get();
                        foreach ($recs as $rec) { 
                            if ($rec->amount < 0) {
                                $_income += ((-1) * ((int)($rec->amount)));
                            } else {
                                $_expense += ((int)($rec->amount));
                            }
                        }
                        $expense[] = $_expense;
                        $income[] = $_income;
                    }


                    $data['lables'] = $lables;
                    $data['income'] = $income;
                    $data['expense'] = $expense;


                    $box_financial  = new Box('Financial report - (Last 30 days)', view('admin.charts.bar', [
                        'data' => $data,
                        'link' => admin_url('financial-records')
                    ]));
                    $box_financial->collapsable();
                    $column->append($box_financial);
                });

                $row->column(3, function (Column $column) {

                    $data = [];
                    $data[] = FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->where('amount', '>', 0)
                        ->sum('amount');

                    $data[] = (-1 * FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->where('amount', '<', 0)
                        ->sum('amount'));

                    $bal = $data[0] - $data[1];
                    if ($bal < 0) {
                        $title = "Loss: UGX" . number_format($bal);
                    } else {
                        $title = "Profits: UGX" . number_format($bal);
                    }

                    $box  = new Box('Profit or Loss', view('widgets.box-1', [
                        'title' => $title,
                        'data' => $data,
                        'colors' => ['green', 'red'],
                        'labels' => [
                            'Total Income - UGX ' . number_format($data[0]),
                            'Total Expense - UGX ' . number_format($data[1]),
                        ],
                    ]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {

                    $recs = FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->orderBy('id', 'Desc')
                        ->limit(8)
                        ->get();
                    $data = [];
                    foreach ($recs as $va) {
                        $_data['title'] = $va->description;
                        $_data['sub_title'] = $va->amount_text;
                        $data[] = $_data;
                    }
                    $box  = new Box('Recent transactions', view('widgets.box-4', ['data' => $data]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
            })
            ->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $data = [];
                    $data[] = GardenActivity::where([
                        'administrator_id' => Admin::user()->id,
                        'is_done' => true,
                    ])->count();

                    $data[] = GardenActivity::where([
                        'administrator_id' => Admin::user()->id,
                        'is_done' => false,
                    ])->count();

                    $tot = $data[0] + $data[1];
                    $percentage_done = 100;
                    if ($tot > 0) {
                        $percentage_done = ((int) ((($tot - $data[1]) / $tot) * 100));
                    }

                    $title = "$percentage_done% Activities completed";

                    $box  = new Box('Production activities', view('widgets.box-1', [
                        'title' => $title,
                        'data' => $data,
                        'colors' => ['green', 'red'],
                        'labels' => [
                            'Completed ' . number_format($data[0]),
                            'Nont Completed ' . number_format($data[1]),
                        ],
                    ]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $recs = GardenActivity::where('administrator_id', Admin::user()->id)
                        ->orderBy('id', 'Desc')
                        ->limit(8)
                        ->get();
                    $data = [];
                    foreach ($recs as $va) {
                        $_data['title'] = Str::limit($va->name, 20);
                        $_data['sub_title'] = $va->is_done ? "Done" : "Not done";
                        $data[] = $_data;
                    }
                    $box  = new Box(' Recent scheduled acticivies', view('widgets.box-4', ['data' => $data]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $recs = GardenProductionRecord::where('administrator_id', Admin::user()->id)
                        ->orderBy('id', 'Desc')
                        ->limit(8)
                        ->get();
                    $data = [];
                    foreach ($recs as $va) {
                        $_data['title'] = Str::limit($va->description, 16);
                        $_data['sub_title'] = Str::limit("By " . $va->owner->name, 10);
                        $data[] = $_data;
                    }
                    $box  = new Box('Recent production records', view('widgets.box-4', ['data' => $data]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $box  = new Box('Latest E-Academy courses', view('widgets.box-1', ['data' => [
                        ''
                    ]]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
            });
    }
    public function index(Content $content)
    {

        $u = Administrator::find(Auth::user()->id);
        if ($u == null) {
            die("Admin not found.");
        }
        if (isset($_GET['completed_wizard'])) {
            if ($_GET['completed_wizard'] == 'yes') {
                if ($u != null) {
                    if ($u->completed_wizard != 1) {
                        $u->completed_wizard = 1;
                        $u->save();
                        return redirect(admin_url("/"));
                    }
                }
            }
        }

        if ($u->completed_wizard == 1) {
            if (
                Admin::user()->isRole('admin') ||
                Admin::user()->isRole('administrator')
            ) {
                if (!isset($_GET['refreshed'])) {
                    header('Location: ' . admin_url('?refreshed=1'));
                    die();
                }
                $events = Utils::prepare_calendar_events(Admin::user()->id);
                // return redirect(admin_url("/maps"));
                return $content
                    ->view("admin.farmer.dashboard", [
                        'events' => $events
                    ]);
            }
            else if (
                Admin::user()->isRole('agent')
            ) {

               //get all enterprises
               $organisation = \App\Models\Organisation::find(Admin::user()->group_id);
               $farmer_groups = \App\Models\FarmersGroup::where('organisation_id', $organisation->id)->pluck('id')->toArray();
               $farmers = User::whereIn('group_id', $farmer_groups)->pluck('id')->toArray();
               $farms = \App\Models\Farm::whereIn('administrator_id', $farmers)->get();
                $markers = '';
                $icon_path = '';
                foreach($farms as $farm){
                    $status ='';
                    //check has closed farm
                    if(!$farm->running){
                        $status = 'Closed';
                        $icon_path = "'/assets/icons/pin-closed.png'";
                    }else {
                        $status = 'Operational';
                        $icon_path = "'/assets/icons/pin-open.png'";
                    }

                    $markers .= 'new L.marker(['.$farm->latitude.','.$farm->longitude.'],{icon:L.icon({iconUrl:'.$icon_path.',
                        iconSize:     [40, 50], // size of the icon
                        iconAnchor:   [22, 94], // point of the icon which will correspond to marker\'s location
                        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
                            })}).addTo(mymap).bindPopup("<a href=\"/admin/farms/'.$farm->id. '\">'.$farm->name.'</a><br>'.$status.'");';
                }
                Admin::css('https://unpkg.com/leaflet@1.9.3/dist/leaflet.css');
                Admin::js('https://unpkg.com/leaflet@1.9.3/dist/leaflet.js');

                Admin::script("
                    //use leafletjs
                    var mymap = L.map('mapid').setView([0.347596,32.582520], 8);
                        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoiZGFsaWhpbGxhcnkiLCJhIjoiY2s1c2ZhYnp1MDF2NDNsbDd0bTNjM3RzNCJ9._wzQ6YFFVtt5c_KAbsd1XA', {
                        attribution: 'Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors, <a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Imagery © <a href=\"https://www.mapbox.com/\">Mapbox</a>',
                        maxZoom: 18,
                        id: 'mapbox/streets-v11',
                        accessToken: 'pk.eyJ1IjoiZGFsaWhpbGxhcnkiLCJhIjoiY2s1c2ZhYnp1MDF2NDNsbDd0bTNjM3RzNCJ9._wzQ6YFFVtt5c_KAbsd1XA'
                    }).addTo(mymap);". $markers
                );
                $content->row('<div id="mapid" style="width: 100%; height: 500px;"></div>');
                
                return $content;
            } 
            else if (
                Admin::user()->isRole('farmer') ||
                Admin::user()->isRole('basic-user')
            ) {
                if (!isset($_GET['refreshed'])) {
                    header('Location: ' . admin_url('?refreshed=1'));
                    die();
                }
                $events = Utils::prepare_calendar_events(Admin::user()->id);
                return $content
                    ->view("admin.farmer.dashboard", [
                        'events' => $events
                    ]);
            } 
        } else {
            return $content
                ->view("admin.wizard.main");
        }
    }
}


/*


        return $content
            ->title('Dashboard')
            ->description('Description...')
            ->view("admin.wizard.main")
            ->row(function (Row $row) {


                $row->column(3, function (Column $column) {
                    $box  = new Box('My products views', view('widgets.box-3', [
                        'icon' => '1.png',
                        'count' => '24',
                        'sub_title' => 'All people who viewed your farm products.',
                    ]));
                    $box->style('success');
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    $box  = new Box('My customers messages', view('widgets.box-3', [
                        'icon' => '3.png',
                        'count' => '51',
                        'sub_title' => 'Unread messages from your customers.',
                    ]));
                    $box->style('success');
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    $box  = new Box('Announcements', view('widgets.box-3', [
                        'icon' => '4.png',
                        'count' => '51',
                        'sub_title' => 'Communications from your farmers association.',
                    ]));
                    $box->style('success');
                    $column->append($box);
                });

                $row->column(3, function (Column $column) {
                    $box  = new Box('E-Academy', view('widgets.box-3', [
                        'icon' => '2.png',
                        'count' => '17',
                        'sub_title' => 'Farming digital courses available for FREE.',
                    ]));
                    $box->style('success');
                    $column->append($box);
                });
            })
            ->row(function (Row $row) {
                $row->column(6, function (Column $column) {
                    $data = [];
                    $lables = [];
                    $income = [];
                    $expense = [];
                    $days_ago = 30;
                    for ($i = 0; $i < $days_ago; $i++) {

                        $min_day = new Carbon();
                        $max_day = new Carbon();

                        $min_day->subDays($days_ago);
                        $max_day->subDays(($days_ago - 1));

                        $min_day->addDays($i);
                        $max_day->addDays($i);
                        $min = $min_day->format('Y-m-d H:i:s');
                        $max = $max_day->format('Y-m-d H:i:s');
                        $lables[] = $max_day->format('d M-Y');
                        $_income = 0;
                        $_expense = 0;
                        $recs = FinancialRecord::whereBetween('created_at', [$min, $max])->get();
                        foreach ($recs as $rec) {
                            if ($rec->amount < 0) {
                                $_income += ((-1) * ((int)($rec->amount)));
                            } else {
                                $_expense += ((int)($rec->amount));
                            }
                        }
                        $expense[] = $_expense;
                        $income[] = $_income;
                    }


                    $data['lables'] = $lables;
                    $data['income'] = $income;
                    $data['expense'] = $expense;

                    $box_financial  = new Box('Financial report - (Last 30 days)', view('admin.charts.bar', ['data' => $data]));
                    $box_financial->collapsable();
                    $column->append($box_financial);
                });

                $row->column(3, function (Column $column) {

                    $data = [];
                    $data[] = FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->where('amount', '>', 0)
                        ->sum('amount');

                    $data[] = (-1 * FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->where('amount', '<', 0)
                        ->sum('amount'));

                    $bal = $data[0] - $data[1];
                    if ($bal < 0) {
                        $title = "Loss: UGX" . number_format($bal);
                    } else {
                        $title = "Profits: UGX" . number_format($bal);
                    }

                    $box  = new Box('Profit or Loss', view('widgets.box-1', [
                        'title' => $title,
                        'data' => $data,
                        'colors' => ['green', 'red'],
                        'labels' => [
                            'Total Income - UGX ' . number_format($data[0]),
                            'Total Expense - UGX ' . number_format($data[1]),
                        ],
                    ]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {

                    $recs = FinancialRecord::where('administrator_id', Admin::user()->id)
                        ->orderBy('id', 'Desc')
                        ->limit(8)
                        ->get();
                    $data = [];
                    foreach ($recs as $va) {
                        $_data['title'] = $va->description;
                        $_data['sub_title'] = $va->amount_text;
                        $data[] = $_data;
                    }
                    $box  = new Box('Recent transactions', view('widgets.box-4', ['data' => $data]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
            })
            ->row(function (Row $row) {
                $row->column(3, function (Column $column) {
                    $data = [];
                    $data[] = GardenActivity::where([
                        'administrator_id' => Admin::user()->id,
                        'is_done' => true,
                    ])->count();

                    $data[] = GardenActivity::where([
                        'administrator_id' => Admin::user()->id,
                        'is_done' => false,
                    ])->count();

                    $tot = $data[0] + $data[1];
                    $percentage_done = 100;
                    if ($tot > 0) {
                        $percentage_done = ((int) ((($tot - $data[1]) / $tot) * 100));
                    }

                    $title = "$percentage_done% Activities completed";

                    $box  = new Box('Production activities', view('widgets.box-1', [
                        'title' => $title,
                        'data' => $data,
                        'colors' => ['green', 'red'],
                        'labels' => [
                            'Completed ' . number_format($data[0]),
                            'Nont Completed ' . number_format($data[1]),
                        ],
                    ]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $recs = GardenActivity::where('administrator_id', Admin::user()->id)
                        ->orderBy('id', 'Desc')
                        ->limit(8)
                        ->get();
                    $data = [];
                    foreach ($recs as $va) {
                        $_data['title'] = Str::limit($va->name, 20);
                        $_data['sub_title'] = $va->is_done ? "Done" : "Not done";
                        $data[] = $_data;
                    }
                    $box  = new Box(' Recent scheduled acticivies', view('widgets.box-4', ['data' => $data]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $recs = GardenProductionRecord::where('administrator_id', Admin::user()->id)
                        ->orderBy('id', 'Desc')
                        ->limit(8)
                        ->get();
                    $data = [];
                    foreach ($recs as $va) {
                        $_data['title'] = Str::limit($va->description, 16);
                        $_data['sub_title'] = Str::limit("By " . $va->owner->name, 10);
                        $data[] = $_data;
                    }
                    $box  = new Box('Recent production records', view('widgets.box-4', ['data' => $data]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
                $row->column(3, function (Column $column) {
                    $box  = new Box('Latest E-Academy courses', view('widgets.box-1', ['data' => [
                        ''
                    ]]));
                    $box
                        ->style('success')
                        ->collapsable();
                    $column->append($box);
                });
            });

*/