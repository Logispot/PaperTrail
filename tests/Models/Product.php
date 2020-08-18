<?php

namespace Logispot\PaperTrail\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Logispot\PaperTrail\Traits\PaperTrail;

/**
 * Class Product
 * @package Logispot\PaperTrail\Tests\Models
 */
class Product extends Model
{
    use PaperTrail;

    protected $guarded = [];
}
