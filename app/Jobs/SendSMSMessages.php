<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\library\Functions;
use GuzzleHttp\Client;

class SendSMSMessages extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $member, $message;

    /**
     * Create a new job instance.
     *
     * @param Member $member
     * @param $message
     */
    public function __construct($member, $message)
    {
        $this->member = $member;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {   
        $tgDetails = \Config::get('scholar.sms.txtguru');
        $url = $tgDetails['url'];
        $params = [];
        $params['username'] = $tgDetails['username'];
        $params['password'] = $tgDetails['password'];
        $params['source'] = $tgDetails['source'];

        $params['dmobile'] = '91' . $this->member;

        $params['message'] = $this->message;
        $qp = http_build_query($params);

        $url .= '?' . $qp;


        $client = new Client();
        
        $response = $client->request('GET', $url);

        unset($client);
        unset($params);

        return true;
        
    }
}
