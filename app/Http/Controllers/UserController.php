<?php

namespace App\Http\Controllers;

use App\Libraries\WaterMark;
use mikehaertl\shellcommand\Command;
use Symfony\Component\Process\Process;


class UserController extends Controller
{
    function show($text,$inputFile, $outputFile)
    {
        $check = WaterMark::make($text,$inputFile, $outputFile);
        dd($check);
    }
}
