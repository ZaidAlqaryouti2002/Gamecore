<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends AdminResourceController
{
    private const STATUSES = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate(['status' => ['nullable', Rule::in(self::STATUSES)]]);
        $query = Order::query();
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $this->listing($request, $query, ['recipient_name', 'city', 'phone']);
    }

    public function show(Order $order): JsonResponse
    {
        return $this->data($order->load('items'));
    }

    public function store(Request $request): JsonResponse
    {
        $fields = $request->validate($this->shippingRules() + [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'user')],
            'shipping_fee' => ['required', 'numeric', 'decimal:0,2', 'between:0,99999999.99'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'between:1,10000'],
        ]);

        $order = DB::transaction(function () use ($fields) {
            $items = collect($fields['items'])->sortBy('product_id');
            unset($fields['items']);
            $subtotal = 0;
            $lines = [];

            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->find($item['product_id']);
                if (! $product || ! $product->is_active || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['items' => 'A selected product is unavailable or has insufficient stock.']);
                }

                $price = (int) round((float) ($product->sale_price ?? $product->price) * 100);
                $subtotal += $price * $item['quantity'];
                $lines[] = (new OrderItem)->forceFill([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $price / 100,
                    'quantity' => $item['quantity'],
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            $total = $subtotal + (int) round((float) $fields['shipping_fee'] * 100);
            if ($total > 9999999999) {
                throw ValidationException::withMessages(['items' => 'The order total exceeds the supported amount.']);
            }

            $order = new Order;
            $order->forceFill($fields + [
                'subtotal' => $subtotal / 100,
                'total' => $total / 100,
                'status' => 'pending',
                'payment_status' => 'pending',
            ])->save();
            $order->items()->saveMany($lines);

            return $order->load('items');
        }, 3);

        return $this->data($order, 201);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $fields = $request->validate($this->shippingRules() + [
            'status' => ['required', Rule::in(self::STATUSES)],
            'payment_status' => ['required', Rule::in(['pending', 'paid', 'failed', 'refunded'])],
        ]);

        $updated = DB::transaction(function () use ($fields, $order) {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($locked->status !== $fields['status']) {
                $next = match ($locked->status) {
                    'pending' => ['processing', 'cancelled'],
                    'processing' => ['shipped', 'cancelled'],
                    'shipped' => ['delivered'],
                    default => [],
                };
                if (! in_array($fields['status'], $next, true)) {
                    throw ValidationException::withMessages(['status' => 'This order cannot move to the selected status.']);
                }
                if ($fields['status'] === 'cancelled') {
                    $this->restoreStock($locked);
                }
            }

            $locked->forceFill($fields)->save();

            return $locked->load('items');
        }, 3);

        return $this->data($updated);
    }

    public function destroy(Order $order): JsonResponse
    {
        return DB::transaction(function () use ($order) {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (! in_array($locked->status, ['pending', 'cancelled'], true) || $locked->payment_status === 'paid') {
                return response()->json(['message' => 'Only unpaid pending or cancelled orders can be deleted.'], 409);
            }
            if ($locked->status === 'pending') {
                $this->restoreStock($locked);
            }

            $locked->delete();

            return response()->json(null, 204);
        }, 3);
    }

    private function restoreStock(Order $order): void
    {
        foreach ($order->items()->orderBy('product_id')->get() as $item) {
            Product::query()->whereKey($item->product_id)->increment('stock', $item->quantity);
        }
    }

    private function shippingRules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'payment_method' => ['required', Rule::in(['cod', 'card'])],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
