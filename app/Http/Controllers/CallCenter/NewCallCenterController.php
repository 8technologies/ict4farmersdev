<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Models\AgentProfile;
use App\Models\CallModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class NewCallCenterController extends Controller
{
    public function call_center_voice(Request $r)
    {
        $sessionId = $r->input('sessionId');
        if ($sessionId === null) {
            return $this->xmlResponse($this->wrapResponse(['<Say>Missing session ID.</Say>']));
        }

        $call = CallModel::where('session_id', $sessionId)->first();
        $isNew = false;
        if ($call === null) {
            $call = new CallModel();
            $call->session_id = $sessionId;
            $isNew = true;
        }

        $this->updateCallFromRequest($call, $r);
        $call->save();

        if ($this->isCallCompleted($r)) {
            return $this->xmlResponse($this->wrapResponse(['<Say>Thank you.</Say>']));
        }

        $dtmfDigits = $r->input('dtmfDigits');

        if ($isNew && $dtmfDigits === null) {
            return $this->xmlResponse($this->languageMenuResponse(true));
        }

        if ($dtmfDigits === null) {
            if ($call->language !== null && $this->languageNeedsCategory($call->language) && $call->inquiry_category === null) {
                return $this->xmlResponse($this->categoryMenuResponse($call->language));
            }

            return $this->xmlResponse($this->languageMenuResponse(false));
        }

        if ($call->language === null) {
            $language = $this->languageForDigit($dtmfDigits);
            if ($language === null) {
                return $this->xmlResponse($this->languageMenuResponse(false, true));
            }

            $call->language = $language;
            $call->save();

            if ($this->languageNeedsCategory($language)) {
                return $this->xmlResponse($this->categoryMenuResponse($language));
            }

            return $this->xmlResponse($this->dialResponse($language, null));
        }

        if ($this->languageNeedsCategory($call->language) && $call->inquiry_category === null) {
            $category = $this->categoryForDigit($dtmfDigits);
            if ($category === null) {
                return $this->xmlResponse($this->categoryMenuResponse($call->language, true));
            }

            $call->inquiry_category = $category;
            $call->save();

            return $this->xmlResponse($this->dialResponse($call->language, $category));
        }

        return $this->xmlResponse($this->dialResponse($call->language, $call->inquiry_category));
    }

    private function isCallCompleted(Request $r)
    {
        $isActive = $r->input('isActive');
        $state = $r->input('callSessionState');

        return $isActive === '0' || $state === 'Completed';
    }

    private function updateCallFromRequest(CallModel $call, Request $r)
    {
        $payload = $r->all();
        if (!empty($payload)) {
            $call->last_payload = json_encode($payload);
        }

        $map = [
            'callerNumber' => 'caller_phone_number',
            'callerCountryCode' => 'caller_country',
            'callerCarrierName' => 'caller_carrier',
            'direction' => 'direction',
            'destinationNumber' => 'destination_number',
            'callSessionState' => 'call_session_state',
            'status' => 'status',
            'currencyCode' => 'currency_code',
            'amount' => 'amount',
            'callStartTime' => 'call_start_time',
            'dialStartTime' => 'dial_start_time',
            'durationInSeconds' => 'duration_in_seconds',
            'dialDurationInSeconds' => 'dial_duration_in_seconds',
            'recordingUrl' => 'recording_url',
            'dialDestinationNumber' => 'dial_destination_number',
            'dtmfDigits' => 'last_dtmf_digits',
        ];

        foreach ($map as $requestKey => $column) {
            if ($r->has($requestKey)) {
                $call->{$column} = $r->input($requestKey);
            }
        }

        if ($r->has('isActive')) {
            $call->is_active = $r->input('isActive') === '1';
        }

        if ($r->filled('durationInSeconds')) {
            $call->call_duration = $r->input('durationInSeconds');
        } elseif ($r->filled('dialDurationInSeconds')) {
            $call->call_duration = $r->input('dialDurationInSeconds');
        }

        if ($r->filled('dialDestinationNumber')) {
            $call->agent_phone_number = $r->input('dialDestinationNumber');
            $agent = AgentProfile::where('phone_number', $call->agent_phone_number)->first();
            if ($agent !== null) {
                $call->agent_profile_id = $agent->id;
            }
        }

        if ($r->filled('recordingUrl')) {
            $this->downloadRecording($call, $r->input('recordingUrl'));
        }
    }

    private function languageNeedsCategory($language)
    {
        return in_array($language, ['English', 'Luganda'], true);
    }

    private function languageForDigit($digit)
    {
        $map = [
            '1' => 'English',
            '2' => 'Luganda',
            '3' => 'Runyakitara',
            '4' => 'Swahili',
            '6' => 'Lugisu',
        ];

        return $map[(string) $digit] ?? null;
    }

    private function categoryForDigit($digit)
    {
        $map = [
            '1' => 'Coffee',
            '2' => 'Farming',
        ];

        return $map[(string) $digit] ?? null;
    }

    private function languageMenuResponse($includeIntro, $invalid = false)
    {
        $nodes = [];

        if ($includeIntro) {
            $nodes[] = '<Play url="' . asset('assets/audio/pwds/call_center/intro_01.mp3') . '"></Play>';
        }

        if ($invalid) {
            $nodes[] = '<Say>You entered a wrong selection. Please listen carefully and select again.</Say>';
        }

        $nodes[] = '<GetDigits timeout="30" numDigits="1"><Play url="' . asset('assets/audio/pwds/call_center/menu_selection_audio.mp3') . '"></Play></GetDigits>';

        return $this->wrapResponse($nodes);
    }

    private function categoryMenuResponse($language, $invalid = false)
    {
        $audio = $language === 'English'
            ? 'assets/audio/pwds/call_center/for_help_01.mp3'
            : 'assets/audio/pwds/call_center/okwebuza_01.mp3';

        $nodes = [];
        if ($invalid) {
            $nodes[] = '<Say>You entered a wrong selection. Please listen carefully and select again.</Say>';
        }

        $nodes[] = '<GetDigits timeout="30" numDigits="1"><Play url="' . asset($audio) . '"></Play></GetDigits>';

        return $this->wrapResponse($nodes);
    }

    private function dialResponse($language, $category)
    {
        $phoneNumbers = $this->buildDialNumbers($language, $category);
        if ($phoneNumbers === '') {
            return $this->wrapResponse(['<Say>No agents available, please try later.</Say>']);
        }
        $nodes = [];

        if ($language === 'Runyakitara') {
            $nodes[] = '<Say>Please wait as we connect you to Runyakitara agent.</Say>';
        } elseif ($language === 'English' && $category === 'Coffee') {
            $nodes[] = '<Say>Please wait as we connect you to Coffee agent.</Say>';
        } elseif ($language === 'English' && $category === 'Farming') {
            $nodes[] = '<Say>Please wait as we connect you to Farming agent.</Say>';
        } elseif ($language === 'Luganda' && $category === 'Coffee') {
            $nodes[] = '<Say>Please wait as we connect you to Luganda Coffee agent.</Say>';
        } elseif ($language === 'Luganda' && $category === 'Farming') {
            $nodes[] = '<Say>Please wait as we connect you to Luganda Farming agent.</Say>';
        }

        $nodes[] = '<Dial record="true" sequential="true" phoneNumbers="' . $phoneNumbers . '"/>';

        return $this->wrapResponse($nodes);
    }

    private function buildDialNumbers($language, $category)
    {
        $languageKey = str_replace(' ', '', $language);
        $query = AgentProfile::query()
            ->where('is_active', true)
            ->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(language, ''), ' ', ''))", [$languageKey]);

        if ($category !== null) {
            $categoryKey = str_replace(' ', '', $category);
            $query->where(function ($q) use ($categoryKey) {
                $q->whereRaw("FIND_IN_SET(?, REPLACE(COALESCE(inquiry_category, ''), ' ', ''))", [$categoryKey])
                    ->orWhereNull('inquiry_category')
                    ->orWhere('inquiry_category', '');
            });
        } else {
            $query->where(function ($q) {
                $q->whereNull('inquiry_category')
                    ->orWhere('inquiry_category', '');
            });
        }

        $agentNumbers = $query
            ->orderBy('priority')
            ->orderBy('id')
            ->pluck('phone_number')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $sipEndpoints = $this->sipFallbackNumbers($language, $category);
        $dialList = array_values(array_unique(array_merge($agentNumbers, $sipEndpoints)));

        return implode(',', $dialList);
    }

    private function sipFallbackNumbers($language, $category)
    {
        $fallback = [
            'English' => [
                'Coffee' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
                'Farming' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
            ],
            'Luganda' => [
                'Coffee' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
                'Farming' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
            ],
            'Runyakitara' => [
                '' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
            ],
            'Swahili' => [
                '' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
            ],
            'Lugisu' => [
                '' => ['agent1.farmercallcenter@ug.sip2.africastalking.com'],
            ],
        ];

        $key = $category ?? '';

        if (isset($fallback[$language][$key])) {
            return $fallback[$language][$key];
        }

        return $fallback[$language][''] ?? [];
    }

    private function wrapResponse(array $nodes)
    {
        return '<?xml version="1.0" encoding="UTF-8"?><Response>' . implode('', $nodes) . '</Response>';
    }

    private function xmlResponse($xml)
    {
        return response($xml)->header('Content-Type', 'text/xml');
    }

    private function downloadRecording(CallModel $call, $recordingUrl)
    {
        if (!$recordingUrl) {
            return;
        }

        $disk = Storage::disk('local');
        if ($call->recording_path && $disk->exists($call->recording_path)) {
            return;
        }

        $safeSessionId = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $call->session_id);
        $path = parse_url($recordingUrl, PHP_URL_PATH);
        $extension = pathinfo($path ?? '', PATHINFO_EXTENSION);
        if ($extension === '') {
            $extension = 'mp3';
        }

        $hash = substr(sha1($recordingUrl), 0, 16);
        $recordingPath = 'recordings/' . $safeSessionId . '_' . $hash . '.' . $extension;

        if ($disk->exists($recordingPath)) {
            $call->recording_path = $recordingPath;
            return;
        }

        try {
            $response = Http::timeout(10)->get($recordingUrl);
            if ($response->successful()) {
                $disk->put($recordingPath, $response->body());
                $call->recording_path = $recordingPath;
            }
        } catch (\Exception $e) {
            \Log::warning('Recording download failed', [
                'session_id' => $call->session_id,
                'recording_url' => $recordingUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
