<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PetStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Pet;
use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(string $orderId)
    {
        Gate::authorize('view', Order::class);

        $order = Order::findOrFail($orderId);

        return $this->format($order);
    }

    protected function format(Order $order)
    {
        return [
            'id' => $order->id,
            'petId' => $order->pet_id,
            'quantity' => $order->quantity,
            'shipDate' => $order->ship_date,
            'status' => $order->status,
            'complete' => $order->complete
        ];
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Order::class);

        $this->validate($request, [
            'petId' => 'required|integer|exists:pets,id',
            'quantity' => 'required|integer|min:0',
            'shipDate' => 'required|date',
            'status' => [
                'required',
                Rule::in(OrderStatus::getOptions())
            ],
            'complete' => 'required|boolean'
        ]);

        $order = Order::create([
            'pet_id' => $request->input('petId'),
            'quantity' => $request->input('quantity'),
            'ship_date' => $request->input('shipDate'),
            'status' => $request->input('status', OrderStatus::PLACED),
            'complete' => $request->input('complete', false)
        ]);

        return $order;
    }

    public function destroy(int $orderId)
    {
        Gate::authorize('delete', Order::class);

        $order = Order::findOrFail($orderId);

        $order->delete();

        return 'successful operation';
    }
}
