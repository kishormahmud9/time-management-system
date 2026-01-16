<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
     protected $guarded = [];

     public function business()
     {
         return $this->belongsTo(Business::class);
     }

     public function timesheets()
     {
         return $this->hasMany(Timesheet::class, 'mail_template_id');
     }

     public function usedBy()
     {
         return $this->hasMany(EmailTemplateUsedBy::class, 'mail_template_id');
     }
}
