<?php

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRule extends Model
{
    use HasFactory;

    protected $table = 'campaign_rules';

    protected $fillable = [
        'campaign_id',
        'rule_type',
        'operator',
        'value',
        'priority',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public const TYPE_MINIMUM_ORDER = 'minimum_order';
    public const TYPE_CUSTOMER_TOTAL_ORDERS = 'customer_total_orders';
    public const TYPE_CUSTOMER_TOTAL_SPENT = 'customer_total_spent';
    public const TYPE_CUSTOMER_ROLE = 'customer_role';
    public const TYPE_CUSTOMER_REGISTRATION_DATE = 'customer_registration_date';
    public const TYPE_PROVINCE = 'province';
    public const TYPE_CITY = 'city';
    public const TYPE_PAYMENT_METHOD = 'payment_method';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_PRODUCT = 'product';
    public const TYPE_DAY_OF_WEEK = 'day_of_week';
    public const TYPE_FIRST_ORDER = 'first_order';

    public const OP_EQUALS = 'equals';
    public const OP_NOT_EQUALS = 'not_equals';
    public const OP_GREATER_THAN = 'greater_than';
    public const OP_LESS_THAN = 'less_than';
    public const OP_GREATER_OR_EQUAL = 'greater_or_equal';
    public const OP_LESS_OR_EQUAL = 'less_or_equal';
    public const OP_IN = 'in';
    public const OP_NOT_IN = 'not_in';
    public const OP_CONTAINS = 'contains';
    public const OP_NOT_CONTAINS = 'not_contains';

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function evaluate(array $context): bool
    {
        $actualValue = $context[$this->rule_type] ?? null;

        return match($this->operator) {
            self::OP_EQUALS => $actualValue == $this->value,
            self::OP_NOT_EQUALS => $actualValue != $this->value,
            self::OP_GREATER_THAN => $actualValue > $this->value,
            self::OP_LESS_THAN => $actualValue < $this->value,
            self::OP_GREATER_OR_EQUAL => $actualValue >= $this->value,
            self::OP_LESS_OR_EQUAL => $actualValue <= $this->value,
            self::OP_IN => in_array($actualValue, (array) $this->value),
            self::OP_NOT_IN => !in_array($actualValue, (array) $this->value),
            self::OP_CONTAINS => is_string($actualValue) && str_contains($actualValue, $this->value),
            self::OP_NOT_CONTAINS => is_string($actualValue) && !str_contains($actualValue, $this->value),
            default => false,
        };
    }

    public static function getRuleTypes(): array
    {
        return [
            self::TYPE_MINIMUM_ORDER => 'Minimum Pesanan',
            self::TYPE_CUSTOMER_TOTAL_ORDERS => 'Total Pesanan Pelanggan',
            self::TYPE_CUSTOMER_TOTAL_SPENT => 'Total Belanja Pelanggan',
            self::TYPE_CUSTOMER_ROLE => 'Peran Pelanggan',
            self::TYPE_CUSTOMER_REGISTRATION_DATE => 'Tanggal Registrasi',
            self::TYPE_PROVINCE => 'Provinsi',
            self::TYPE_CITY => 'Kota/Kabupaten',
            self::TYPE_PAYMENT_METHOD => 'Metode Pembayaran',
            self::TYPE_CATEGORY => 'Kategori Produk',
            self::TYPE_PRODUCT => 'Produk',
            self::TYPE_DAY_OF_WEEK => 'Hari dalam Minggu',
            self::TYPE_FIRST_ORDER => 'Pesanan Pertama',
        ];
    }

    public static function getOperators(): array
    {
        return [
            self::OP_EQUALS => 'Sama dengan',
            self::OP_NOT_EQUALS => 'Tidak sama dengan',
            self::OP_GREATER_THAN => 'Lebih besar dari',
            self::OP_LESS_THAN => 'Kurang dari',
            self::OP_GREATER_OR_EQUAL => 'Lebih besar atau sama dengan',
            self::OP_LESS_OR_EQUAL => 'Kurang dari atau sama dengan',
            self::OP_IN => 'Dalam daftar',
            self::OP_NOT_IN => 'Tidak dalam daftar',
            self::OP_CONTAINS => 'Mengandung',
            self::OP_NOT_CONTAINS => 'Tidak mengandung',
        ];
    }
}
