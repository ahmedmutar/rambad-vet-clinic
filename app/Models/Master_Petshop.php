<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Master_Petshop extends Model
{
    protected $table = "Master_petshops";

    protected $dates = ['deleted_at'];

    protected $guarded = ['id'];

    protected $fillable = ['payment_number', 'user_id', 'branch_id', 'user_update_id'];
}
