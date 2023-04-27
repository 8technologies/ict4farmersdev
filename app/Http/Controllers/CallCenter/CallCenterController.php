<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use AfricasTalking\SDK\AfricasTalking;
use App\Models\Attribute;
use App\Models\Call;
use App\Models\Configuration;
use Illuminate\Http\Request;

// $username = $_ENV['AT_API_USERNAME'];  // use 'sandbox' for development in the test environment
// $apiKey = $_ENV['AT_API_KEY']; // use your sandbox app API key for development in the test environment

// $AT = new AfricasTalking($username, $apiKey);

// // Get the voice service
// $voiceinstance = $AT->voice();


class CallCenterController extends Controller
{
    public function call_center_voice(Request $request)
    {
        $at = new Attribute();
        $at->name = json_encode($_POST);
        $at->type = json_encode($_GET);
        $at->options = json_encode($request->dtmfDigits);
        $at->save();
        $session_id = $request->sessionId;
        $is_active = $request->isActive;
        $phone_number = $request->callerNumber;
        $direction = $request->direction;
        $agent_called = $request->dialDestinationNumber;
        $destination_number = $request->destinationNumber;
        $recording_url = $request->recordingUrl;
        $duration_in_seconds = $request->durationInSeconds;
        $currency_code = $request->currencyCode;
        $call_state = $request->callSessionState;
        $amount = $request->amount;
        $dtmfDigits = $request->dtmfDigits;
        $config = Configuration::get()->first();

        $response = NULL;
        $menu = NULL;
        $language = NULL;


        if ($dtmfDigits == NULL) {
            $response = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Play url="'. $_ENV['APP_URL'] .'/ict4farmers-web/public/assets/audio/pwds/call_center/intro_01.mp3'.'">';   // thank you for calling  the farmers help center, please wait as we redirect you
            // $response .= '<Play url="'.$config->introduction.'">';   // thank you for calling  the farmers help center, please wait as we redirect you
            /*   $response .= '<Play url="' . asset('assets/audio/pwds/call_center/intro_01.mp3') . '">';   // thank you for calling  the farmers help center, please wait as we redirect you
            $response .= '</Play>'; */
            // dd(asset('assets/audio/pwds/call_center/intro_01.mp3'));
            $response .= '<GetDigits >';
            // $response .= '<Play url="'.$config->call_back_voice.'">';   // for help in english, press 1,.......
            $response .= '<Play url="' . asset('assets/audio/pwds/call_center/menu_selection_audio.mp3') . '"></Play>';
            $response .= '</GetDigits>';
            $response .= '<Say>We did not get your input. Good bye</Say>';
            $response .= '</Response>';

            header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
            header("Pragma: no-cache"); //HTTP 1.0
            header('Content-type: text/plain');
            return $response;
        }
        $current_call = NULL;

        // getting the current call
        try {
            $current_call = Call::where('session_id', $session_id)->first();

            // // Print the current_call onto the page so that the AT gateway can read it
            // header('Content-type: text/plain');
            // echo ">>>>>>>>>>>>> ".$current_call." >>>>>>>>>>>>>";

        } catch (\Throwable $th) {
            // $current_call = NULL;
            return;
        }

        # check if call is already saved and skip saving it again
        try {
            if (!isset($current_call)) {
                $new_call = new Call();
                $new_call->session_id = $session_id;
                $new_call->phone = $phone_number;
                $new_call->call_type = $direction;
                $new_call->active = $is_active;
                $new_call->agent_phone = $agent_called;
                $new_call->call_menu_selected = 1;
                $new_call->save();   // save the current call and all its attributes
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        // Start English menu 1 ---------------------------------------------------------------------------
        if ($dtmfDigits == '1' && $current_call->call_menu_selected == 1) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<GetDigits timeout="30" numDigits="1">';
            // $response .= '<Play url="'.$config->help_in_english.'">'; // (isaac) for help in coffee farming, press 1, ...
            $response .= '<Play url="' . asset('assets/audio/pwds/call_center/for_help_01.mp3') . '">'; // (isaac) for help in coffee farming, press 1, ...
            $response .= '</Play>';
            $response .= '</GetDigits>';
            $response .= '</Response>';

            $menu = 2;
            $language = "English";

            $current_call->call_menu_selected = 2;
            $current_call->language = "English";
        }  // End English menu 1 ---------------------------------------------------------------------------


        // OPTION 1
        // Start English sub menu
        elseif (($dtmfDigits == '1') && ($current_call->call_menu_selected == 2) && ($current_call->language == "English")) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256774610022,+256789272217,agent1.farmercallcenter@ug.sip2.africastalking.com"/>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494"/>';   // Mubarak
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256783631083"/>';    // Doryn
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256705638458"/>';  // Otim
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256778945859"/>';   // Jed
            $response .= '</Response>';
        } elseif (($dtmfDigits == '2') && ($current_call->call_menu_selected == 2) && ($current_call->language == "English")) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494" />';
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256787969833,+256783784498,agent1.farmercallcenter@ug.sip2.africastalking.com"/>';
            $response .= '</Response>';
        } // End English sub menu



        // OPTION 2
        // Start Luganda menu 1
        elseif (($dtmfDigits == '2') && ($current_call->call_menu_selected == 1)) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<GetDigits timeout="30" numDigits="1">';
            // $response .= '<Play url="'.$config->help_in_luganda.'">';
            $response .= '<Play url="' . asset('assets/audio/pwds/call_center/okwebuza_01.mp3') . '">';
            $response .= '</Play>';
            $response .= '</GetDigits>';
            $response .= '</Response>';

            $menu = 2;
            $language = "Luganda";

            $current_call->call_menu_selected = 2;
            $current_call->language = "Luganda";
        } // End Luganda menu 1 ---------------------------------------------------------------------------


        // Start Luganda sub menu
        elseif (($dtmfDigits == '1') && ($current_call->call_menu_selected == 2) && ($current_call->language == "Luganda")) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494" />';
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256773813709,+256789272217,agent1.farmercallcenter@ug.sip.africastalking.com"/>';
            $response .= '</Response>';
        } elseif (($dtmfDigits == '2') && ($current_call->call_menu_selected == 2) && ($current_call->language == "Luganda")) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494" />';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256772313512,+256772313512,agent1.farmercallcenter@ug.sip.africastalking.com"/>';
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256784802723,agent1.farmercallcenter@ug.sip.africastalking.com"/>';
            $response .= '</Response>';
        }  // End Luganda sub menu




        // OPTION 3    // Runyakitara
        elseif (($dtmfDigits == '3') && ($current_call->call_menu_selected == 1)) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494" />';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256774952449,+256783784498,agent2.farmercallcenter@ug.sip2.africastalking.com"/>';
            // Phone numbers below are in the format:                           nelson1,        nelson2,    dorcus1,       dorcus2.... 
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256700213998,+256783784498,+256705022766, +256774952449,agent2.farmercallcenter@ug.sip2.africastalking.com"/>';
            $response .= '</Response>';

            $menu = 2;
            $language = "Runyakitara";

            $current_call->call_menu_selected = 1;
            $current_call->language = "Runyakitara";
        }



        // OPTION 4    // swahili
        elseif ($dtmfDigits == '4' &&  $current_call->call_menu_selected == 1) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494" />';
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256772973970,+256783784498,agent3.farmercallcenter@ug.sip2.africastalking.com"/>';
            $response .= '</Response>';

            $menu = 2;
            $language = "Swahili";

            $current_call->call_menu_selected = 1;
            $current_call->language = "Swahili";
        }


        // OPTION 5  // Luo
        elseif ($dtmfDigits == '5' &&  $current_call->call_menu_selected == 1) {
            $response  = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256706638494" />';
            // $response .= '<Dial record="true" sequential="true" phoneNumbers="+256782701885,+256784067089,agent3.farmercallcenter@ug.sip.africastalking.com"/>';
            // Phone numbers below are in the format:                           Noki Charles Alebtong 
            $response .= '<Dial record="true" sequential="true" phoneNumbers="+256772673509,agent3.farmercallcenter@ug.sip.africastalking.com"/>';
            $response .= '</Response>';

            $menu = 2;
            $language = "Luo";

            // $current_call->call_menu_selected = 1;
            // $current_call->language = "Luo";
        }


        // updating the selected menu option of the current call
        if ($menu == 2) {
            $current_call->call_menu_selected = 2;
            $current_call->language = $language;
            $current_call->save();
        }


        // update call when its done
        if ($is_active == 0) {
            // update the call to record the voice -------   getting the current call

            $current_call->recording_url = $recording_url;
            $current_call->call_duration = $duration_in_seconds;
            $current_call->agent_phone = $agent_called;
            $current_call->save();
        }else {
            return $response;
        }

    }
}


/*

THESE ARE FOR THE PYTHON SYSTEM

+256312319003	Tollfree	Yes	
Callback: https://app.unffeict4farmers.org/callcenter/voice/
Events: N/A

+256800209003	Tollfree	Yes	
Callback: https://app.unffeict4farmers.org/callcenter/voice/
Events: N/A



https://5176-41-75-186-219.eu.ngrok.io/api/call_center_voice








// USE THIS FOR app2
https://app2.unffeict4farmers.org/api/call_center_voice

*/
