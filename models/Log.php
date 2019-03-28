<?php namespace Wiz\Webhooks\Models;

use Model;

/**
 * Log Model
 */
class Log extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'wiz_webhooks_logs';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['hook_id', 'output'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'hook' => [
            'Wiz\Webhooks\Models\Hook',
        ],
    ];
}
