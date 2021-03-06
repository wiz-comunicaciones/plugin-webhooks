<?php namespace Wiz\Webhooks\Http;

use Request;
use Response;
use Exception;
use Wiz\Webhooks\Models\Hook;
use Illuminate\Routing\Controller;

class WebhooksController extends Controller
{

    /**
     * Execute a webhook
     *
     * @param  string   $token
     * @return Response
     */
    public function execute($token)
    {
        try {
            // If no webhook was found, return a 404
            if (!$hook = Hook::findByTokenAndMethod($token, Request::method()))
                return Response::make(e(trans('wiz.webhooks::lang.responses.not_found')), 404);

            switch($hook->type){
                case 'console':
                    $hook->queueConsoleCommand(Request::toArray()); # Queue the console command, and return a 200
                    break;
                case 'shell':
                default:
                    $hook->queueScript(); # Otherwise queue the script for execution, and return a 200
                    break;
            }
            return Response::make(e(trans('wiz.webhooks::lang.responses.success')), 200);
        } catch (Exception $e) {
            return Response::make(e(trans('wiz.webhooks::lang.responses.failed')), 500);
        }
    }
}
