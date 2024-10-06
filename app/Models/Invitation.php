<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    
        use HasFactory;
    
        /**
         * Les attributs pouvant être assignés en masse.
         *
         * @var array
         */
        protected $fillable = [
            'email',
            'group_id',
            'token',
        ];
    
        /**
         * Relation avec le modèle Group.
         * Une invitation appartient à un groupe.
         */
        public function group()
        {
            return $this->belongsTo(Group::class);
        }
    
        /**
         * Générer un jeton d'invitation unique.
         *
         * @return string
         */
        public static function generateToken()
        {
            return \Illuminate\Support\Str::random(32);
        }
}
