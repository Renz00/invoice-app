<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function get_all_invoice(){
        //join invoice table with customer table and order by descending ID
        $invoices = Invoice::with('customer')->orderBy('id', 'DESC')->get();
        return response()->json([
            'invoices' => $invoices
        ], 200);
    }

    public function search_invoice(Request $request){
        $search = $request->get('s');
        if ($search != null){
            $invoices = Invoice::with('customer')
            ->where('id', 'LIKE', "%$search%")
            ->get();
            return response()->json([
                'invoices' => $invoices
            ], 200);
        }
        else {
            return $this->get_all_invoice();
        }
    }

    public function create_invoice(Request $request){
        $counter = Counter::where('key', 'invoice')->first();
        $random = Counter::where('key', 'invoice')->first();

        $invoice = Invoice::orderBy('id', 'DESC')->first();
        if ($invoice) {
            $invoice = $invoice->id+1; //incrementing invoice id
            $counters = $counter->value + $invoice; //adding it to counter value
        }
        else {
            $counters = $counter->value;
        }

        $formData = [
            'number' => $counter->prefix.$counters,
            'customer_id' => null,
            'customer' => null,
            'date' => ('Y-m-d'),
            'due_date' =>  null,
            'reference' => null,
            'discount' => 0,
            'terms_and_conditions' => 'Default Terms and Conditions',
            'items' => [
                [
                'product_id' => null,
                'product' => null,
                'unit_price' => 0,
                'quantity' => 1
                ]
            ]
        ];
        return response()->json($formData, 200);
    }

    public function add_invoice(Request $request){

        $request->validate([
            'invoice_item' => 'required',
            'customer_id' => 'required',
            'date' => 'required|date',
            'due_date' => 'required|date',
            'number' => 'required',
            'discount' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
            'terms_and_conditions' => 'required',
        ]);

        $invoiceitem = $request->input('invoice_item');

        $invoicedata['sub_total'] = $request->input('subtotal');
        $invoicedata['total'] = $request->input('total');
        $invoicedata['customer_id'] = $request->input('customer_id');
        $invoicedata['number'] = $request->input('number');
        $invoicedata['date'] = $request->input('date');
        $invoicedata['due_date'] = $request->input('due_date');
        $invoicedata['discount'] = $request->input('discount');
        $invoicedata['reference'] = $request->input('reference');
        $invoicedata['terms_and_conditions'] = $request->input('terms_and_conditions');

        $invoice = Invoice::create($invoicedata);

        foreach (json_decode($invoiceitem) as $item){
            $itemdata['product_id'] = $item->id;
            $itemdata['invoice_id'] = $invoice->id;
            $itemdata['quantity'] = $item->quantity;
            $itemdata['unit_price'] = $item->unit_price;

            InvoiceItem::create($itemdata);
        }

    }

    public function show_invoice($id){
        //join invoice table with customer table and invoice_items table using eloquent
        //and retrieve records where $id value is equal to invoice id
        //invoice_items table is also joined with product table
        //because of eloquent relationship
        $invoice = Invoice::with(['customer', 'invoice_items.product'])
            ->where('id', $id)->get();
        return response()->json([
            'invoice' => $invoice
        ], 200);
    }

    public function delete_invoice_items($id){
        $invoiceitem = InvoiceItem::findOrFail($id);
        //return value of 1 or true means delete was successful
        return $invoiceitem->delete();
    }

    public function update_invoice(Request $request, $id){
        //default value is false
        $update1 = false;
        $update2 = false;

        $request->validate([
            'invoice_item' => 'required',
            'customer_id' => 'required',
            'date' => 'required|date',
            'due_date' => 'required|date',
            'number' => 'required',
            'discount' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'total' => 'required|numeric',
            'terms_and_conditions' => 'required',
        ]);

        $invoice = Invoice::where('id', $id)->first();

        $invoice->sub_total = $request->subtotal;
        $invoice->total = $request->total;
        $invoice->customer_id = $request->customer_id;
        $invoice->number = $request->number;
        $invoice->date = $request->date;
        $invoice->due_date = $request->due_date;
        $invoice->discount = $request->discount;
        $invoice->reference = $request->reference;
        $invoice->terms_and_conditions = $request->terms_and_conditions;

        $update1 = $invoice->update($request->all());

        $invoiceitem = $request->input('invoice_item');

        $update2 = $invoice->invoice_items()->delete();

        if ($update2 == true){
            foreach (json_decode($invoiceitem) as $item){
                $itemdata['product_id'] = $item->id;
                $itemdata['invoice_id'] = $invoice->id;
                $itemdata['quantity'] = $item->quantity;
                $itemdata['unit_price'] = $item->unit_price;

                InvoiceItem::create($itemdata);
            }
        }

        return response()->json([
            'update1' => $update1,
            'update2' => $update2,
        ], 200);
    }
}
