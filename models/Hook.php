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
            $this->queueConsoleCommand(16);
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
