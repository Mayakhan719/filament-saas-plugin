<?php

namespace Maya\FilamentSaasPlugin\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PaymentGateway extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'alias',
        'status',
        'gateway_parameters',
        'supported_currencies',
        'crypto',
        'configurations',
        'description',
        'sort_order',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'crypto' => 'boolean',
        'gateway_parameters' => 'json',
        'supported_currencies' => 'json',
        'configurations' => 'json',
        'sort_order' => 'integer',
    ];
}