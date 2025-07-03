<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OcModel extends Model
{
    use HasFactory;

    protected $connection = 'opencart';
    protected $table = 'order';

}