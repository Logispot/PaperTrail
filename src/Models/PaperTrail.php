<?php

namespace Logispot\PaperTrail\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class PaperTrail
 * @package App\Model
 */
class PaperTrail extends Eloquent
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function reference()
    {
        return $this->morphTo('reference');
    }

    public function user()
    {
        return $this->morphTo('user');
    }
}
