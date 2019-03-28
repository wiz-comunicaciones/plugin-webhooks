<?php namespace Wiz\Webhooks\Models;

use Model;

/**
 * RequestData Model
 */
class RequestData extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wiz_webhooks_requests';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    protected $jsonable = ['request_data'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['hook_id', 'request_data'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'hook' => [
            'Wiz\Webhooks\Models\Hook',
        ],
    ];
}
