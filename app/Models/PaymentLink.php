<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The example fields prompt.txt lists beyond name/phone/email — those
     * three already always flow into PaymentTransaction's own columns
     * regardless of this configuration, so they aren't part of this list.
     */
    public const STANDARD_FIELDS = [
        'comment' => 'Comment',
        'reference' => 'Reference',
        'product' => 'Product',
        'quantity' => 'Quantity',
        'registration_number' => 'Registration Number',
        'student_id' => 'Student ID',
        'invoice_number' => 'Invoice Number',
    ];

    protected $fillable = [
        'business_id',
        'title',
        'type',
        'amount',
        'custom_amount',
        'expiry_date',
        'description',
        'fields',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'custom_amount' => 'boolean',
        'expiry_date' => 'date',
        'fields' => 'array',
    ];

    /**
     * Turns the create-form's two raw inputs — checked standard field keys
     * and a newline-separated list of custom labels (capped at 10, to keep
     * a pasted wall of text from producing an unbounded fields array) — into
     * the canonical {key, label} shape stored in the `fields` column. Lives
     * here rather than in a controller since both the web and API payment
     * link controllers need the identical transformation.
     */
    public static function buildFieldsFromInput(?array $standardFields, ?string $customFieldLabels): array
    {
        $fields = [];

        foreach ($standardFields ?? [] as $key) {
            if (isset(self::STANDARD_FIELDS[$key])) {
                $fields[] = ['key' => $key, 'label' => self::STANDARD_FIELDS[$key]];
            }
        }

        $customLabels = array_filter(array_map('trim', explode("\n", $customFieldLabels ?? '')));

        foreach (array_slice($customLabels, 0, 10) as $label) {
            $fields[] = ['key' => Str::slug($label, '_'), 'label' => $label];
        }

        return $fields;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
