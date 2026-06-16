<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use \App\Mail\VoteSuccessfulMail;

class VotingConfirmationController extends Controller
{

    public static function Send_Voting_Confirmation($email = null)
    {

        try {

            $subject = "Vote Successfully Recorded - Valley Golf and Country Club, Inc.";

            \Mail::to($email)->send(new VoteSuccessfulMail($subject));
        } catch (Exception $e) {
            throw new Exception('Error sending confirmation.');
        }
    }
}
