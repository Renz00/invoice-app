<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'total',
        'customer_id',
        'sub_total',
        'date',
        'due_date',
        'reference',
        'discount',
        'number',
        'terms_and_conditions'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function invoice_items(){
        return $this->hasMany(InvoiceItem::class);
    }
}
