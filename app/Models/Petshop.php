<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petshop extends Model
{
  protected $table = "Petshops";

  protected $dates = ['deleted_at'];

  protected $guarded = ['id'];

  protected $fillable = ['list_of_item_id', 'master_petshop_id',
      'total_item', 'user_id', 'user_update_id'];
}
