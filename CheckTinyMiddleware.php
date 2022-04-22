<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

class CheckTinyMiddleware
{
    const TPLUS_SERIALNUMBER = 0;
    const TPLUS_SPECIALID = 1;
    const TPLUS_DATAPARTITION = 2;
    const TPLUS_COUNTER = 3;
    const TPLUS_TIMER = 4;
    const TPLUS_NETWORKUSER = 5;
    const TPLUS_MAXNETWORKUSER = 6;
    const TPLUS_ONLINEUSER = 7;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $mp = new COM("{6DC390A4-4DE4-46CC-AEA6-B36F364CA9B0}");
        $var1 = env('USER_KEY');
        $varSafe1 =env('SAFE1');
        $varSafe2 = env('SAFE2');

        //initial in Single connection
        $mp->FindFirstTPlus($var1, $varSafe1, $varSafe2);

        if ($mp->GetTPlusErrorCode() == 1)
        {
            throw new Exception("Lock Not Found", 404);
        }

        if ($mp->GetTPlusErrorCode() == 10)
        {
            throw new Exception("Lock Data Invalid", 403);
        }

        if ($mp->GetTPlusErrorCode() == 0) {
            //get data
            $SerialNumber = $mp->GetTPlusData(self::TPLUS_SERIALNUMBER);
            settype($SerialNumber, "string");

            $SpatialID = $mp->GetTPlusData(self::TPLUS_SPECIALID);
            settype($SpatialID, "string");

            $DataPartition = $mp->GetTPlusData(self::TPLUS_DATAPARTITION);
            settype($DataPartition, "string");

            $MAXNTHID = $mp->GetTPlusData(self::TPLUS_MAXNETWORKUSER);
            settype($MAXNTHID, "string");

            $NtUser = $mp->GetTPlusData(self::TPLUS_MAXNETWORKUSER);
            settype($NtUser, "string");

            $Timer = $mp->GetTPlusData(self::TPLUS_TIMER);
            settype($Timer, "string");

            $Counter = $mp->GetTPlusData(self::TPLUS_COUNTER);
            settype($Counter, "string");


            if ($SerialNumber == env('KEY_SERIAL_NUMBER'))
            {
                return $next($request);

            }
            else
            {
                throw new Exception("Key Serial number not valid for this app." , 403);
            }
        } else
        {
            throw new Exception("Something went wrong TinyErrorCode : " . $mp->GetTPlusErrorCode, 403);
        }
    }
}
