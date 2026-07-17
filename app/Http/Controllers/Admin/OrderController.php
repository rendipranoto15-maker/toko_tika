<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'shippingArea'])
            ->where('order_status', '!=', 'waiting_payment')
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'shippingArea', 'items.product', 'items.variant']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
            'order_status'   => 'required|in:waiting_payment,waiting_confirmation,processed,shipped,completed,cancelled',
        ]);

        if ($order->order_status === 'completed') {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('info', 'Pesanan sudah selesai dan tidak bisa diubah kembali.');
        }

        if ($request->order_status === 'shipped' && $order->has_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', 'Pesanan belum bisa dikirim karena masih ada item yang menunggu restok.');
        }

        $newStatus = $request->order_status;
        $data      = [
            'payment_status' => $request->payment_status,
            'order_status'   => $newStatus,
        ];

        if ($newStatus === 'shipped' && !$order->shipped_at) {
            $data['shipped_at'] = now();
        }

        if ($newStatus === 'completed' && !$order->completed_at) {
            $data['completed_at'] = now();
        }

        if ($newStatus === 'pending') {
            $data['shipped_at']   = null;
            $data['completed_at'] = null;
        }

        $order->update($data);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function confirmPayment(Order $order)
    {
        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('info', 'Pembayaran sudah dikonfirmasi sebelumnya.');
        }

        $order->update([
            'payment_status'       => 'paid',
            'order_status'         => 'processed',
            'payment_confirmed_at' => now(),
            'need_reupload'        => false,
            'reupload_note'        => null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    /**
     * ✅ FIX: Hapus dd() — tambahkan redirect yang benar
     * Admin minta user upload ulang bukti bayar
     */
    public function requestReupload(Request $request, Order $order)
    {
        $note = $request->input(
            'reupload_note',
            'Foto bukti pembayaran kurang jelas. Silakan upload ulang.'
        );

        $order->update([
            'need_reupload'  => true,
            'reupload_note'  => $note,
            'payment_status' => 'pending',
            'order_status'   => 'waiting_confirmation',
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Permintaan upload ulang berhasil dikirim ke pelanggan.');
    }

    /**
     * Alokasikan stok terkini (yang sudah ditambah admin lewat Edit Produk)
     * ke satu item pesanan yang menunggu restok, dengan cara MENGURANGI
     * stok sejumlah waiting_restock_quantity milik item tsb.
     *
     * Dipakai bersama oleh restockItem() (satu-satu) dan fulfillRestock()
     * (sekaligus semua item dalam satu pesanan), supaya perilakunya selalu
     * konsisten dan tidak ada jalur yang lupa mengurangi stok.
     *
     * @throws \Exception jika stok belum cukup untuk item ini.
     */
    private function deductRestockForItem(OrderItem $item): void
    {
        $product    = $item->product?->fresh();
        $restockQty = $item->waiting_restock_quantity ?? 0;

        if (!$product) {
            throw new \Exception('Produk tidak ditemukan.');
        }

        if ($restockQty > 0) {
            // Gunakan base_stock (satuan kanonik) sebagai sumber kebenaran untuk pengecekan
            $availableStock = $product->base_stock;

            if ($availableStock < $restockQty) {
                $shortage        = $restockQty - $availableStock;
                $shortageDisplay = $product->stock_unit === 'kg'
                    ? rtrim(rtrim(number_format($shortage / 1000, 2, '.', ''), '0'), '.') . ' kg'
                    : $shortage . ' ' . ($product->stock_unit ?? '');

                throw new \Exception(
                    'Stok "' . $product->name . '" belum cukup untuk merestok item ini. '
                    . 'Silakan tambah stok lewat halaman Edit Produk terlebih dahulu (kurang ' . $shortageDisplay . ').'
                );
            }

            $product->decrement('base_stock', $restockQty);
            $product->update([
                'stock_quantity' => $product->stock_unit === 'kg'
                    ? $product->fresh()->base_stock / 1000
                    : $product->fresh()->base_stock,
            ]);
        }

        $item->update([
            'is_waiting_restock'       => false,
            'waiting_restock_quantity' => 0,
        ]);
    }

    public function fulfillRestock(Order $order)
    {
        if (!$order->has_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('info', 'Pesanan ini sudah tidak memiliki item yang menunggu restok.');
        }

        try {
            foreach ($order->items as $item) {
                if ($item->is_waiting_restock) {
                    $this->deductRestockForItem($item);
                }
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', $e->getMessage());
        }

        $order->update([
            'has_waiting_restock' => false,
            'restock_note'        => null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Semua restok terpenuhi. Pesanan sekarang bisa diproses ke Shipping.');
    }

    public function restockItem(Order $order, OrderItem $item)
    {
        if ($item->order_id !== $order->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$item->is_waiting_restock) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('info', 'Item ini sudah tidak menunggu restok.');
        }

        if (!$item->product) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', 'Produk tidak ditemukan.');
        }

        // Alurnya: admin menambah stok fisik dulu lewat halaman Edit Produk
        // (yang mengisi ulang stock_quantity & base_stock sekaligus), BARU
        // klik tombol ini untuk mengalokasikan/menjatah stok tsb ke pesanan
        // yang menunggu. Karena itu di sini kita MENGURANGI stok terkini,
        // bukan menambah — checkout tidak lagi mendahului pengurangan untuk
        // item yang masih menunggu restok (lihat CheckoutController::reduceStock()).
        $product    = $item->product;
        $restockQty = $item->waiting_restock_quantity ?? 0; // simpan sebelum di-reset di dalam helper

        try {
            $this->deductRestockForItem($item);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.orders.show', $order->id)
                ->with('error', $e->getMessage());
        }

        $stillWaiting = $order->items()
            ->where('is_waiting_restock', true)
            ->exists();

        if (!$stillWaiting) {
            $order->update([
                'has_waiting_restock' => false,
                'restock_note'        => null,
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order->id)
            ->with('success', 'Stok "' . $product->name . '" berhasil direstok ' . $restockQty . ' ' . ($product->stock_unit ?? 'item') . '.');
    }
}