<?php namespace Wiz\Webhooks\Models;

use DB;
use Illuminate\Support\Facades\Artisan;
use Model;
use Queue;
use Backend;
use Carbon\Carbon;

/**
 * Hook Model
 */
class Hook extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wiz_webhooks_hooks';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'script',
        'http_method',
        'is_enabled',
    ];

    /**
     * @var array Attribute casting
     */
    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * @var array Datetime fields
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'executed_at',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'logs' => [
            'Wiz\Webhooks\Models\Log',
        ],
        'request_data' => [
            'Wiz\Webhooks\Models\RequestData',
        ],
    ];

    /**
     * Generate a unique token
     *
     * @return void
     */
    public function beforeCreate()
    {
        do {
            $this->token = str_random(40);
        } while (self::where('token', $this->token)->exists());
    }

    /**
     * Execute the script and log the output
     *
     * @return boolean
     */
    public function queueScript()
    {
        trace_log('Attempting to queue script');
        Queue::push('Wiz\Webhooks\Jobs\ShellHandler', ['hook_id' => $this->id]);
    }

    /**
     * Execute the console command and log the output
     *
     * @return boolean
     */
    public function queueConsoleCommand($request_data)
    {
        trace_log('Attempting to execute console command');
        $requestData = RequestData::create([
            'hook_id' => $this->id,
            'request_data' => $request_data
        ]);
        trace_log($requestData->id);
        trace_log('Queuing console command ' . $this->script);
        Artisan::queue($this->script, ['request_id' => $requestData->id]);
        trace_log('Command queued');
    }

    public function executeScript()
    {
        if($this->type == 'shell')
            $this->queueScript();
        else
            $this->queueConsoleCommand(json_decode('{"id":51384,"parent_id":0,"number":"51384","order_key":"wc_order_5c9e17a5af057","created_via":"checkout","version":"3.1.2","status":"on-hold","currency":"USD","date_created":"2019-03-29T08:03:33","date_created_gmt":"2019-03-29T13:03:33","date_modified":"2019-03-29T08:03:35","date_modified_gmt":"2019-03-29T13:03:35","discount_total":"0.00","discount_tax":"0.00","shipping_total":"0.00","shipping_tax":"0.00","cart_tax":"0.00","total":"1034.00","total_tax":"0.00","prices_include_tax":false,"customer_id":0,"customer_ip_address":"190.20.140.33","customer_user_agent":"mozilla\/5.0 (macintosh; intel mac os x 10_13_6) applewebkit\/537.36 (khtml, like gecko) chrome\/73.0.3683.86 safari\/537.36","customer_note":"This is a TEST","billing":{"first_name":"Sebastian","last_name":"Nieto","company":"This is a TEST","address_1":"Avenida Diego de Almagro 5377","address_2":"","city":"Santiago de Chile","state":"Region Metropolitana","postcode":"7790115","country":"CL","email":"nietomilevcic@gmail.com","phone":"936628700"},"shipping":{"first_name":"","last_name":"","company":"","address_1":"","address_2":"","city":"","state":"","postcode":"","country":""},"payment_method":"bacs","payment_method_title":"Send me an invoice","transaction_id":"","date_paid":null,"date_paid_gmt":null,"date_completed":null,"date_completed_gmt":null,"cart_hash":"d42f84eb6b5a48af982131528617b9e4","meta_data":[{"id":1171794,"key":"_billing_email_2","value":"nietomilevcic@gmail.com"},{"id":1171808,"key":"billing_first_name","value":"Sebastian"},{"id":1171809,"key":"billing_last_name","value":"Nieto"},{"id":1171810,"key":"billing_company","value":"This is a TEST"},{"id":1171811,"key":"billing_email","value":"nietomilevcic@gmail.com"},{"id":1171812,"key":"billing_phone","value":"936628700"},{"id":1171813,"key":"billing_email_2","value":"nietomilevcic@gmail.com"},{"id":1171814,"key":"billing_country","value":"CL"},{"id":1171815,"key":"billing_address_1","value":"Avenida Diego de Almagro 5377"},{"id":1171816,"key":"billing_city","value":"Santiago de Chile"},{"id":1171817,"key":"billing_state","value":"Region Metropolitana"},{"id":1171818,"key":"billing_postcode","value":"7790115"},{"id":1171819,"key":"order_comments","value":"This is a TEST"},{"id":1171820,"key":"staying_place","value":"Avenida Diego de Almagro 5377"},{"id":1171821,"key":"referral_info","value":"   "},{"id":1171824,"key":"_order_stock_reduced","value":"yes"}],"line_items":[{"id":3369,"name":"El Cocuy National Park Sierra Nevada Colombia 7 Day Tour - 2 People","product_id":12541,"variation_id":28762,"quantity":1,"tax_class":"","subtotal":"1034.00","subtotal_tax":"0.00","total":"1034.00","total_tax":"0.00","taxes":[],"meta_data":[{"id":36172,"key":"number-of-people","value":"2 people"},{"id":36173,"key":"_wapbk_booking_status","value":"paid"},{"id":36174,"key":"Start Date","value":"04\/03\/19"},{"id":36175,"key":"_wapbk_booking_date","value":"2019-04-03"}],"sku":"CO0102-2","price":1034}],"tax_lines":[],"shipping_lines":[],"fee_lines":[],"coupon_lines":[],"refunds":[]}'));
    }

    /**
     * Returns the script with normalized line endings
     *
     * @return void
     */
    public function getScriptAttribute($script)
    {
        return preg_replace('/\r\n?/', PHP_EOL, $script);
    }

    /**
     * Find a hook by token and HTTP method
     *
     * @param  \October\Rain\Database\Builder   $query
     * @param  string                           $token
     * @param  string                           $httpMethod
     * @return \October\Rain\Database\Builder
     */
    public function scopeFindByTokenAndMethod($query, $token, $httpMethod) {
        return $query->whereIsEnabled(true)
            ->whereHttpMethod($httpMethod)
            ->whereToken($token)
            ->firstOrFail();
    }

    /**
     * Enables or disables webhooks
     *
     * @param  \October\Rain\Database\Builder   $query
     * @return integer
     */
    public function scopeSetIsEnabled($query, $isEnabled)
    {
        return $query->update([
            'is_enabled' => $isEnabled,
            'updated_at' => Carbon::now(),
        ]);
    }

    public function scopeDisable($query)
    {
        return $query->setIsEnabled(false);
    }

    public function scopeEnable($query)
    {
        return $query->setIsEnabled(true);
    }

    /**
     * Left joins the logs count
     *
     * @param  \October\Rain\Database\Builder   $query
     * @return \October\Rain\Database\Builder
     */
    public function scopeJoinLogsCount($query)
    {
        $subquery = Log::select(DB::raw('id, hook_id, COUNT(*) as logs_count'))
            ->groupBy('hook_id')
            ->getQuery()
            ->toSql();

        return $query
            ->addSelect('wiz_webhooks_hooks.*')
            ->addSelect('logs.logs_count')
            ->leftJoin(DB::raw('(' . $subquery . ') logs'), 'wiz_webhooks_hooks.id', '=', 'logs.hook_id');
    }

    /**
     * Helper for snake_case http method
     *
     * @return string
     */
    public function getHttpMethodAttribute()
    {
        return array_key_exists('http_method', $this->attributes)
            ? $this->attributes['http_method']
            : 'post';
    }

    /**
     * Count the number of logs this hook has
     *
     * @return integer
     */
    public function getLogsCountAttribute($logs)
    {
        return array_key_exists('logs_count', $this->attributes)
            ? (int) $this->attributes['logs_count']
            : 0;
    }

    /**
     * Returns a url to this webhook
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('wiz/webhooks', [ 'token' => $this->token ]);
    }
}
