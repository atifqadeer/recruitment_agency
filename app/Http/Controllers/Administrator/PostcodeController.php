<?php

namespace Horsefly\Http\Controllers\Administrator;

use Horsefly\Sale;
use Horsefly\Office;
use Horsefly\Unit;
use Horsefly\Cv_note;
use Horsefly\Specialist_job_titles;
use Illuminate\Http\Request;
use Horsefly\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DateTime;

class PostcodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
//        $this->middleware('permission:postcode-finder_search', ['only' => ['index','getPostcodeResults']]);
    }

    public function index(){
        return view('administrator.postcode.index');
    }

    public function getPostcodeResults(Request $request)
    {
        $today =Carbon::parse(date("Y-m-d"));

        $validator = Validator::make($request->all(), [
            'postcode' => 'required',
            'radius' => 'required'
        ])->validate();

        $postcode = $request->Input('postcode');
        $radius = $request->Input('radius');

        $postcode_para = urlencode($postcode).',UK';

        //        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key=AIzaSyBPx06p1VPBhS_qz-dw7t0rYkoMbKeoNBM";
        $postcode_api = config('app.postcode_api');
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$postcode_para}&key={$postcode_api}";
		

        $resp_json = file_get_contents($url);

        $resp = json_decode($resp_json, true);

        if ($resp['status'] == 'OK') {

            // get the important data
            $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";

            $data['cordinate_results'] = $this->distance($lati,$longi, $radius);
            if ($data['cordinate_results']->isNotEmpty()) {
                foreach($data['cordinate_results'] as &$job){
					$cv_limit = Cv_note::where(['sale_id' => $job->id, 'status' => 'active'])
                    ->count();
                    $job['cv_limit'] = $cv_limit;
					$newDate = Carbon::parse($job->posted_date);
                    $different_days = $today->diffInDays($newDate);
                    $office_id =$job['head_office'];
                    $unit_id =$job['head_office_unit'];


                    $office = Office::select("office_name")->where(["id" => $office_id,"status" => "active"])->first();
					$office = $office->office_name;
                    $unit = Unit::select("unit_name")->where(["id" => $unit_id,"status" => "active"])->first();
                    $unit = $unit->unit_name;
                    $job['office_name'] = $office;
                    $job['unit_name'] = $unit;
					 if($different_days <= 7)
                        {
                            $job['days_diff'] = 'true';

                        }
                        else
                        {
                            $job['days_diff'] = 'false';
                        }
					$title_prof =$job['job_title_prof'];
                    if($title_prof)
                    {
                        $job_title_prof = Specialist_job_titles::select("specialist_prof")->where("id", $title_prof)->first();
                        $job['job_title_prof_res']=$job_title_prof->specialist_prof;
                    }
                }
            } else {
                $data['cordinate_results'] = [];
            }
        }
		else
		{
			echo 'data not found';exit();
		}

        return view('administrator.postcode.index',compact('data','postcode','radius'));
    }

    function distance($lat, $lon, $radius)
    {
        $location_distance = Sale::select(DB::raw("*, ((ACOS(SIN($lat * PI() / 180) * SIN(lat * PI() / 180) +
                COS($lat * PI() / 180) * COS(lat * PI() / 180) * COS(($lon - lng) * PI() / 180)) * 180 / PI()) * 60 * 1.1515)
                AS distance"))->having("distance", "<", $radius)->orderBy("distance")->where("status", "active")->where("is_on_hold", "0")->get();
        /**
         * gives more accurate distance but it also shows more distances for 2 out of 10
         * (ROUND( 6353 * 2 * ASIN(SQRT( POWER(SIN(($lat - lat) * pi()/180 / 2),2) + COS($lat * pi()/180 ) * COS( lat *  pi()/180) * POWER(SIN(($lon - lng) * pi()/180 / 2), 2) )), 2)) AS distance_cur
         */
        return $location_distance;
    }
}
