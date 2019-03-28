<?php

Route::any('wiz/webhooks/{token}', 'Wiz\Webhooks\Http\WebhooksController@execute');
