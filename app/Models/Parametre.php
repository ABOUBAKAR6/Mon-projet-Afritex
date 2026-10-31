<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Parametre extends Model
{
    protected $primaryKey = 'cle';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['cle', 'valeur'];

    // Lu sur chaque page : mis en cache jusqu'à la prochaine modification
    public static function valeur(string $cle, ?string $defaut = null): ?string
    {
        $tous = Cache::rememberForever('parametres', fn () => static::pluck('valeur', 'cle')->all());

        return $tous[$cle] ?? $defaut;
    }

    public static function definir(string $cle, ?string $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        Cache::forget('parametres');
    }
}
