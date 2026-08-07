<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingEmailRecipient extends Model
{
    protected $fillable = [
        'marketing_campaign_id',
        'email',
        'name',
        'source_type',
        'source_id',
        'status',
        'failure_reason',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }
}
